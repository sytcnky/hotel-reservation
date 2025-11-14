<?php

namespace App\Support\Helpers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaConversions
{
    public static function apply($model, string $collectionName = null): void
    {
        $c = $collectionName;

        // Thumbnail
        $model->addMediaConversion('thumb')
            ->width(300)
            ->format('webp')
            ->performOnCollections($c);

        // Thumbnail retina
        $model->addMediaConversion('thumb2x')
            ->width(600)
            ->format('webp')
            ->performOnCollections($c);

        // Small (mobil slot)
        $model->addMediaConversion('small')
            ->width(450)
            ->format('webp')
            ->performOnCollections($c);

        // Small retina
        $model->addMediaConversion('small2x')
            ->width(900)
            ->format('webp')
            ->performOnCollections($c);

        // Large (desktop slot)
        $model->addMediaConversion('large')
            ->width(900)
            ->format('webp')
            ->performOnCollections($c);

        // Large retina
        $model->addMediaConversion('large2x')
            ->width(1800)
            ->format('webp')
            ->performOnCollections($c);
    }
}
