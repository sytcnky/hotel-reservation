<?php

namespace App\Support\Helpers;

class CampaignColorHelper
{
    /**
     * Bootstrap bg-* class'ını hex renge çevirir.
     * Yalnızca Filament içi görsel önizleme için kullanılır.
     */
    public static function backgroundHexFromClass(?string $class): string
    {
        // Senin paletine göre revize edilmiş renkler
        $map = [
            'bg-primary'          => '#1E3A5F', // Gece mavisi
            'bg-primary-subtle'   => '#61758F', // Primary %30 açılmış

            'bg-secondary'        => '#6C7A89', // Dingin gri
            'bg-secondary-subtle' => '#98A1AC', // Secondary %30 açılmış

            'bg-success'          => '#27AE60', // Yeşil
            'bg-success-subtle'   => '#67C68F', // Success %30 açılmış

            'bg-danger'           => '#C0392B', // Kırmızı
            'bg-danger-subtle'    => '#D2746A', // Danger %30 açılmış

            'bg-warning'          => '#F39C12', // Kehribar
            'bg-warning-subtle'   => '#F6B959', // Warning %30 açılmış

            'bg-info'             => '#3498DB', // Temiz mavi
            'bg-info-subtle'      => '#70B6E5', // Info %30 açılmış

            'bg-light'            => '#F8F9FA', // Açık gri
            'bg-light-subtle'     => '#FFFFFF', // Daha açık varyant

            'bg-dark'             => '#2C3E50', // Koyu gri
            'bg-dark-subtle'      => '#6B7784', // Dark %30 açılmış
        ];

        $class = $class ?: 'bg-primary';

        return $map[$class] ?? '#1E3A5F';
    }
}
