<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Helpers\CurrencyHelper;
use App\Services\CouponViewModelService;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    public function index(Request $request, CouponViewModelService $couponVm)
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        // AKTİF PARA BİRİMİ: KANONİK KAYNAK
        $userCurrency = CurrencyHelper::currentCode();

        $buckets = $couponVm->buildBucketsForUser($user, $userCurrency, 'account');

        $activeCoupons = $buckets['active'] ?? [];
        $pastCoupons   = $buckets['past'] ?? [];

        return view('pages.account.coupons', [
            'activeCoupons' => $activeCoupons,
            'pastCoupons'   => $pastCoupons,
        ]);
    }
}
