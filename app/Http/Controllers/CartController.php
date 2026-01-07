<?php

namespace App\Http\Controllers;

use App\Services\CampaignPlacementViewService;
use App\Services\CouponViewModelService;
use App\Services\CampaignViewModelService;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(
        Request $request,
        CouponViewModelService $couponVm,
        CampaignViewModelService $campaignVm,
        CampaignPlacementViewService $campaignPlacement
    ) {
        $cart = session('cart', [
            'items' => [],
        ]);

        $items = $cart['items'] ?? [];

        $cartItems    = $items;
        $cartSubtotal = 0;
        $cartCurrency = null;

        foreach ($items as $ci) {
            $amount = (float) ($ci['amount'] ?? 0);
            $cartSubtotal += $amount;

            if ($cartCurrency === null && ! empty($ci['currency'])) {
                $cartCurrency = $ci['currency'];
            }
        }

        $cartCoupons         = [];
        $appliedCouponIds    = (array) session('cart.applied_coupons', []);
        $couponDiscountTotal = 0.0;

        // Kampanyalar için sepet değişkenleri
        $cartCampaigns         = [];
        $campaignDiscountTotal = 0.0;

        $user = $request->user();
        if ($user && $cartSubtotal > 0 && $cartCurrency) {
            $userCurrency = CurrencyHelper::currentCode();

            // -----------------------------
            // Kuponlar
            // -----------------------------
            $cartCoupons = $couponVm->buildCartCouponsForUser(
                $user,
                $userCurrency,
                $cartSubtotal,
                $cartCurrency
            );

            foreach ($cartCoupons as &$vm) {
                $id = $vm['id'] ?? null;

                $isApplied         = $id !== null && in_array($id, $appliedCouponIds, true);
                $vm['is_applied']  = $isApplied;

                if ($isApplied && empty($vm['is_applicable'])) {
                    $appliedCouponIds = array_values(array_filter(
                        $appliedCouponIds,
                        fn($aid) => (int)$aid !== (int)$id
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
            // Kampanyalar
            // -----------------------------
            $cartCampaigns = $campaignVm->buildCartCampaignsForUser(
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

        $finalTotal = max(0, $cartSubtotal - $couponDiscountTotal - $campaignDiscountTotal);

        return view('pages.cart.index', [
            'cartItems'           => $cartItems,
            'cartSubtotal'        => $cartSubtotal,
            'cartCurrency'        => $cartCurrency,
            'cartCoupons'         => $cartCoupons,
            'couponDiscountTotal' => $couponDiscountTotal,
            'finalTotal'          => $finalTotal,

            'cartCampaigns'         => $cartCampaigns,
            'campaignDiscountTotal' => $campaignDiscountTotal,

            'campaigns' => $campaignPlacement->buildForPlacement('basket'),
        ]);
    }

    public function remove(string $key, Request $request)
    {
        $items = session('cart.items', []);

        if (array_key_exists($key, $items)) {
            unset($items[$key]);

            if (empty($items)) {
                session()->forget('cart');
                session()->forget('cart.applied_coupons');
            } else {
                session(['cart.items' => $items]);
            }
        }

        return redirect()
            ->back()
            ->with('ok', 'cart_item_removed');
    }

    public function applyCoupon(Request $request, CouponViewModelService $couponVm)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()
                ->route('login', ['redirect' => '/cart'])
                ->with('err', 'err_login_required');
        }

        $userCouponId = (int) $request->input('user_coupon_id');

        $cart  = session('cart', ['items' => []]);
        $items = $cart['items'] ?? [];

        if (empty($items)) {
            return redirect()
                ->back()
                ->with('err', 'err_cart_empty');
        }

        $cartSubtotal = 0;
        $cartCurrency = null;

        foreach ($items as $ci) {
            $amount = (float) ($ci['amount'] ?? 0);
            $cartSubtotal += $amount;

            if ($cartCurrency === null && ! empty($ci['currency'])) {
                $cartCurrency = $ci['currency'];
            }
        }

        if ($cartSubtotal <= 0 || ! $cartCurrency) {
            return redirect()
                ->back()
                ->with('err', 'err_no_amount');
        }

        $userCurrency = CurrencyHelper::currentCode();

        $cartCoupons = $couponVm->buildCartCouponsForUser(
            $user,
            $userCurrency,
            $cartSubtotal,
            $cartCurrency
        );

        $target = collect($cartCoupons)->firstWhere('id', $userCouponId);

        if (! $target || empty($target['is_applicable'])) {
            return redirect()
                ->back()
                ->with('err', 'err_not_applicable');
        }

        $applied = (array) session('cart.applied_coupons', []);
        $applied = array_map('intval', $applied);

        if (in_array($userCouponId, $applied, true)) {
            return redirect()
                ->back()
                ->with('ok', 'coupon_applied');
        }

        $isExclusive = ! empty($target['is_exclusive']);

        if ($isExclusive) {
            $applied = [$userCouponId];
        } else {
            if (! empty($applied)) {
                $hasExclusive = collect($cartCoupons)
                    ->filter(fn (array $vm) => in_array((int) ($vm['id'] ?? 0), $applied, true))
                    ->contains(fn (array $vm) => ! empty($vm['is_exclusive']));

                if ($hasExclusive) {
                    return redirect()
                        ->back()
                        ->with('err', 'err_exclusive_block');
                }
            }

            $applied[] = $userCouponId;
            $applied   = array_values(array_unique($applied));
        }

        session(['cart.applied_coupons' => $applied]);

        return redirect()
            ->back()
            ->with('ok', 'coupon_applied');
    }

    public function removeCoupon(Request $request)
    {
        $userCouponId = (int) $request->input('user_coupon_id');

        $applied = (array) session('cart.applied_coupons', []);

        if ($userCouponId) {
            $applied = array_values(array_filter(
                $applied,
                fn ($id) => (int) $id !== $userCouponId
            ));
        }

        session(['cart.applied_coupons' => $applied]);

        return redirect()
            ->back()
            ->with('ok', 'coupon_removed');
    }
}
