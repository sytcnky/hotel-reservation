<?php

namespace App\Http\Requests\Concerns;

use Carbon\Carbon;

trait NormalizesBookingSnapshot
{
    protected function normalizeDateToYmd(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === '' || $value === null) {
            return null;
        }

        foreach ([
                     'Y-m-d',
                     'd.m.Y',
                     'd/m/Y',
                     'd-m-Y',
                     'Y/m/d',
                     'Y-m-d H:i',
                     'd.m.Y H:i',
                     'd/m/Y H:i',
                 ] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
                // denemeye devam
            }
        }

        // En sonda serbest parse (çok toleranslı); parse edemezse exception fırlatır
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value; // validasyon zaten yakalayacak; burada bozmayalım
        }
    }

    protected function normalizeCurrency(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = strtoupper(trim($value));

        return $value === '' ? null : $value;
    }
}
