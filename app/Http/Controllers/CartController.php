<?php

namespace App\Http\Controllers;

use App\Services\CampaignPlacementViewService;
use App\Services\CartInvariant;
use App\Services\CartPageViewService;
use App\Services\CouponViewModelService;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(
        Request $request,
        CartPageViewService $cartPage,
        CampaignPlacementViewService $campaignPlacement
    ) {
        $data = $cartPage->buildIndexViewData($request);

        // Mixed currency guard (fail-fast) — mevcut davranış: mismatch => cart reset + redirect err
        if (!empty($data['guard_triggered'])) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_currency_mismatch');
        }

        return view('pages.cart.index', [
            'cartItems'           => $data['cartItems'],
            'cartSubtotal'        => $data['cartSubtotal'],
            'cartCurrency'        => $data['cartCurrency'],
            'cartCoupons'         => $data['cartCoupons'],
            'couponDiscountTotal' => $data['couponDiscountTotal'],
            'finalTotal'          => $data['finalTotal'],

            'cartCampaigns'         => $data['cartCampaigns'],
            'campaignDiscountTotal' => $data['campaignDiscountTotal'],

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

    public function applyCoupon(
        Request $request,
        CouponViewModelService $couponVm,
        CartInvariant $cartInvariant
    ) {
        $user = $request->user();
        if (! $user) {
            return redirect()
                ->route('login', ['redirect' => '/cart'])
                ->with('err', 'err_login_required');
        }

        $userCouponId = (int) $request->input('user_coupon_id');

        $cart  = session('cart', ['items' => []]);
        $items = (array) ($cart['items'] ?? []);

        if (empty($items)) {
            return redirect()
                ->back()
                ->with('err', 'err_cart_empty');
        }

        // Mixed currency guard (fail-fast) — mevcut davranış: mismatch => cart reset + err
        if ($cartInvariant->resetIfCurrencyMismatch($items)) {
            return redirect()
                ->back()
                ->with('err', 'err_cart_currency_mismatch');
        }

        [$cartSubtotal, $cartCurrency] = $cartInvariant->computeSubtotalAndCurrency($items);

        if ((float) $cartSubtotal <= 0 || ! $cartCurrency) {
            return redirect()
                ->back()
                ->with('err', 'err_no_amount');
        }

        $userCurrency = CurrencyHelper::currentCode();

        $cartCoupons = $couponVm->buildCartCouponsForUser(
            $user,
            $userCurrency,
            (float) $cartSubtotal,
            (string) $cartCurrency
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
