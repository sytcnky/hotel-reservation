<?php

namespace App\Support\Helpers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageHelper
{
    public static function normalize(?Media $media): array
    {
        if (! $media) {
            return [
                'thumb'    => asset('images/placeholders/thumb.webp'),
                'thumb2x'  => asset('images/placeholders/thumb2x.webp'),
                'small'    => asset('images/placeholders/small.webp'),
                'small2x'  => asset('images/placeholders/small2x.webp'),
                'large'    => asset('images/placeholders/large.webp'),
                'large2x'  => asset('images/placeholders/large2x.webp'),
                'alt'      => 'placeholder',
                'width'    => null,
                'height'   => null,
                'exists'   => false,
            ];
        }

        return [
            'thumb'    => $media->getUrl('thumb'),
            'thumb2x'  => $media->getUrl('thumb2x'),
            'small'    => $media->getUrl('small'),
            'small2x'  => $media->getUrl('small2x'),
            'large'    => $media->getUrl('large'),
            'large2x'  => $media->getUrl('large2x'),
            'alt'      => $media->name ?? 'image',
            'width'    => $media->getCustomProperty('width') ?? null,
            'height'   => $media->getCustomProperty('height') ?? null,
            'exists'   => true,
        ];
    }
}
