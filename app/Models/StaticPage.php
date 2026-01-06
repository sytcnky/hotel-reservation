<?php

namespace App\Models;

use App\Support\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


use App\Support\Helpers\MediaConversions;

class StaticPage extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'key',
        'is_active',
        'sort_order',
        'content',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order'=> 'integer',
        'content'   => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    |
    | Home page:
    | - hero background
    | - hero transparent (girl / overlay görseli)
    | - popular hotels promo bg
    */
    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name');

        $accepts = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        $this
            ->addMediaCollection('home_hero_background')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes($accepts);

        $this
            ->addMediaCollection('home_hero_transparent')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes($accepts);

        $this
            ->addMediaCollection('home_popular_hotels_hero')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes($accepts);

        $this
            ->addMediaCollection('transfer_content_image')
            ->singleFile()
            ->useDisk($disk)
            ->acceptsMimeTypes($accepts);

        $this
            ->addMediaCollection('villa_content_images')
            ->useDisk($disk)
            ->acceptsMimeTypes($accepts);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        MediaConversions::apply($this, 'home_hero_background');
        MediaConversions::apply($this, 'home_hero_transparent');
        MediaConversions::apply($this, 'home_popular_hotels_hero');
        MediaConversions::apply($this, 'transfer_content_image');
        MediaConversions::apply($this, 'villa_content_images');
    }

    /*
    |--------------------------------------------------------------------------
    | Image Accessors
    |--------------------------------------------------------------------------
    |
    | <x-responsive-image :image="..." preset="...">
    */
    public function getHomeHeroBackgroundImageAttribute(): array
    {
        return ImageHelper::normalize(
            $this->getFirstMedia('home_hero_background')
        );
    }

    public function getHomeHeroTransparentImageAttribute(): array
    {
        return ImageHelper::normalize(
            $this->getFirstMedia('home_hero_transparent')
        );
    }

    public function getHomePopularHotelsHeroImageAttribute(): array
    {
        return ImageHelper::normalize(
            $this->getFirstMedia('home_popular_hotels_hero')
        );
    }

    public function getTransferContentImageAttribute(): array
    {
        $media = $this->getFirstMedia('transfer_content_image');

        return ImageHelper::normalize($media);
    }

    public function getVillaContentImagesAttribute(): array
    {
        return $this->getMedia('villa_content_images')
            ->map(fn (Media $media) => ImageHelper::normalize($media))
            ->values()
            ->all();
    }
}
