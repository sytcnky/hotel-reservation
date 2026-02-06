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

        // Mixed currency guard (fail-fast)
        if ($this->cartInvariant->resetIfCurrencyMismatch($items)) {
            return [
                'guard_triggered'       => true,
                'cartItems'             => [],
                'cartSubtotal'          => 0.0,
                'cartCurrency'          => null,
                'cartCoupons'           => [],
                'appliedCouponIds'      => [],
                'couponDiscountTotal'   => 0.0,
                'cartCampaigns'         => [],
                'campaignDiscountTotal' => 0.0,
                'finalTotal'            => 0.0,
            ];
        }

        [$cartSubtotal, $cartCurrency] = $this->cartInvariant->computeSubtotalAndCurrency($items);

        $cartItems = $items;

        $cartCoupons         = [];
        $couponDiscountTotal = 0.0;

        $cartCampaigns         = [];
        $campaignDiscountTotal = 0.0;

        // applied ids (session) -> normalize int + unique
        $appliedCouponIds = (array) session('cart.applied_coupons', []);
        $appliedCouponIds = array_values(array_unique(array_map('intval', $appliedCouponIds)));

        $user = $request->user();

        if ($user && $cartSubtotal > 0 && $cartCurrency) {
            $userCurrency = CurrencyContext::code($request);

            $cartCoupons = $this->couponVm->buildCartCouponsForUser(
                $user,
                $userCurrency,
                (float) $cartSubtotal,
                (string) $cartCurrency,
                $cartItems
            );

            /*
             |--------------------------------------------------------------------------
             | applied_coupons normalize (stale cleanup)
             |--------------------------------------------------------------------------
             | - cartCoupons'a hiç girmeyen (expired/not_started/used-out) kuponlar temizlenir
             | - is_applicable olmayanlar applied'dan düşer
             | - session update: tek sefer
             */
            $couponMap = []; // id => ['is_applicable'=>bool, 'index'=>int]
            foreach ($cartCoupons as $i => $vm) {
                $id = isset($vm['id']) ? (int) $vm['id'] : 0;
                if ($id > 0) {
                    $couponMap[$id] = [
                        'is_applicable' => ! empty($vm['is_applicable']),
                        'index'         => $i,
                    ];
                }
            }

            $normalizedApplied = [];
            foreach ($appliedCouponIds as $aid) {
                $aid = (int) $aid;
                if ($aid < 1) {
                    continue;
                }

                // cartCoupons içinde yoksa stale -> düş
                if (! isset($couponMap[$aid])) {
                    continue;
                }

                // applicable değilse applied kalmasın
                if ($couponMap[$aid]['is_applicable'] !== true) {
                    continue;
                }

                $normalizedApplied[] = $aid;
            }
            $normalizedApplied = array_values(array_unique($normalizedApplied));

            // session yaz (sadece değiştiyse)
            if ($normalizedApplied !== $appliedCouponIds) {
                session(['cart.applied_coupons' => $normalizedApplied]);
            }
            $appliedCouponIds = $normalizedApplied;

            // VM'lere is_applied bas + discount sum
            foreach ($cartCoupons as &$vm) {
                $id = isset($vm['id']) ? (int) $vm['id'] : 0;

                $isApplied = ($id > 0) && in_array($id, $appliedCouponIds, true);
                $vm['is_applied'] = $isApplied;

                if ($isApplied) {
                    $couponDiscountTotal += (float) ($vm['calculated_discount'] ?? 0);
                }
            }
            unset($vm);

            $cartCampaigns = $this->campaignVm->buildCartCampaignsForUser(
                $user,
                $cartItems,
                (string) $cartCurrency,
                (float) $cartSubtotal
            );

            foreach ($cartCampaigns as $cvm) {
                if (! empty($cvm['is_applicable'])) {
                    $campaignDiscountTotal += (float) ($cvm['calculated_discount'] ?? 0);
                }
            }
        }

        $finalTotal = max(
            0.0,
            (float) $cartSubtotal - (float) $couponDiscountTotal - (float) $campaignDiscountTotal
        );

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
