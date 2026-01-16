<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\UserCoupon;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Support\Facades\DB;

class OrderFinalizeService
{
    /**
     * 3DS success sonrası finalize:
     * - Session lock
     * - Order create (idempotent)
     * - Items create
     * - Coupon usage increment
     * - Attempt success + session completed
     *
     * @return array{0: ?Order, 1: bool} [$order, $orderCreatedNow]
     */
    public function finalizeSuccess(
        CheckoutSession $checkout,
        PaymentAttempt $attempt,
        array $result
    ): array {
        $order = null;
        $orderCreatedNow = false;

        DB::transaction(function () use ($checkout, $attempt, $result, &$order, &$orderCreatedNow) {
            $lockedSession = CheckoutSession::query()
                ->where('id', $checkout->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status === CheckoutSession::STATUS_COMPLETED && $lockedSession->order_id) {
                $order = Order::withTrashed()->find($lockedSession->order_id);
                $orderCreatedNow = false;
                return;
            }

            $payload = (array) ($lockedSession->customer_snapshot ?? []);
            $items   = (array) ($payload['items'] ?? []);

            // P1-7 GUARD: customer_snapshot / items zorunlu
            if (empty($payload) || empty($items)) {
                throw new \RuntimeException('Checkout verisi eksik (customer_snapshot/items). Sipariş oluşturulamadı.');
            }

            $meta             = (array) ($payload['metadata'] ?? []);
            $discountSnapshot = (array) ($payload['discount_snapshot'] ?? []);

            // locale: orders.locale NOT NULL → garanti
            $locale = LocaleHelper::normalizeCode(app()->getLocale());

            // customer fields (order üzerinde donmuş olsun)
            $customerName  = null;
            $customerEmail = null;
            $customerPhone = null;

            $guest = $meta['guest'] ?? null;

            if (is_array($guest)) {
                $first = trim((string) ($guest['first_name'] ?? ''));
                $last  = trim((string) ($guest['last_name'] ?? ''));
                $customerName  = trim($first . ' ' . $last) ?: null;
                $customerEmail = ! empty($guest['email']) ? (string) $guest['email'] : null;
                $customerPhone = ! empty($guest['phone']) ? (string) $guest['phone'] : null;
            } else {
                $user = $lockedSession->user_id
                    ? \App\Models\User::query()->find($lockedSession->user_id)
                    : null;

                if ($user) {
                    $customerName  = $user->name ?? null;
                    $customerEmail = $user->email ?? null;
                    $customerPhone = $user->phone ?? null;
                }
            }

            // Order oluşturuldu (code NOT NULL olduğu için önce ID alıp code’u birlikte yazarız)
            $nextIdRow = DB::selectOne("select nextval('orders_id_seq') as id");
            $nextId    = (int) ($nextIdRow->id ?? 0);

            if ($nextId <= 0) {
                throw new \RuntimeException('Order ID sequence alınamadı (orders_id_seq).');
            }

            $order = new Order();

            $order->forceFill([
                'id'                => $nextId,
                'code'              => 'ORD-' . $nextId,

                'user_id'           => $lockedSession->user_id,
                'status'            => 'pending',
                'payment_status'    => 'paid',
                'paid_at'           => now(),
                'payment_expires_at'=> null,

                'currency'          => $lockedSession->currency,
                'total_amount'      => (float) $lockedSession->cart_total,
                'discount_amount'   => (float) $lockedSession->discount_amount,

                'billing_address'   => null,
                'coupon_snapshot'   => $discountSnapshot ?: null,
                'metadata'          => $meta ?: null,

                'locale'            => $locale,
                'customer_name'     => $customerName,
                'customer_email'    => $customerEmail,
                'customer_phone'    => $customerPhone,
            ]);

            $order->save();

            $orderCreatedNow = true;

            foreach ($items as $item) {
                $snapshot = (array) ($item['snapshot'] ?? []);

                $title = null;

                // Öncelik: snapshot içindeki “zaten doğru olması gereken” alanlar
                foreach (['tour_name', 'room_name', 'villa_name', 'hotel_name', 'vehicle_name', 'title', 'name'] as $k) {
                    $v = trim((string) ($snapshot[$k] ?? ''));
                    if ($v !== '') {
                        $title = $v;
                        break;
                    }
                }

                // Tek fallback
                if ($title === null) {
                    $title = '-';
                }

                $currency   = strtoupper((string) $item['currency']);
                $quantity   = (int) $item['quantity'];
                $unitPrice  = (float) $item['unit_price'];
                $totalPrice = (float) $item['total_price'];

                $order->items()->create([
                    'product_type' => $item['product_type'] ?? null,
                    'product_id'   => $item['product_id'] ?? null,
                    'title'        => $title,
                    'quantity'     => $quantity,
                    'currency'     => $currency,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $totalPrice,
                    'snapshot'     => $snapshot,
                ]);
            }

            if ($lockedSession->type === CheckoutSession::TYPE_USER && $lockedSession->user_id && $discountSnapshot) {
                $this->incrementUserCouponUsage((int) $lockedSession->user_id, $discountSnapshot);
            }

            $attempt->forceFill([
                'order_id'          => $order->id,
                'status'            => PaymentAttempt::STATUS_SUCCESS,
                'gateway_reference' => $result['gateway_reference'] ?? $attempt->gateway_reference,
                'raw_request'       => $result['raw_request'] ?? null,
                'raw_response'      => $result['raw_response'] ?? $result,
                'completed_at'      => now(),
            ])->save();

            $lockedSession->forceFill([
                'status'       => CheckoutSession::STATUS_COMPLETED,
                'completed_at' => now(),
                'order_id'     => $order->id,
            ])->save();

            $orderCreatedNow = true;
        });

        return [$order, $orderCreatedNow];
    }

    protected function incrementUserCouponUsage(int $userId, array $discountSnapshot): void
    {
        foreach ($discountSnapshot as $row) {
            if (($row['type'] ?? null) !== 'coupon') {
                continue;
            }

            $userCouponId = $row['user_coupon_id'] ?? null;
            if (! $userCouponId) {
                continue;
            }

            $userCoupon = UserCoupon::query()
                ->where('id', $userCouponId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $userCoupon) {
                continue;
            }

            $userCoupon->forceFill([
                'used_count'   => (int) $userCoupon->used_count + 1,
                'last_used_at' => now(),
            ])->save();
        }
    }
}
