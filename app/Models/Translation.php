<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = [
        'group',
        'key',
        'values',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    public static function getValue(string $group, string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $cacheKey = "translations_{$locale}";

        $items = Cache::remember($cacheKey, 3600, function () use ($locale) {
            return static::query()
                ->get()
                ->mapWithKeys(function (self $row) use ($locale) {
                    $value = $row->values[$locale] ?? null;
                    if ($value === null || $value === '') {
                        return [];
                    }

                    return ["{$row->group}.{$row->key}" => $value];
                })
                ->toArray();
        });

        return $items["{$group}.{$key}"] ?? null;
    }

    public static function flushCache(): void
    {
        $locales = config('app.supported_locales', ['tr', 'en']);

        foreach ($locales as $locale) {
            Cache::forget("translations_{$locale}");
        }
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
