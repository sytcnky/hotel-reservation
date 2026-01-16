<?php

namespace App\Services;

use App\Models\CheckoutSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutSessionGuard
{
    public function loadByCode(string $code): CheckoutSession
    {
        return CheckoutSession::query()
            ->where('code', $code)
            ->firstOrFail();
    }

    /**
     * P0-1: IDOR bariyeri
     * - TYPE_USER: user_id eşleşmiyorsa 404
     * - TYPE_GUEST: signature zorunlu (payment/show/process). 3DS akışında token zaten ikinci faktör;
     *              signature varsa geçerli olmalı; yoksa akış bozulmasın diye token ile devam eder.
     */
    public function authorize(Request $request, CheckoutSession $session, bool $requireSignatureForGuest): void
    {
        if ($session->type === CheckoutSession::TYPE_USER) {
            $uid = auth()->id();
            if (! $uid || (int) $uid !== (int) $session->user_id) {
                abort(404);
            }
            return;
        }

        if ($session->type === CheckoutSession::TYPE_GUEST) {
            if ($requireSignatureForGuest) {
                if (! $request->hasValidSignature()) {
                    abort(404);
                }
                return;
            }

            // Signature paramı varsa geçerli olmalı (aksi halde kötü niyetli signature ile bypass olmasın)
            if ($request->query->has('signature') && ! $request->hasValidSignature()) {
                abort(404);
            }

            return;
        }

        abort(404);
    }

    public function isExpired(CheckoutSession $session): bool
    {
        if ($session->expires_at === null) {
            return false;
        }

        return now()->greaterThan($session->expires_at);
    }

    /**
     * TTL expired ise:
     * - session status EXPIRED yapılır (lock)
     * - attempt expired finalize edilir (idempotent)
     */
    public function expireIfNeededAndFinalizeAttempt(
        CheckoutSession $session,
        string $idempotencyKey,
        PaymentAttemptService $attempts
    ): bool {
        if (! $this->isExpired($session)) {
            return false;
        }

        DB::transaction(function () use ($session, $idempotencyKey, $attempts) {
            /** @var CheckoutSession $locked */
            $locked = CheckoutSession::query()
                ->where('id', $session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== CheckoutSession::STATUS_EXPIRED) {
                $locked->forceFill(['status' => CheckoutSession::STATUS_EXPIRED])->save();
            }

            $attempts->finalizeAttemptAsExpired($locked, $idempotencyKey);
        });

        return true;
    }

    /**
     * Session zaten EXPIRED ise attempt finalize (idempotent) + true.
     * Session EXPIRED değilse false.
     */
    public function finalizeAttemptIfSessionExpired(
        CheckoutSession $session,
        string $idempotencyKey,
        PaymentAttemptService $attempts
    ): bool {
        if ($session->status !== CheckoutSession::STATUS_EXPIRED) {
            return false;
        }

        DB::transaction(function () use ($session, $idempotencyKey, $attempts) {
            /** @var CheckoutSession $locked */
            $locked = CheckoutSession::query()
                ->where('id', $session->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== CheckoutSession::STATUS_EXPIRED) {
                $locked->forceFill(['status' => CheckoutSession::STATUS_EXPIRED])->save();
            }

            $attempts->finalizeAttemptAsExpired($locked, $idempotencyKey);
        });

        return true;
    }
}
