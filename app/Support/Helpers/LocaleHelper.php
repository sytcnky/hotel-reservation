<?php

namespace App\Support\Helpers;

use App\Models\Language;
use Illuminate\Support\Facades\Storage;

class LocaleHelper
{
    public static function active(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->all();
    }

    public static function options(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'native_name', 'flag'])
            ->map(function (Language $lang) {
                $flag = $lang->flag;

                if ($flag) {
                    // FileUpload artık public/flags içine yazıyor:
                    // DB'de tipik değer: "flags/xxxx.svg"
                    if (
                        ! str_starts_with($flag, 'http://')
                        && ! str_starts_with($flag, 'https://')
                        && ! str_starts_with($flag, '/')
                    ) {
                        $flag = Storage::disk('public')->url($flag); // => /storage/flags/xxxx.svg
                    }
                }

                return [
                    'code'  => $lang->code,
                    'label' => $lang->native_name ?: $lang->code,
                    'flag'  => $flag,
                ];
            })
            ->keyBy('code')
            ->toArray();
    }
}
