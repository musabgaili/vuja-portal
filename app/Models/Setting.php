<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value runtime settings. Values are stored JSON-encoded so a setting can
 * be a scalar, list or map. Reads are cached; writes bust the cache.
 *
 * Use Setting::get('engagement.actions', []) / Setting::put($key, $value).
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_PREFIX = 'setting:';

    /** Fetch a setting, JSON-decoded. Returns $default when unset. */
    public static function get(string $key, $default = null)
    {
        $cached = Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key) {
            $row = static::query()->where('key', $key)->first();

            // Sentinel so a genuinely-stored null is distinguishable from "unset".
            return $row ? ['v' => json_decode($row->value, true)] : null;
        });

        return $cached === null ? $default : $cached['v'];
    }

    /** Upsert a setting (value is JSON-encoded) and refresh the cache. */
    public static function put(string $key, $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value)],
        );
        Cache::forget(self::CACHE_PREFIX.$key);
    }

    /** Remove a setting (reverting reads to their default) and bust the cache. */
    public static function forget(string $key): void
    {
        static::query()->where('key', $key)->delete();
        Cache::forget(self::CACHE_PREFIX.$key);
    }
}
