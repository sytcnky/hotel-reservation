<?php

namespace App\Services;

use App\Models\Villa;
use App\Models\VillaRateRule;

class VillaRateRuleSelector
{
    /**
     * Villa için seçilecek fiyat kuralı (tek otorite).
     *
     * Seçim kuralları (kilitli):
     * - currency code: case-insensitive
     * - is_active: true veya null
     * - closed: false veya null
     * - priority/date_start sırası
     */
    public function select(Villa $villa, string $currencyCode): ?VillaRateRule
    {
        $currencyCode = strtoupper(trim($currencyCode));
        if ($currencyCode === '') {
            return null;
        }

        return $villa->rateRules()
            ->whereHas('currency', function ($q) use ($currencyCode) {
                $q->whereRaw('upper(code) = ?', [$currencyCode]);
            })
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->where(function ($q) {
                $q->where('closed', false)->orWhereNull('closed');
            })
            ->orderBy('priority', 'asc')
            ->orderBy('date_start', 'asc')
            ->first();
    }
}
