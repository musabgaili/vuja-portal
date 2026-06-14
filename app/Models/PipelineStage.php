<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A CRM pipeline stage (Kanban column). The central source of truth that
 * replaced config('crm.stages'). 'won' and 'lost' are terminal outcomes handled
 * as constants elsewhere and are not rows here.
 */
class PipelineStage extends Model
{
    protected $fillable = ['key', 'label', 'color', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    /** Bootstrap colour names offered in the editor. */
    public const COLORS = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'dark'];

    private static ?array $memoMap = null;
    private static ?array $memoColors = null;

    protected static function booted(): void
    {
        // Any change to the set invalidates the per-request memo.
        static::saved(fn () => self::flushMemo());
        static::deleted(fn () => self::flushMemo());
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('position')->orderBy('id');
    }

    /** Active stages as an ordered [key => label] map (the config('crm.stages') replacement). */
    public static function map(): array
    {
        return self::$memoMap ??= static::query()
            ->where('is_active', true)->orderBy('position')->orderBy('id')
            ->pluck('label', 'key')->toArray();
    }

    public static function keys(): array
    {
        return array_keys(self::map());
    }

    /** [key => color] across ALL stages (active or not) so historical badges still colour. */
    public static function colorMap(): array
    {
        return self::$memoColors ??= static::query()->pluck('color', 'key')->toArray();
    }

    public static function flushMemo(): void
    {
        self::$memoMap = null;
        self::$memoColors = null;
    }
}
