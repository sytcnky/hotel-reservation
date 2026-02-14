<?php

namespace App\Services;

class CartInvariant
{
    /**
     * Items içinde currency mismatch var mı? (null/boş currency alanları yok sayılır)
     */
    public function currencyMismatch(array $items): bool
    {
        $cartCurrency = null;

        foreach ($items as $ci) {
            $c = $ci['currency'] ?? null;
            if (! $c) {
                continue;
            }

            $c = strtoupper((string) $c);

            if ($cartCurrency === null) {
                $cartCurrency = $c;
                continue;
            }

            if ($c !== $cartCurrency) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sepette currency mismatch varsa cart'ı invalidate eder.
     * - cart + applied_coupons temizlenir
     * - true döner (guard triggered)
     */
    public function resetIfCurrencyMismatch(array $items): bool
    {
        if ($this->currencyMismatch($items)) {
            $this->resetCart();
            return true;
        }

        return false;
    }

    /**
     * Cart subtotal + currency hesaplar.
     *
     * @return array{0: float, 1: ?string} [cartSubtotal, cartCurrency]
     */
    public function computeSubtotalAndCurrency(array $items): array
    {
        $cartSubtotal = 0.0;
        $cartCurrency = null;

        foreach ($items as $ci) {
            $amount = (float) ($ci['amount'] ?? 0);
            $cartSubtotal += $amount;

            if ($cartCurrency === null && ! empty($ci['currency'])) {
                $cartCurrency = strtoupper((string) $ci['currency']);
            }
        }

        return [$cartSubtotal, $cartCurrency];
    }

    /**
     * Sepete incoming currency kabul edilebilir mi?
     * - Items içinde mismatch varsa: false
     * - Sepet currency'i varsa ve incoming farklıysa: false
     */
    public function canAcceptIncomingCurrency(array $items, string $incomingCurrency): bool
    {
        if ($this->currencyMismatch($items)) {
            return false;
        }

        $incomingCurrency = strtoupper((string) $incomingCurrency);

        $cartCurrency = null;

        foreach ($items as $ci) {
            $c = $ci['currency'] ?? null;
            if (! $c) {
                continue;
            }

            $c = strtoupper((string) $c);

            if ($cartCurrency === null) {
                $cartCurrency = $c;
                break;
            }
        }

        if ($cartCurrency !== null && $incomingCurrency !== $cartCurrency) {
            return false;
        }

        return true;
    }

    /**
     * Mevcut cart items + incoming currency ile invariant'ı kontrol eder;
     * geçmiyorsa cart'ı resetler.
     *
     * Davranış:
     * - items içinde mismatch varsa reset + false
     * - cart currency mevcut ve incoming farklıysa reset + false
     * - aksi halde true
     */
    public function resetIfCannotAcceptIncomingCurrency(array $items, string $incomingCurrency): bool
    {
        if (! $this->canAcceptIncomingCurrency($items, $incomingCurrency)) {
            $this->resetCart();
            return true;
        }

        return false;
    }

    /**
     * Tek otorite: cart + applied_coupons birlikte temizlenir.
     */
    public function resetCart(): void
    {
        session()->forget('cart');
        session()->forget('cart.applied_coupons');
    }
}
