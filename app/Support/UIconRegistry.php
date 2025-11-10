<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class UIconRegistry
{
    // prefix eşlemesi
    private const MAP = [
        'outline' => 'rr',
        'bold'    => 'br',
        'solid'   => 'sr',
        'straight'=> 'ss',
        'thin'    => 'tn',
    ];

    public static function list(array|string $variants = 'all'): array
    {
        $key = 'uicons:'.(is_array($variants) ? implode(',', $variants) : $variants);

        return Cache::remember($key, now()->addDay(), function () use ($variants) {
            $cssPath = base_path('node_modules/@flaticon/flaticon-uicons/css/all/all.css');
            if (! is_file($cssPath)) {
                return [];
            }
            $css = file_get_contents($cssPath) ?: '';

            // fi-xx-icon-name:before
            preg_match_all('/\.fi-(rr|br|sr|ss|tn)-([a-z0-9\-]+):before/i', $css, $m, PREG_SET_ORDER);

            // varyant filtresi
            $allowed = null;
            if ($variants !== 'all') {
                $allowed = [];
                foreach ((array) $variants as $v) {
                    if (isset(self::MAP[$v])) $allowed[] = self::MAP[$v];
                    if (in_array($v, ['rr','br','sr','ss','tn'], true)) $allowed[] = $v;
                }
                $allowed = array_unique($allowed);
            }

            $out = [];
            foreach ($m as $hit) {
                [$full, $pref, $name] = $hit;
                if ($allowed && ! in_array($pref, $allowed, true)) continue;
                $out[] = "fi fi-{$pref}-{$name}";
            }

            // benzersiz + sıralı
            $out = array_values(array_unique($out));
            sort($out, SORT_NATURAL);

            return $out;
        });
    }
}
