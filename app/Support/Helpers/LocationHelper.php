<?php

namespace App\Support\Helpers;

class LocationHelper
{
    protected static array $data = [];

    protected static function load(): array
    {
        if (! static::$data) {
            $path = resource_path('data/locations/tr.json');
            if (file_exists($path)) {
                static::$data = json_decode(file_get_contents($path), true) ?? [];
            }
        }

        return static::$data;
    }

    public static function getProvinces(): array
    {
        $countries = static::load()['countries'] ?? [];

        return $countries[0]['provinces'] ?? [];
    }

    public static function getDistricts(?string $provinceSlug): array
    {
        if (! $provinceSlug) {
            return [];
        }
        foreach (static::getProvinces() as $province) {
            if ($province['slug'] === $provinceSlug) {
                return $province['districts'] ?? [];
            }
        }

        return [];
    }

    public static function getAreas(?string $districtSlug): array
    {
        if (! $districtSlug) {
            return [];
        }
        foreach (static::getProvinces() as $province) {
            foreach ($province['districts'] ?? [] as $district) {
                if ($district['slug'] === $districtSlug) {
                    return $district['areas'] ?? [];
                }
            }
        }

        return [];
    }
}
