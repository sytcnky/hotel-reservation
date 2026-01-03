<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Support\Helpers\MediaConversions;
use App\Support\Helpers\ImageHelper;

class TravelGuide extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'travel_guides';

    protected $fillable = [
        'title',
        'excerpt',
        'slug',
        'canonical_slug',
        'tags',
        'sidebar_tour_ids',
        'is_active',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'title'            => 'array',
        'excerpt'          => 'array',
        'slug'             => 'array',
        'tags'             => 'array',
        'sidebar_tour_ids' => 'array',
        'is_active'        => 'boolean',
        'published_at'     => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function blocks(): HasMany
    {
        return $this->hasMany(TravelGuideBlock::class)->orderBy('sort_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    */

    public function registerMediaCollections(): void
    {
        // Kapak (tek dosya) — Hotel / Villa / Tour ile AYNI
        $this
            ->addMediaCollection('cover')
            ->singleFile()
            ->useDisk(config('media-library.disk_name'))
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Standart ICR dönüşümleri — AYNI
        MediaConversions::apply($this, 'cover');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (image helpers)
    |--------------------------------------------------------------------------
    */

    public function getCoverImageAttribute(): array
    {
        $media = $this->getFirstMedia('cover');

        return ImageHelper::normalize($media);
    }

    /*
    |--------------------------------------------------------------------------
    | Slug Mutator
    |--------------------------------------------------------------------------
    */

    protected function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = is_array($value)
            ? json_encode($value)
            : $value;

        $base = config('app.locale', 'tr');

        $slugArr = is_array($value)
            ? $value
            : json_decode($this->attributes['slug'] ?? '[]', true);

        $title    = $this->attributes['title'] ?? '{}';
        $titleArr = is_array($title) ? $title : json_decode($title, true);

        $this->attributes['canonical_slug'] = Str::slug(
            $slugArr[$base]
            ?? ($slugArr[array_key_first($slugArr) ?? '']
            ?? ($titleArr[$base] ?? 'gezi-rehberi'))
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (TravelGuide $guide) {
            if (empty($guide->canonical_slug)) {
                $base  = config('app.locale', 'tr');
                $slug  = (array) ($guide->slug ?? []);
                $title = (array) ($guide->title ?? []);

                $guide->canonical_slug = Str::slug(
                    $slug[$base]
                    ?? ($slug[array_key_first($slug) ?? '']
                    ?? ($title[$base] ?? 'gezi-rehberi'))
                );
            }
        });

        static::saving(function (TravelGuide $guide) {
            if (empty($guide->canonical_slug)) {
                $base  = config('app.locale', 'tr');
                $slug  = (array) ($guide->slug ?? []);
                $title = (array) ($guide->title ?? []);

                $guide->canonical_slug = Str::slug(
                    $slug[$base]
                    ?? ($slug[array_key_first($slug) ?? '']
                    ?? ($title[$base] ?? 'gezi-rehberi'))
                );
            }
        });
    }
}
