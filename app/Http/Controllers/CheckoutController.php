<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferBookingRequest;
use App\Http\Requests\TourBookingRequest;
use App\Http\Requests\HotelBookingRequest;
use App\Http\Requests\VillaBookingRequest;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Villa;

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
     */
    private function createOrderFromCart(array $cart, array $customerData): \App\Models\Order
    {
        $items = $cart['items'] ?? [];

        if (empty($items)) {
            throw new \RuntimeException('Sepet boş.');
        }

        // Para birimi: tüm satırlar aynı olmalı (senin mimarinde böyle)
        $currency = $items[0]['currency'];

        // Toplamlar
        $totalAmount     = 0;
        $totalPrepayment = 0;

        foreach ($items as $ci) {
            $totalAmount     += (float) $ci['amount'];
            $totalPrepayment += (float) $ci['amount']; // Şimdilik aynı; villa için ileride farklı handling yapılabilir
        }

        // Order oluştur
        $order = \App\Models\Order::create([
            'user_id'          => auth()->id(),
            'status'           => 'pending',
            'payment_status'   => 'unpaid',
            'currency'         => $currency,
            'total_amount'     => $totalAmount,
            'total_prepayment' => $totalPrepayment,
            'discount_amount'  => 0,
            'coupon_code'      => null,

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
                'order_id'    => $order->id,
                'product_type'=> $ci['product_type'],
                'product_id'  => $ci['product_id'],

                // snapshot içinden title seçimi
                'title'       => $ci['snapshot']['tour_name']
                    ?? $ci['snapshot']['room_name']
                        ?? $ci['snapshot']['villa_name']
                        ?? $ci['snapshot']['hotel_name']
                        ?? 'Ürün',

                'quantity'    => 1,
                'currency'    => $ci['currency'],
                'unit_price'  => (float) $ci['amount'],
                'total_price' => (float) $ci['amount'],

                'snapshot'    => $ci['snapshot'],
            ]);
        }

        return $order;
    }

    /**
     * Sepeti siparişe çevirir ve sepeti temizler
     */
    public function complete()
    {
        $cart = session('cart');

        if (!$cart || empty($cart['items'])) {
            return redirect()->to(localized_route('cart'))
                ->with('err', 'Sepetiniz boş.');
        }

        $customerData = [
            'name'  => auth()->user()->name  ?? null,
            'email' => auth()->user()->email ?? null,
            'phone' => auth()->user()->phone ?? null,
        ];

        try {
            $order = $this->createOrderFromCart($cart, $customerData);
        } catch (\Throwable $e) {
            // GEÇİCİ DEBUG
            dd(
                $e->getMessage(),
                $e->getFile() . ':' . $e->getLine()
            );
        }

        session()->forget('cart');

        return redirect()->to(localized_route('order.thankyou', ['code' => $order->code]));
    }


}
