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
            ->whereNull('deleted_at')
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
        $existing = PaymentAttempt::query()
            ->where('checkout_session_id', $checkout->id)
            ->whereIn('status', [
                PaymentAttempt::STATUS_PENDING,
                PaymentAttempt::STATUS_PENDING_3DS,
            ])
            ->whereNull('completed_at')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $payable = max((float) $checkout->cart_total - (float) $checkout->discount_amount, 0);

        try {
            return PaymentAttempt::create([
                'order_id'            => null,
                'checkout_session_id' => $checkout->id,
                'idempotency_key'     => $idempotencyKey,
                'gateway'             => config('icr.payments.driver', 'demo'),
                'amount'              => $payable,
                'currency'            => $checkout->currency,
                'status'              => PaymentAttempt::STATUS_PENDING,
                'ip_address'          => $request->ip(),
                'user_agent'          => substr((string) $request->userAgent(), 0, 255),
                'started_at'          => now(),
            ]);
        } catch (\Throwable $e) {
            $pending = PaymentAttempt::query()
                ->where('checkout_session_id', $checkout->id)
                ->whereIn('status', [
                    PaymentAttempt::STATUS_PENDING,
                    PaymentAttempt::STATUS_PENDING_3DS,
                ])
                ->whereNull('completed_at')
                ->whereNull('deleted_at')
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
                PaymentAttempt::STATUS_PENDING_3DS,
            ])
            ->whereNull('completed_at')
            ->whereNull('deleted_at')
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

        PaymentAttempt::create([
            'order_id'            => null,
            'checkout_session_id' => $session->id,
            'idempotency_key'     => $idempotencyKey,
            'gateway'             => config('icr.payments.driver', 'demo'),
            'amount'              => $payable,
            'currency'            => $session->currency,
            'status'              => PaymentAttempt::STATUS_EXPIRED,
            'ip_address'          => $session->ip_address,
            'user_agent'          => $session->user_agent ? substr((string) $session->user_agent, 0, 255) : null,
            'started_at'          => $session->started_at ?? now(),
            'completed_at'        => now(),
            'error_code'          => 'SESSION_EXPIRED',
            'error_message'       => 'Ödeme oturumu zaman aşımına uğradı.',
            'raw_response'        => ['reason' => 'checkout_session_expired'],
        ]);
    }

    /**
     * show3ds(): attempt bulunamazsa null döner (redirect akışı bozulmasın).
     */
    public function findPending3dsAttemptForSession(CheckoutSession $session, int $attemptId): ?PaymentAttempt
    {
        if ($attemptId <= 0) {
            return null;
        }

        return PaymentAttempt::query()
            ->where('id', $attemptId)
            ->where('checkout_session_id', $session->id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * complete3ds(): önceki davranışla aynı şekilde "bulunamazsa 404" için firstOrFail kullanır.
     */
    public function getAttemptForComplete3dsOrFail(CheckoutSession $session, int $attemptId): PaymentAttempt
    {
        return PaymentAttempt::query()
            ->where('id', $attemptId)
            ->where('checkout_session_id', $session->id)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    /**
     * 3DS token doğrulama (tek otorite).
     */
    public function isValid3dsToken(PaymentAttempt $attempt, string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $expected = hash_hmac('sha256', (string) $attempt->id, (string) $attempt->idempotency_key);

        return hash_equals($expected, $token);
    }
}
