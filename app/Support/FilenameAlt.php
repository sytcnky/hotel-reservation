<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class FilenameAlt
{
    public static function from(Media $media, int $maxWords = 10): string
    {
        $name = pathinfo($media->file_name, PATHINFO_FILENAME);

        // tire, alt çizgi, nokta → boşluk
        $clean = preg_replace('/[-_.]+/', ' ', (string) $name);

        // harf/rakam ve boşluk dışı karakterleri at
        $clean = preg_replace('/[^[:alnum:]\s]/u', ' ', $clean);

        // fazladan boşlukları temizle
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        // kelime sınırı uygula
        $parts = explode(' ', $clean);
        if ($maxWords > 0 && count($parts) > $maxWords) {
            $parts = array_slice($parts, 0, $maxWords);
        }

        // baş harfleri büyüt
        $alt = mb_convert_case(implode(' ', $parts), MB_CASE_TITLE, 'UTF-8');

        return $alt !== '' ? $alt : 'Image';
    }
}
