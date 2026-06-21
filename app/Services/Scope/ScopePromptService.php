<?php

namespace App\Services\Scope;

use App\Models\ScopePromptSetting;

/**
 * Resolves the Scope Planner AI prompt templates PER TIER. Resolution order for
 * a (type, tier) pair:
 *   1. a manager override row keyed "<tier>.<type>" (if non-blank),
 *   2. a legacy global override keyed "<type>" (pre per-tier rows, if any),
 *   3. the shipped default in config('scope.prompts.<type>').
 * Placeholders ({brief}, {sections}, …) are substituted by render().
 */
class ScopePromptService
{
    /** The editable template types. */
    public const TYPES = ['generate_system', 'generate_user', 'suggest_system'];

    /** The tiers each type can be tuned for. */
    public const TIERS = ['student', 'entrepreneur', 'company'];

    /** Storage key for a (type, tier) override row. */
    private function storageKey(string $type, string $tier): string
    {
        return $tier.'.'.$type;
    }

    /** Raw template for a (type, tier): per-tier override → legacy global → config default. */
    public function template(string $type, string $tier): string
    {
        $perTier = ScopePromptSetting::where('key', $this->storageKey($type, $tier))->value('content');
        if (is_string($perTier) && trim($perTier) !== '') {
            return $perTier;
        }

        $legacyGlobal = ScopePromptSetting::where('key', $type)->value('content');
        if (is_string($legacyGlobal) && trim($legacyGlobal) !== '') {
            return $legacyGlobal;
        }

        return $this->default($type);
    }

    /** The shipped default for a type (ignores any override) — used by "reset". */
    public function default(string $type): string
    {
        return (string) config('scope.prompts.'.$type, '');
    }

    /** Whether this (type, tier) currently uses a per-tier override (vs the default/global). */
    public function isCustom(string $type, string $tier): bool
    {
        $perTier = ScopePromptSetting::where('key', $this->storageKey($type, $tier))->value('content');

        return is_string($perTier) && trim($perTier) !== '';
    }

    /** Persist (or clear, when blank) a per-tier override. */
    public function save(string $type, string $tier, ?string $content, ?int $userId = null): void
    {
        $key = $this->storageKey($type, $tier);

        if ($content === null || trim($content) === '') {
            ScopePromptSetting::where('key', $key)->delete();

            return;
        }

        ScopePromptSetting::updateOrCreate(['key' => $key], ['content' => $content, 'updated_by' => $userId]);
    }

    /** Drop the per-tier override so it reverts to the default. */
    public function reset(string $type, string $tier): void
    {
        ScopePromptSetting::where('key', $this->storageKey($type, $tier))->delete();
    }

    /** Substitute {placeholder} tokens into the resolved (type, tier) template. */
    public function render(string $type, string $tier, array $vars): string
    {
        // strtr does a single simultaneous pass, so a value that itself contains a
        // literal {token} (e.g. a brief that mentions "{services}") is never
        // re-scanned and cannot be replaced by a later variable.
        $map = [];
        foreach ($vars as $k => $v) {
            $map['{'.$k.'}'] = (string) $v;
        }

        return strtr($this->template($type, $tier), $map);
    }
}
