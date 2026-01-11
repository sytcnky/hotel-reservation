<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasLocalizedColumns
{
    /**
     * Admin panel locale için tek otorite.
     */
    protected static function uiLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * JSON i18n kolonundan UI locale değerini döner.
     * - Fallback yok (en yok, ilk eleman yok).
     * - Boş string => null.
     * - Edge-case: attr string ise JSON decode denenir; değilse string döner (trimlenmiş, boşsa null).
     */
    protected function getLocalized(string $attr): ?string
    {
        $raw = $this->getAttribute($attr);

        if ($raw === null) {
            return null;
        }

        // Edge-case: DB'den string gelirse (cast bozulmuş / eski veri)
        if (is_string($raw)) {
            $s = trim($raw);

            // JSON string olabilir
            $decoded = json_decode($s, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                return $s !== '' ? $s : null;
            }
        }

        // Array değilse scalar olarak döndür.
        if (! is_array($raw)) {
            $s = trim((string) $raw);

            return $s !== '' ? $s : null;
        }

        $ui = static::uiLocale();

        $val = $raw[$ui] ?? null;

        if (! is_string($val)) {
            return null;
        }

        $val = trim($val);

        return $val !== '' ? $val : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Local scopes (admin tablo arama/sıralama için)
    |--------------------------------------------------------------------------
    | Sadece uiLocale
    | Boş string -> NULL kabul edilir.
    */

    public function scopeOrderByLocalized(Builder $query, string $column, string $direction = 'asc'): Builder
    {
        $ui = static::uiLocale();
        $dir = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        // Postgres: NULLS LAST ile boş/eksik çeviriler en sona gider.
        return $query->orderByRaw("NULLIF({$column}->>?, '') {$dir} NULLS LAST", [$ui]);
    }

    public function scopeWhereLocalizedLike(Builder $query, string $column, string $needle): Builder
    {
        $ui = static::uiLocale();

        // NULLIF(...,'') => boş string eşleşmesin
        return $query->whereRaw("NULLIF({$column}->>?, '') ILIKE ?", [$ui, "%{$needle}%"]);
    }
}
