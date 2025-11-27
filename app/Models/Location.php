<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'type',
        'code',
        'name',
        'slug',
        'path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (empty($model->slug) && ! empty($model->name)) {
                $model->slug = \Illuminate\Support\Str::slug(
                    is_array($model->name)
                        ? ($model->name['tr'] ?? reset($model->name))
                        : $model->name
                );
            }

            // Path otomatik oluştur
            $model->path = $model->generatePath();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getFullPathAttribute(): string
    {
        return $this->path ?: $this->generatePath();
    }

    public function generatePath(): string
    {
        $segments = [];
        $node     = $this;

        while ($node) {
            $segments[] = $node->slug;
            $node       = $node->parent;
        }

        return implode('/', array_reverse($segments));
    }

    /**
     * Verilen type için (country/province/district/area) yukarı doğru
     * ilk bulduğu location kaydını döndürür.
     */
    public function getAncestorByType(string $type): ?self
    {
        $node = $this;

        while ($node) {
            if ($node->type === $type) {
                return $node;
            }
            $node = $node->parent;
        }

        return null;
    }

    /**
     * Verilen type için ancestor ismini döndürür.
     * Şu an name alanı string, ileride array olursa onu da tolere eder.
     */
    public function getAncestorName(string $type, string $loc = 'tr'): ?string
    {
        $location = $this->getAncestorByType($type);
        if (! $location) {
            return null;
        }

        $name = $location->name;

        if (is_array($name)) {
            return $name[$loc]
                ?? ($name[array_key_first($name)] ?? null);
        }

        return $name !== null ? (string) $name : null;
    }

    /**
     * Örnek: "İçmeler / Marmaris / Muğla" gibi bir etiket üretir.
     */
    public function displayLabel(string $loc = 'tr'): string
    {
        $names = [];

        for ($n = $this; $n; $n = $n->parent) {
            if (is_array($n->name)) {
                $names[] = $n->name[$loc]
                    ?? ($n->name[array_key_first($n->name)] ?? '');
            } else {
                $names[] = (string) $n->name;
            }
        }

        return implode(' / ', array_filter(array_reverse($names)));
    }
}
