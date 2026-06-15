<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Editable bilingual boilerplate paragraphs (pricing intro, notes, terms,
 * confidentiality, validity…). Looked up by section + language, preferring a
 * tier-specific override over the shared 'all' default.
 */
class ScopeBoilerplate extends Model
{
    protected $table = 'scope_boilerplate';

    protected $fillable = ['tier', 'language', 'section_key', 'content'];

    /**
     * Resolve boilerplate text for a section: a tier-specific row wins over the
     * shared 'all' row. Falls back to a lang/scope.php default, then null.
     */
    public static function text(string $sectionKey, string $language, string $tier = 'all'): ?string
    {
        $rows = Cache::remember('scope_boilerplate:'.$language, 300, function () use ($language) {
            return static::where('language', $language)->get();
        });

        $hit = $rows->firstWhere(fn ($r) => $r->section_key === $sectionKey && $r->tier === $tier)
            ?? $rows->firstWhere(fn ($r) => $r->section_key === $sectionKey && $r->tier === 'all');

        if ($hit && filled($hit->content)) {
            return $hit->content;
        }

        $fallback = __('scope.'.$sectionKey);

        return $fallback === 'scope.'.$sectionKey ? null : $fallback;
    }

    protected static function booted(): void
    {
        $flush = function () {
            Cache::forget('scope_boilerplate:en');
            Cache::forget('scope_boilerplate:ar');
        };
        static::saved($flush);
        static::deleted($flush);
    }
}
