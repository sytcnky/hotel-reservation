<?php

namespace App\Support\Helpers;

class MediaConversions
{
    public static function apply($model, string $collectionName = null): void
    {
        $c = $collectionName;

        // Mini thumbnail
        $model->addMediaConversion('thumb')
            ->width(150)
            ->format('webp')
            ->performOnCollections($c);

        // Mini thumbnail retina
        $model->addMediaConversion('thumb2x')
            ->width(300)
            ->format('webp')
            ->performOnCollections($c);

        // Small
        $model->addMediaConversion('small')
            ->width(320)
            ->format('webp')
            ->performOnCollections($c);

        // Small retina
        $model->addMediaConversion('small2x')
            ->width(640)
            ->format('webp')
            ->performOnCollections($c);

        // Large
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
