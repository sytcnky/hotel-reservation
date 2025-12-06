<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferBookingRequest;
use App\Http\Requests\TourBookingRequest;
use App\Http\Requests\HotelBookingRequest;
use App\Http\Requests\VillaBookingRequest;
use App\Models\Hotel;
use App\Models\UserCoupon;
use App\Models\Villa;
use App\Services\CouponViewModelService;
use App\Services\CampaignViewModelService;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Tüm sepet ekleme işlemleri için ortak helper.
     */
    private function addToCart(string $productType, int $productId, float $amount, string $currency, array $snapshot): void
    {
        $cart = session()->get('cart', [
            'items' => [],
        ]);

        $cart['items'][] = [
            'product_type' => $productType,
            'product_id'   => $productId,
            'amount'       => $amount,
            'currency'     => strtoupper($currency),
            'snapshot'     => $snapshot,
        ];

        session()->put('cart', $cart);
    }

    /**
     * Transfer booking -> sepete ekleme
     */
    public function bookTransfer(TransferBookingRequest $request)
    {
        // Validasyon
        $data = $request->validated();

        // Formdan gelen (validation dışında kalan) ek alanları da snapshot'a ekle
        foreach (['from_label', 'to_label', 'vehicle_image', 'vehicle_name'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        $snapshot = $data;

        $amount   = (float) ($snapshot['price_total'] ?? 0);
        $currency = $snapshot['currency'];
        $routeId  = (int) ($snapshot['route_id'] ?? 0);

        $this->addToCart(
            'transfer',
            $routeId,
            $amount,
            $currency,
            $snapshot,
        );

        // Sepete yönlendir + başarı mesajı
        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }

    /**
     * Excursion (tour) booking -> sepete ekleme
     */
    public function bookTour(TourBookingRequest $request)
    {
        $data = $request->validated();

        // Opsiyonel alanları snapshot'a ekle (görsel + kategori)
        foreach (['cover_image', 'category_name'] as $extraKey) {
            if ($request->filled($extraKey)) {
                $data[$extraKey] = $request->input($extraKey);
            }
        }

        // Null children/infants yerine 0 yazarak snapshot'ı normalize et
        $data['children'] = $data['children'] ?? 0;
        $data['infants']  = $data['infants'] ?? 0;

        $this->addToCart(
            'tour',
            (int) $data['tour_id'],
            (float) $data['price_total'],
            $data['currency'],
            $data,
        );

        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }

    /**
     * Hotel room booking -> sepete ekleme
     */
    public function bookHotel(HotelBookingRequest $request)
    {
        $data = $request->validated();

        // Null children yerine 0
        $data['children'] = $data['children'] ?? 0;

        // Snapshot temel olarak valid alanlar
        $snapshot = $data;

        // Opsiyonel metin alanı (ör: lokasyon etiketi)
        if ($request->filled('location_label')) {
            $snapshot['location_label'] = $request->input('location_label');
        }

        // Otel cover görselini çek → yoksa galeriden al
        $hotel = Hotel::query()
            ->with('media')
            ->findOrFail($data['hotel_id']);

        $media = $hotel->getFirstMedia('cover')
            ?: $hotel->getFirstMedia('gallery');

        if ($media) {
            $snapshot['hotel_image'] = [
                'thumb'   => $media->getUrl('thumb'),
                'thumb2x' => $media->getUrl('thumb2x'),
                'alt'     => $data['hotel_name'],
            ];
        }

        $this->addToCart(
            'hotel_room',
            (int) $data['room_id'],
            (float) $data['price_total'],
            $data['currency'],
            $snapshot,
        );

        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }

    /**
     * Villa booking -> sepete ekleme
     */
    public function bookVilla(VillaBookingRequest $request)
    {
        $data = $request->validated();

        // Null children yerine 0
        $data['children'] = $data['children'] ?? 0;

        // Opsiyonel lokasyon etiketi
        if ($request->filled('location_label')) {
            $data['location_label'] = $request->input('location_label');
        }

        // Villa cover / gallery görseli → snapshot.villa_image
        $villa = Villa::query()
            ->with('media')
            ->findOrFail($data['villa_id']);

        $media = $villa->getFirstMedia('cover')
            ?: $villa->getFirstMedia('gallery');

        if ($media) {
            $data['villa_image'] = [
                'thumb'   => $media->getUrl('thumb'),
                'thumb2x' => $media->getUrl('thumb2x'),
                'alt'     => $data['villa_name'],
            ];
        }

        // Sepette “şimdi ödenecek” tutar olarak ön ödemeyi kullanıyoruz
        $this->addToCart(
            'villa',
            (int) $data['villa_id'],
            (float) $data['price_prepayment'],
            $data['currency'],
            $data,
        );

        return redirect()
            ->to(localized_route('cart'))
            ->with('ok', 'validated');
    }

    /**
     * Sepeti Order’a çevirir
     *
     * @param  array  $cart
     * @param  array  $customerData
     * @param  float  $couponDiscountTotal     Kuponlardan gelen toplam indirim
     * @param  array  $couponSnapshot          Kupon snapshot listesi
     * @param  float  $campaignDiscountTotal   Kampanyalardan gelen toplam indirim
     * @param  array  $campaignSnapshot        Kampanya snapshot listesi
     */
    private function createOrderFromCart(
        array $cart,
        array $customerData,
        float $couponDiscountTotal = 0.0,
        array $couponSnapshot = [],
        float $campaignDiscountTotal = 0.0,
        array $campaignSnapshot = []
    ): \App\Models\Order {
        $items = $cart['items'] ?? [];

        if (empty($items)) {
            throw new \RuntimeException('cart_empty');
        }

        // Para birimi: tüm satırlar aynı olmalı
        $currency = $items[0]['currency'];

        // Toplam (kuponsuz raw toplam) → sepet satırlarındaki amount'ların toplamı
        $totalAmount = 0.0;

        foreach ($items as $ci) {
            $lineAmount  = (float) ($ci['amount'] ?? 0);
            $totalAmount += $lineAmount;
        }

        // Toplam indirim: kupon + kampanya
        $rawDiscount = max(0.0, $couponDiscountTotal + $campaignDiscountTotal);

        // Toplamdan fazla olamaz
        $discountAmount = 0.0;
        if ($rawDiscount > 0 && $totalAmount > 0) {
            $discountAmount = min($rawDiscount, $totalAmount);
        }

        // Kupon + Kampanya snapshot’larını tek JSON’da tutuyoruz
        $allDiscountsSnapshot = array_values(array_merge(
            $couponSnapshot,
            $campaignSnapshot
        ));

        // Order oluştur
        $order = \App\Models\Order::create([
            'user_id'         => auth()->id(),
            'status'          => 'pending',
            'payment_status'  => 'unpaid',
            'currency'        => $currency,
            'total_amount'    => $totalAmount,
            'discount_amount' => $discountAmount,

            // Kupon ve kampanya detayları artık sadece coupon_snapshot içinde tutuluyor.
            // coupon_code kolonunu bilinçli olarak boş bırakıyoruz (ileride gerekirse kullanırız).
            'coupon_code'     => null,
            'coupon_snapshot' => $allDiscountsSnapshot ?: null,

            // Müşteri bilgileri (şimdilik basit)
            'customer_name'    => $customerData['name']  ?? null,
            'customer_email'   => $customerData['email'] ?? null,
            'customer_phone'   => $customerData['phone'] ?? null,

            'billing_address'  => null,
            'metadata'         => [],
        ]);

        // Order items
        foreach ($items as $ci) {
            \App\Models\OrderItem::create([
                'order_id'     => $order->id,
                'product_type' => $ci['product_type'],
                'product_id'   => $ci['product_id'],

                // snapshot içinden title seçimi
                'title'        => $ci['snapshot']['tour_name']
                    ?? $ci['snapshot']['room_name']
                        ?? $ci['snapshot']['villa_name']
                        ?? $ci['snapshot']['hotel_name']
                        ?? 'Ürün',

                'quantity'     => 1,
                'currency'     => $ci['currency'],
                'unit_price'   => (float) $ci['amount'],
                'total_price'  => (float) $ci['amount'],

                'snapshot'     => $ci['snapshot'],
            ]);
        }

        return $order;
    }

    /**
     * Sepeti siparişe çevirir ve sepeti temizler
     */
    public function complete(
        CouponViewModelService $couponVm,
        CampaignViewModelService $campaignVm
    ) {
        $cart = session('cart');

        if (!$cart || empty($cart['items'])) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_empty');
        }

        $items = $cart['items'];

        // Sepet toplamı + currency (Order ve kupon hesabı için)
        $cartSubtotal = 0.0;
        $cartCurrency = null;

        foreach ($items as $ci) {
            $amount = (float) ($ci['amount'] ?? 0);
            $cartSubtotal += $amount;

            if ($cartCurrency === null && !empty($ci['currency'])) {
                $cartCurrency = $ci['currency'];
            }
        }

        $couponDiscountTotal = 0.0;
        $couponSnapshot      = [];

        $campaignDiscountTotal = 0.0;
        $campaignSnapshot      = [];

        $user = auth()->user();

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

            $appliedIds = (array) session('cart.applied_coupons', []);

            foreach ($cartCoupons as $vm) {
                $id = $vm['id'] ?? null;

                $isApplied    = $id !== null && in_array($id, $appliedIds, true);
                $isApplicable = !empty($vm['is_applicable']);
                $discount     = (float) ($vm['calculated_discount'] ?? 0);

                if ($isApplied && $isApplicable && $discount > 0) {
                    $couponDiscountTotal += $discount;

                    $couponSnapshot[] = [
                        'user_coupon_id' => $id,
                        // buildViewModel içinde henüz yoksa ileride ekleyebiliriz
                        'coupon_id'      => $vm['coupon_id'] ?? null,
                        'code'           => $vm['code'] ?? null,
                        'discount'       => $discount,
                        'title'          => $vm['title'] ?? null,
                        'badge_label'    => $vm['badge_label'] ?? null,
                        'type'           => 'coupon',
                    ];
                }
            }

            // -----------------------------
            // Kampanyalar
            // -----------------------------
            $cartCampaigns = $campaignVm->buildCartCampaignsForUser(
                $user,
                $items,
                $cartCurrency,
                $cartSubtotal
            );

            foreach ($cartCampaigns as $cvm) {
                $discount = (float) ($cvm['calculated_discount'] ?? 0);

                if (! empty($cvm['is_applicable']) && $discount > 0) {
                    $campaignDiscountTotal += $discount;

                    $campaignSnapshot[] = [
                        'campaign_id' => $cvm['id'],
                        'discount'    => $discount,
                        'title'       => $cvm['title'] ?? null,
                        'type'        => 'campaign',
                    ];
                }
            }
        }

        $customerData = [
            'name'  => $user->name  ?? null,
            'email' => $user->email ?? null,
            'phone' => $user->phone ?? null,
        ];

        try {
            $order = $this->createOrderFromCart(
                $cart,
                $customerData,
                $couponDiscountTotal,
                $couponSnapshot,
                $campaignDiscountTotal,
                $campaignSnapshot
            );
        } catch (\Throwable $e) {
            // GEÇİCİ DEBUG
            dd(
                $e->getMessage(),
                $e->getFile() . ':' . $e->getLine()
            );
        }

        /**
         * Kupon kullanım sayısını güncelle
         * Not: Şu anda sipariş oluşturma anında artırıyoruz.
         * İleride ödeme başarılı olduğunda artırmak istersen,
         * bu blok payment akışına taşınabilir.
         */
        if ($user && !empty($couponSnapshot)) {
            foreach ($couponSnapshot as $cSnap) {
                $userCouponId = $cSnap['user_coupon_id'] ?? null;
                if (!$userCouponId) {
                    continue;
                }

                $userCoupon = UserCoupon::where('id', $userCouponId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($userCoupon) {
                    $userCoupon->used_count   = (int) $userCoupon->used_count + 1;
                    $userCoupon->last_used_at = now();
                    $userCoupon->save();
                }
            }
        }

        // Sepeti ve kupon seçimlerini temizle
        session()->forget('cart');
        session()->forget('cart.applied_coupons');

        return redirect()
            ->to(localized_route('order.thankyou', ['code' => $order->code]));
    }
}
