<?php

namespace App\Enums;

enum PersonType: string
{
    case ADULT = 'adult';
    case CHILD = 'child';
    case INFANT = 'infant';
    case SENIOR = 'senior';

    public function label(): string
    {
        return match ($this) {
            self::ADULT => __('Yetişkin'),
            self::CHILD => __('Çocuk'),
            self::INFANT => __('Bebek'),
            self::SENIOR => __('Yaşlı'),
        };
    }

    public function ageRange(): array
    {
        return match ($this) {
            self::ADULT => [12, 64],
            self::CHILD => [2, 11],
            self::INFANT => [0, 1],
            self::SENIOR => [65, 120],
        };
    }
}
