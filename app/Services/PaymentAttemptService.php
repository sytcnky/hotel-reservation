<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;
use Illuminate\Http\Request;

class PaymentAttemptService
{
    public function latestAttemptForSession(CheckoutSession $session): ?PaymentAttempt
    {
        return PaymentAttempt::query()
            ->where('checkout_session_id', $session->id)
            ->withoutTrashed()
            ->orderByDesc('id')
            ->first();
    }

    /**
     * show() sayfasındaki attemptError mantığını korur.
     */
    public function latestAttemptErrorForSession(CheckoutSession $session): ?string
    {
        $latestAttempt = $this->latestAttemptForSession($session);

        if ($latestAttempt && $latestAttempt->status === PaymentAttempt::STATUS_FAILED) {
            return 'msg.err.payment.finalize_failed';
        }

        if ($latestAttempt && $latestAttempt->status === PaymentAttempt::STATUS_EXPIRED) {
            return 'msg.err.payment.session_expired';
        }

        return null;
    }

    public function getOrCreatePendingAttempt(
        CheckoutSession $checkout,
        string $idempotencyKey,
        Request $request
    ): PaymentAttempt {
        // 1) Zaten pending varsa onu kullan (concurrent double-submit engeli)
        $existingPending = PaymentAttempt::query()
            ->where('checkout_session_id', $checkout->id)
            ->whereIn('status', [
                PaymentAttempt::STATUS_PENDING,
            ])
            ->whereNull('completed_at')
            ->withoutTrashed()
            ->orderByDesc('id')
            ->first();

        if ($existingPending) {
            return $existingPending;
        }

        // 2) Bu idempotencyKey ile daha önce attempt yaratıldıysa (completed olsa bile) onu döndür (race-safe)
        $existingByKey = PaymentAttempt::query()
            ->where('checkout_session_id', $checkout->id)
            ->where('idempotency_key', $idempotencyKey)
            ->withoutTrashed()
            ->orderByDesc('id')
            ->first();

        if ($existingByKey) {
            return $existingByKey;
        }

        $payable = max((float) $checkout->cart_total - (float) $checkout->discount_amount, 0);

        $driver = (string) config('icr.payments.driver');
        $driver = trim($driver);
        if ($driver === '') {
            $driver = 'unknown';
        }

        try {
            return PaymentAttempt::create([
                'order_id'            => null,
                'checkout_session_id' => $checkout->id,
                'idempotency_key'     => $idempotencyKey,
                'gateway'             => $driver,
                'amount'              => $payable,
                'currency'            => $checkout->currency,
                'status'              => PaymentAttempt::STATUS_PENDING,
                'ip_address'          => $request->ip(),
                'user_agent'          => substr((string) $request->userAgent(), 0, 255),
                'started_at'          => now(),
            ]);
        } catch (\Throwable $e) {
            // 3) Unique violation / race durumunda: aynı key ile oluşanı çek
            $byKey = PaymentAttempt::query()
                ->where('checkout_session_id', $checkout->id)
                ->where('idempotency_key', $idempotencyKey)
                ->withoutTrashed()
                ->orderByDesc('id')
                ->first();

            if ($byKey) {
                return $byKey;
            }

            // 4) Son çare: pending varsa onu çek
            $pending = PaymentAttempt::query()
                ->where('checkout_session_id', $checkout->id)
                ->whereIn('status', [
                    PaymentAttempt::STATUS_PENDING,
                ])
                ->whereNull('completed_at')
                ->withoutTrashed()
                ->orderByDesc('id')
                ->first();

            if ($pending) {
                return $pending;
            }

            throw $e;
        }
    }

    public function finalizeAttemptAsExpired(CheckoutSession $session, string $idempotencyKey): void
    {
        $pending = PaymentAttempt::query()
            ->where('checkout_session_id', $session->id)
            ->whereIn('status', [
                PaymentAttempt::STATUS_PENDING,
            ])
            ->whereNull('completed_at')
            ->withoutTrashed()
            ->orderByDesc('id')
            ->first();

        if ($pending) {
            $pending->forceFill([
                'status'        => PaymentAttempt::STATUS_EXPIRED,
                'error_code'    => 'SESSION_EXPIRED',
                'error_message' => 'msg.err.payment.session_expired',
                'completed_at'  => now(),
                'raw_response'  => ['reason' => 'checkout_session_expired'],
            ])->save();

            return;
        }

        $payable = max((float) $session->cart_total - (float) $session->discount_amount, 0);

        $driver = (string) config('icr.payments.driver');
        $driver = trim($driver);
        if ($driver === '') {
            $driver = 'unknown';
        }

        PaymentAttempt::create([
            'order_id'            => null,
            'checkout_session_id' => $session->id,
            'idempotency_key'     => $idempotencyKey,
            'gateway'             => $driver,
            'amount'              => $payable,
            'currency'            => $session->currency,
            'status'              => PaymentAttempt::STATUS_EXPIRED,
            'ip_address'          => $session->ip_address,
            'user_agent'          => $session->user_agent ? substr((string) $session->user_agent, 0, 255) : null,
            'started_at'          => $session->started_at ?? now(),
            'completed_at'        => now(),
            'error_code'          => 'SESSION_EXPIRED',
            'error_message'       => 'msg.err.payment.session_expired',
            'raw_response'        => ['reason' => 'checkout_session_expired'],
        ]);
    }
}
