<?php

namespace App\Support\Helpers;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

class SeoFriendlyFileNamer extends FileNamer
{
    /**
     * Orijinal dosyanın (uzantısız) temel adını üretir.
     */
    public function originalFileName(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $baseName  = pathinfo($fileName, PATHINFO_FILENAME);

        // 1) Unicode → ASCII
        $baseName = Str::ascii($baseName);

        // 2) Küçük harf + URL-friendly temizlik
        $baseName = Str::lower($baseName);
        $baseName = preg_replace('/[^a-z0-9]+/', '-', $baseName); // harf/rakam dışını -
        $baseName = trim($baseName, '-');

        // 3) Boş kalırsa fallback
        if ($baseName === '' || $baseName === null) {
            $baseName = 'image';
        }

        // 4) Aynı isim kullanılmışsa -1, -2, ... ekle
        return $this->makeUniqueBaseName($baseName, $extension);
    }

    /**
     * Conversion dosya adını üretir: {base}-{conversionName}
     */
    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        // Buradaki $fileName, originalFileName ile ürettiğimiz isim + uzantı.
        $stripped = pathinfo($fileName, PATHINFO_FILENAME);

        return "{$stripped}-{$conversion->getName()}";
    }

    /**
     * Responsive image dosya adını üretir.
     * (Spatie, responsive için ek info ekliyor; biz sadece base prefix'i belirliyoruz)
     */
    public function responsiveFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }

    /**
     * Aynı file_name varsa sonuna -1, -2, ... ekler.
     */
    protected function makeUniqueBaseName(string $baseName, string $extension): string
    {
        $candidate = $baseName;
        $i = 1;

        // file_name kolonu: "name.ext" formatında saklanıyor
        while (
        Media::where('file_name', $candidate . '.' . $extension)->exists()
        ) {
            $candidate = $baseName . '-' . $i;
            $i++;
        }

        return $candidate;
    }
}
