<?php

namespace App\Http\Requests\Concerns;

trait NormalizesBookingSnapshot
{
    /**
     * Kontrat: Booking (POST) tarih alanları strict Y-m-d gelir.
     * Legacy parse yok. Serbest Carbon::parse yok.
     * passedValidation() sonrası her zaman Y-m-d string veya null.
     */
    protected function normalizeDateToYmd(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === null || $value === '') {
            return null;
        }

        // Strict: Y-m-d. Değilse snapshot'a yazma.
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1
            ? $value
            : null;
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
