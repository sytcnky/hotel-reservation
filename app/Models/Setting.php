<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    // value cast'ini kaldır: hem string hem json tutuyoruz.
    protected $casts = [
        'value' => 'string',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $raw = static::query()
            ->where('key', $key)
            ->value('value');

        if ($raw === null) {
            return $default;
        }

        // Çağıran array bekliyorsa JSON decode et.
        if (is_array($default)) {
            $decoded = json_decode((string) $raw, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                ? $decoded
                : $default;
        }

        return $raw;
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif ($value === null) {
            $value = '';
        } else {
            $value = (string) $value;
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }
}
