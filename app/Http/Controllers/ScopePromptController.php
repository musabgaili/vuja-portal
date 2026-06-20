<?php

namespace App\Http\Controllers;

use App\Models\ScopePromptSetting;
use App\Services\Scope\ScopePromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manager-editable AI prompt templates for the Scope Planner. Lets a manager
 * tune the generate/suggest prompts; the response schema + JSON validation stay
 * in code, so an edited prompt can never break structured generation.
 */
class ScopePromptController extends Controller
{
    public function __construct(private ScopePromptService $prompts) {}

    public function edit()
    {
        abort_unless(Auth::user()?->isManager(), 403);

        $templates = [];
        foreach (ScopePromptService::KEYS as $key) {
            $templates[$key] = [
                'current' => $this->prompts->template($key),
                'default' => $this->prompts->default($key),
                'custom' => $this->prompts->isCustom($key),
            ];
        }

        return view('scope-planner.prompts', [
            'templates' => $templates,
            'keys' => ScopePromptService::KEYS,
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()?->isManager(), 403);

        $data = $request->validate([
            'prompts' => 'array',
            'prompts.*' => 'nullable|string|max:20000',
        ]);

        foreach (ScopePromptService::KEYS as $key) {
            if (! array_key_exists($key, $data['prompts'] ?? [])) {
                continue;
            }
            ScopePromptSetting::updateOrCreate(
                ['key' => $key],
                ['content' => (string) ($data['prompts'][$key] ?? ''), 'updated_by' => Auth::id()],
            );
        }

        return back()->with('success', __('portal.scope_prompts.saved'));
    }

    /** Drop the override for one key so it reverts to the shipped default. */
    public function reset(Request $request)
    {
        abort_unless(Auth::user()?->isManager(), 403);

        $key = $request->validate([
            'key' => 'required|in:'.implode(',', ScopePromptService::KEYS),
        ])['key'];

        ScopePromptSetting::where('key', $key)->delete();

        return back()->with('success', __('portal.scope_prompts.reset_done'));
    }
}
