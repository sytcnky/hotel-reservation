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

    /**
     * Aktif site dilleri (languages tablosu).
     * Fallback YOK: aktif dil yoksa boş array döner.
     */
    public static function activeLocales(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
            ->map(fn ($v) => strtolower(trim($v)))
            ->values()
            ->all();
    }

    public static function getValue(string $group, string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $locale = strtolower(trim((string) $locale));

        if ($locale === '') {
            return null;
        }

        $cacheKey = "translations_{$locale}";

        $items = Cache::remember($cacheKey, 3600, function () use ($locale) {
            return static::query()
                ->get()
                ->mapWithKeys(function (self $row) use ($locale) {
                    $value = is_array($row->values) ? ($row->values[$locale] ?? null) : null;

                    $value = is_string($value) ? trim($value) : null;
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
        foreach (static::activeLocales() as $locale) {
            Cache::forget("translations_{$locale}");
        }
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
