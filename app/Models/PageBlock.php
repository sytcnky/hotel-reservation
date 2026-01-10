<?php

namespace App\Models;

use App\Support\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use App\Support\Helpers\MediaConversions;

class PageBlock extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'static_page_id',
        'type',        // promo | collection
        'data',        // JSON
        'is_active',
        'sort_order',
        'slot',
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(StaticPage::class, 'static_page_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    |
    | Statik sayfalarda görsel alanları şimdilik single upload.
    */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('promo_image')
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
        MediaConversions::apply($this, 'promo_image');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (image helpers)
    |--------------------------------------------------------------------------
    |
    | <x-responsive-image :image="..." preset="..."> için normalize edilmiş image objesi.
    */
    public function getPromoImageAttribute(): array
    {
        $media = $this->getFirstMedia('promo_image');

        return ImageHelper::normalize($media);
    }
}
