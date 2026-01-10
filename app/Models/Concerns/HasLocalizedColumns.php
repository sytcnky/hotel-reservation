<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait HasLocalizedColumns
{
    public static function preferredLocale(): string
    {
        // Panel tek otorite (admin)
        if ($v = Session::get('panel_locale')) {
            return $v;
        }

        if ($v = optional(Auth::user())->locale) {
            return $v;
        }

        return App::getLocale() ?: 'en';
    }

    /** UI access */
    protected function getLocalized(string $attr): string
    {
        $pref = static::preferredLocale();
        $data = (array) ($this->{$attr} ?? []);

        return $data[$pref] ?? ($data['en'] ?? (is_array($data) ? (string) reset($data) : ''));
    }

    /** -------- Local scopes (temiz kullanım için) -------- */
    public function scopeOrderByLocalized(Builder $query, string $column, string $direction = 'asc'): Builder
    {
        $pref = static::preferredLocale();
        $dir = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        return $query->orderByRaw("COALESCE({$column}->>?, {$column}->>'en') {$dir}", [$pref]);
    }

    public function scopeWhereLocalizedLike(Builder $query, string $column, string $needle): Builder
    {
        $pref = static::preferredLocale();

        return $query->whereRaw("COALESCE({$column}->>?, {$column}->>'en') ILIKE ?", [$pref, "%{$needle}%"]);
    }
}
