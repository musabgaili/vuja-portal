<?php

namespace App\Services\Scope;

use App\Models\ScopePromptSetting;

/**
 * Resolves the Scope Planner AI prompt templates: a manager override in
 * scope_prompt_settings if present (and non-blank), otherwise the hardcoded
 * default in config('scope.prompts.*'). Placeholders ({brief}, {sections}, …)
 * are substituted by render().
 */
class ScopePromptService
{
    /** The editable template keys, exposed to the admin UI. */
    public const KEYS = ['generate_system', 'generate_user', 'suggest_system'];

    /** Raw template for a key (DB override else config default). */
    public function template(string $key): string
    {
        $override = ScopePromptSetting::where('key', $key)->value('content');

        if (is_string($override) && trim($override) !== '') {
            return $override;
        }

        return (string) config('scope.prompts.'.$key, '');
    }

    /** The shipped default for a key (ignores any override) — used by "reset". */
    public function default(string $key): string
    {
        return (string) config('scope.prompts.'.$key, '');
    }

    /** Whether a key currently uses a manager override (vs the default). */
    public function isCustom(string $key): bool
    {
        $override = ScopePromptSetting::where('key', $key)->value('content');

        return is_string($override) && trim($override) !== '';
    }

    /** Substitute {placeholder} tokens into the resolved template. */
    public function render(string $key, array $vars): string
    {
        // strtr does a single simultaneous pass, so a value that itself contains a
        // literal {token} (e.g. a brief that mentions "{services}") is never
        // re-scanned and cannot be replaced by a later variable.
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{'.$k.'}'] = (string) $v;
        }

        return strtr($this->template($key), $map);
    }
}
