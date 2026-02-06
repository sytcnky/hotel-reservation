<?php

namespace App\Http\Controllers;

use App\Services\CampaignPlacementViewService;
use App\Services\CartInvariant;
use App\Services\CartPageViewService;
use App\Services\CouponViewModelService;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @param 'err'|'warn'|'notice'|'ok' $level
     */
    private function redirectNotice(string $toUrl, string $level, string $code, array $params = []): RedirectResponse
    {
        $level = in_array($level, ['err', 'warn', 'notice', 'ok'], true) ? $level : 'notice';

        $code = is_string($code) ? trim($code) : '';
        if ($code === '') {
            return redirect()->to($toUrl);
        }

        return redirect()
            ->to($toUrl)
            ->with('notices', [[
                'level'  => $level,
                'code'   => $code,
                'params' => is_array($params) ? $params : [],
            ]]);
    }

    /**
     * @param 'err'|'warn'|'notice'|'ok' $level
     */
    private function backNotice(string $level, string $code, array $params = []): RedirectResponse
    {
        $level = in_array($level, ['err', 'warn', 'notice', 'ok'], true) ? $level : 'notice';

        $code = is_string($code) ? trim($code) : '';
        if ($code === '') {
            return redirect()->back();
        }

        return redirect()
            ->back()
            ->with('notices', [[
                'level'  => $level,
                'code'   => $code,
                'params' => is_array($params) ? $params : [],
            ]]);
    }

    public function index(
        Request $request,
        CartPageViewService $cartPage,
        CampaignPlacementViewService $campaignPlacement
    ) {
        $data = $cartPage->buildIndexViewData($request);

        // Mixed currency guard (fail-fast)
        if (! empty($data['guard_triggered'])) {
            return $this->redirectNotice(
                localized_route('cart'),
                'err',
                'msg.err.cart.currency_mismatch'
            );
        }

        return view('pages.cart.index', [
            'cartItems'             => $data['cartItems'],
            'cartSubtotal'          => $data['cartSubtotal'],
            'cartCurrency'          => $data['cartCurrency'],
            'cartCoupons'           => $data['cartCoupons'],
            'couponDiscountTotal'   => $data['couponDiscountTotal'],
            'finalTotal'            => $data['finalTotal'],
            'cartCampaigns'         => $data['cartCampaigns'],
            'campaignDiscountTotal' => $data['campaignDiscountTotal'],
            'campaigns'             => $campaignPlacement->buildForPlacement('basket'),
        ]);
    }

    public function remove(string $key, Request $request)
    {
        $items = session('cart.items', []);

        if (array_key_exists($key, $items)) {
            unset($items[$key]);

            if (empty($items)) {
                session()->forget('cart');
            } else {
                session(['cart.items' => $items]);
            }
        }

        // Sessiz dönüş (msg yok)
        return redirect()->back();
    }

    public function applyCoupon(
        Request $request,
        CouponViewModelService $couponVm,
        CartInvariant $cartInvariant
    ) {
        $user = $request->user();
        if (! $user) {
            return redirect()
                ->route('login', ['redirect' => localized_route('cart')])
                ->with('notices', [[
                    'level'  => 'err',
                    'code'   => 'msg.err.auth.login_required',
                    'params' => [],
                ]]);
        }

        $userCouponId = (int) $request->input('user_coupon_id');

        $cart  = session('cart', ['items' => []]);
        $items = (array) ($cart['items'] ?? []);

        if (empty($items)) {
            return $this->backNotice('err', 'msg.err.cart.empty');
        }

        // Currency invariant
        if ($cartInvariant->resetIfCurrencyMismatch($items)) {
            return $this->backNotice('err', 'msg.err.cart.currency_mismatch');
        }

        [$cartSubtotal, $cartCurrency] = $cartInvariant->computeSubtotalAndCurrency($items);

        if ((float) $cartSubtotal <= 0 || ! $cartCurrency) {
            return $this->backNotice('err', 'msg.err.cart.no_amount');
        }

        $userCurrency = CurrencyHelper::currentCode();

        $cartCoupons = $couponVm->buildCartCouponsForUser(
            $user,
            $userCurrency,
            (float) $cartSubtotal,
            (string) $cartCurrency,
            $items
        );

        $target = collect($cartCoupons)->firstWhere('id', $userCouponId);

        // Uygulanamaz kupon → GLOBAL hata (notices)
        if (! $target || empty($target['is_applicable'])) {
            return $this->backNotice('err', 'msg.err.coupon.not_applicable');
        }

        $applied = (array) session('cart.applied_coupons', []);
        $applied = array_map('intval', $applied);

        // Idempotent: zaten uygulanmışsa sessizce dön
        if (in_array($userCouponId, $applied, true)) {
            return redirect()->back();
        }

        $isExclusive = ! empty($target['is_exclusive']);

        if ($isExclusive) {
            $applied = [$userCouponId];
        } else {
            if (! empty($applied)) {
                $hasExclusive = collect($cartCoupons)
                    ->filter(fn (array $vm) => in_array((int) ($vm['id'] ?? 0), $applied, true))
                    ->contains(fn (array $vm) => ! empty($vm['is_exclusive']));

                // Exclusive çakışması → GLOBAL hata
                if ($hasExclusive) {
                    return $this->backNotice('err', 'msg.err.coupon.exclusive_conflict');
                }
            }

            $applied[] = $userCouponId;
            $applied   = array_values(array_unique($applied));
        }

        session(['cart.applied_coupons' => $applied]);

        // Başarı mesajı YOK
        return redirect()->back();
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

        // Başarı mesajı YOK
        return redirect()->back();
    }
}
