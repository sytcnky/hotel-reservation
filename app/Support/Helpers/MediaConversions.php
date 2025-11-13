<?php

namespace App\Support\Helpers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaConversions
{
    /**
     * Ortak conversion setini tüm modeller için uygular.
     */
    public static function apply($model, string $collectionName = null): void
    {
        // Basit alias
        $c = $collectionName;

        // thumb
        $model->addMediaConversion('thumb')
            ->width(300)
            ->format('webp')
            ->performOnCollections($c);

        // thumb2x (retina)
        $model->addMediaConversion('thumb2x')
            ->width(600)
            ->format('webp')
            ->performOnCollections($c);

        // mobile
        $model->addMediaConversion('mobile')
            ->width(450)
            ->format('webp')
            ->performOnCollections($c);

        // mobile2x (retina)
        $model->addMediaConversion('mobile2x')
            ->width(900)
            ->format('webp')
            ->performOnCollections($c);

        // desktop
        $model->addMediaConversion('desktop')
            ->width(900)
            ->format('webp')
            ->performOnCollections($c);

        // desktop2x (retina)
        $model->addMediaConversion('desktop2x')
            ->width(1800)
            ->format('webp')
            ->performOnCollections($c);
    }
}
