<?php

namespace App\Models;

use App\Support\Helpers\ImageHelper;
use App\Support\Helpers\MediaConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TravelGuide extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'travel_guides';

    protected $fillable = [
        'title',
        'excerpt',
        'slug',
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

    protected $appends = [
        'title_l',
        'slug_l',
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
        MediaConversions::apply($this, 'cover');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    | Kontrat (Admin): fallback yok. Sadece UI locale key'i okunur.
    */

    public function getTitleLAttribute(): string
    {
        $uiLocale = app()->getLocale();
        $v = is_array($this->title) ? ($this->title[$uiLocale] ?? '') : '';

        return is_string($v) ? trim($v) : '';
    }

    public function getSlugLAttribute(): string
    {
        $uiLocale = app()->getLocale();
        $v = is_array($this->slug) ? ($this->slug[$uiLocale] ?? '') : '';

        return is_string($v) ? trim($v) : '';
    }

    public function getCoverImageAttribute(): array
    {
        $media = $this->getFirstMedia('cover');

        return ImageHelper::normalize($media);
    }
}
