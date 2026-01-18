<?php

namespace App\Services;

use App\Support\Currency\CurrencyContext;
use Illuminate\Http\Request;

class CartPageViewService
{
    public function __construct(
        private readonly CartInvariant $cartInvariant,
        private readonly CouponViewModelService $couponVm,
        private readonly CampaignViewModelService $campaignVm,
    ) {}

    /**
     * Cart index sayfası için tüm view datayı üretir.
     * - Mixed currency guard: mismatch varsa cart resetlenmiş kabul edilir.
     * - Applied coupon cleanup: applied ama artık applicable değilse session’dan düşürür (mevcut davranış).
     *
     * @return array{
     *   guard_triggered: bool,
     *   cartItems: array,
     *   cartSubtotal: float,
     *   cartCurrency: ?string,
     *   cartCoupons: array,
     *   appliedCouponIds: array,
     *   couponDiscountTotal: float,
     *   cartCampaigns: array,
     *   campaignDiscountTotal: float,
     *   finalTotal: float
     * }
     */
    public function buildIndexViewData(Request $request): array
    {
        $cart = session('cart', [
            'items' => [],
        ]);

        $items = (array) ($cart['items'] ?? []);

        // Mixed currency guard (fail-fast) — mevcut davranış: mismatch => cart reset + redirect err
        if ($this->cartInvariant->resetIfCurrencyMismatch($items)) {
            return [
                'guard_triggered'       => true,
                'cartItems'             => [],
                'cartSubtotal'          => 0.0,
                'cartCurrency'          => null,
                'cartCoupons'           => [],
                'appliedCouponIds'       => [],
                'couponDiscountTotal'   => 0.0,
                'cartCampaigns'         => [],
                'campaignDiscountTotal' => 0.0,
                'finalTotal'            => 0.0,
            ];
        }

        [$cartSubtotal, $cartCurrency] = $this->cartInvariant->computeSubtotalAndCurrency($items);

        $cartItems = $items;

        $cartCoupons         = [];
        $appliedCouponIds    = (array) session('cart.applied_coupons', []);
        $couponDiscountTotal = 0.0;

        $cartCampaigns         = [];
        $campaignDiscountTotal = 0.0;

        $user = $request->user();

        if ($user && $cartSubtotal > 0 && $cartCurrency) {
            $userCurrency = CurrencyContext::code($request);

            // -----------------------------
            // Kuponlar (mevcut akış)
            // -----------------------------
            $cartCoupons = $this->couponVm->buildCartCouponsForUser(
                $user,
                $userCurrency,
                $cartSubtotal,
                $cartCurrency
            );

            foreach ($cartCoupons as &$vm) {
                $id = $vm['id'] ?? null;

                $isApplied        = $id !== null && in_array($id, $appliedCouponIds, true);
                $vm['is_applied'] = $isApplied;

                // applied ama artık applicable değil -> applied listeden düşür (mevcut davranış)
                if ($isApplied && empty($vm['is_applicable'])) {
                    $appliedCouponIds = array_values(array_filter(
                        $appliedCouponIds,
                        fn ($aid) => (int) $aid !== (int) $id
                    ));

                    session(['cart.applied_coupons' => $appliedCouponIds]);

                    $vm['is_applied'] = false;
                    continue;
                }

                if ($isApplied) {
                    $couponDiscountTotal += (float) ($vm['calculated_discount'] ?? 0);
                }
            }
            unset($vm);

            // -----------------------------
            // Kampanyalar (mevcut akış)
            // -----------------------------
            $cartCampaigns = $this->campaignVm->buildCartCampaignsForUser(
                $user,
                $cartItems,
                $cartCurrency,
                $cartSubtotal
            );

            foreach ($cartCampaigns as $cvm) {
                if (! empty($cvm['is_applicable'])) {
                    $campaignDiscountTotal += (float) ($cvm['calculated_discount'] ?? 0);
                }
            }
        }

        $finalTotal = max(0.0, $cartSubtotal - $couponDiscountTotal - $campaignDiscountTotal);

        return [
            'guard_triggered'       => false,
            'cartItems'             => $cartItems,
            'cartSubtotal'          => (float) $cartSubtotal,
            'cartCurrency'          => $cartCurrency,
            'cartCoupons'           => $cartCoupons,
            'appliedCouponIds'      => $appliedCouponIds,
            'couponDiscountTotal'   => (float) $couponDiscountTotal,
            'cartCampaigns'         => $cartCampaigns,
            'campaignDiscountTotal' => (float) $campaignDiscountTotal,
            'finalTotal'            => (float) $finalTotal,
        ];
    }
}
