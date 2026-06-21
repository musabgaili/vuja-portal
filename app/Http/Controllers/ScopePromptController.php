<?php

namespace App\Http\Controllers;

use App\Services\Scope\ScopePromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manager-editable AI prompt templates for the Scope Planner, tuned PER TIER
 * (student / entrepreneur / company). The response schema + JSON validation stay
 * in code, so an edited prompt can never break structured generation.
 */
class ScopePromptController extends Controller
{
    public function __construct(private ScopePromptService $prompts) {}

    public function edit()
    {
        abort_unless(Auth::user()?->isManager(), 403);

        // templates[tier][type] = ['current' => ..., 'default' => ..., 'custom' => bool]
        $templates = [];
        foreach (ScopePromptService::TIERS as $tier) {
            foreach (ScopePromptService::TYPES as $type) {
                $templates[$tier][$type] = [
                    'current' => $this->prompts->template($type, $tier),
                    'default' => $this->prompts->default($type),
                    'custom' => $this->prompts->isCustom($type, $tier),
                ];
            }
        }

        return view('scope-planner.prompts', [
            'templates' => $templates,
            'tiers' => ScopePromptService::TIERS,
            'types' => ScopePromptService::TYPES,
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()?->isManager(), 403);

        $request->validate([
            'prompts' => 'array',
            'prompts.*' => 'array',
            'prompts.*.*' => 'nullable|string|max:20000',
        ]);

        $posted = (array) $request->input('prompts', []);
        foreach (ScopePromptService::TIERS as $tier) {
            foreach (ScopePromptService::TYPES as $type) {
                if (! array_key_exists($type, $posted[$tier] ?? [])) {
                    continue;
                }
                // Blank → clears the override and reverts to the default.
                $this->prompts->save($type, $tier, $posted[$tier][$type] ?? null, Auth::id());
            }
        }

        return back()->with('success', __('portal.scope_prompts.saved'));
    }

    /** Drop the override for one (tier, type) so it reverts to the shipped default. */
    public function reset(Request $request)
    {
        abort_unless(Auth::user()?->isManager(), 403);

        $data = $request->validate([
            'tier' => 'required|in:'.implode(',', ScopePromptService::TIERS),
            'type' => 'required|in:'.implode(',', ScopePromptService::TYPES),
        ]);

        $this->prompts->reset($data['type'], $data['tier']);

        return back()->with('success', __('portal.scope_prompts.reset_done'));
    }
}
