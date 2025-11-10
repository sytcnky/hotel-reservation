<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Modelinize:
 *   - implements \Spatie\MediaLibrary\HasMedia
 *   - use HasMediaCollections;
 */
trait HasMediaCollections
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        // Tekil kapak
        $this->addMediaCollection('cover')
            ->useDisk(config('media-library.disk_name', 'public'))
            ->singleFile();

        // Sıralanabilir galeri
        $this->addMediaCollection('gallery')
            ->useDisk(config('media-library.disk_name', 'public'));

        // Belgeler
        $this->addMediaCollection('documents')
            ->useDisk(config('media-library.disk_name', 'public'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Admin küçük önizleme (tüm koleksiyonlar için)
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 300, 225)
            ->format('webp') // enum yerine string
            ->queued();

        // cover: 800x600 & 1600x1200 (webp)
        $this->addMediaConversion('cover_sm')
            ->fit(Fit::Crop, 800, 600)
            ->format('webp')
            ->performOnCollections('cover')
            ->queued();

        $this->addMediaConversion('cover_lg')
            ->fit(Fit::Crop, 1600, 1200)
            ->format('webp')
            ->performOnCollections('cover')
            ->queued();

        // gallery: 400w/800w/1600w (webp)
        foreach ([400, 800, 1600] as $w) {
            $this->addMediaConversion("{$w}w")
                ->width($w)
                ->format('webp')
                ->performOnCollections('gallery')
                ->queued();
        }
    }
}
