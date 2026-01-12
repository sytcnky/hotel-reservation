<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'code',
        'symbol',
        'affix_position',
        'exponent',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name'        => 'array',
        'slug'        => 'array',
        'description' => 'array',
        'is_active'   => 'boolean',
        'exponent'    => 'integer',
    ];

    protected $appends = [
        'name_l',
        'slug_l',
    ];

    public function getNameLAttribute(): ?string
    {
        $raw = $this->getAttribute('name');

        if ($raw === null) {
            return null;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                return trim($raw) !== '' ? $raw : null;
            }
        }

        if (! is_array($raw)) {
            return null;
        }

        $ui = app()->getLocale();
        $val = $raw[$ui] ?? null;

        return is_string($val) && $val !== '' ? $val : null;
    }

    public function getSlugLAttribute(): ?string
    {
        $raw = $this->getAttribute('slug');

        if ($raw === null) {
            return null;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                return trim($raw) !== '' ? $raw : null;
            }
        }

        if (! is_array($raw)) {
            return null;
        }

        $ui = app()->getLocale();
        $val = $raw[$ui] ?? null;

        return is_string($val) && $val !== '' ? $val : null;
    }
}
