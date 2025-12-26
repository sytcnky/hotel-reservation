<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\RefundAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RefundService
{
    public function refundPayment(
        Order $order,
        PaymentAttempt $paymentAttempt,
        float $amount,
        ?string $reason = null,
        array $meta = []
    ): RefundAttempt {
        if ($amount <= 0) {
            throw new RuntimeException('Refund amount must be greater than zero.');
        }

        if ($paymentAttempt->status !== PaymentAttempt::STATUS_SUCCESS) {
            throw new RuntimeException('Only successful payments can be refunded.');
        }

        if ($amount > (float) $paymentAttempt->amount) {
            throw new RuntimeException('Refund amount exceeds payment amount.');
        }

        return DB::transaction(function () use ($order, $paymentAttempt, $amount, $reason, $meta) {

            // initiator bilgisi
            $initiator = is_array($meta['initiator'] ?? null) ? $meta['initiator'] : [];

            $initiatorUserId =
                $meta['initiator_user_id']
                ?? ($initiator['user_id'] ?? null)
                ?? ($initiator['id'] ?? null);

            $initiatorName =
                $meta['initiator_name']
                ?? ($initiator['name'] ?? null);

            $initiatorRole =
                $meta['initiator_role']
                ?? ($initiator['role'] ?? null);


            $refund = RefundAttempt::create([
                'order_id'           => $order->id,
                'payment_attempt_id' => $paymentAttempt->id,
                'amount'             => $amount,
                'currency'           => $paymentAttempt->currency,
                'status'             => RefundAttempt::STATUS_PENDING,
                'gateway'            => $paymentAttempt->gateway,
                'reason'             => $reason,
                'meta'               => $meta ?: null, // istersen bunu tamamen kaldırabiliriz; şimdilik dokunmuyorum
                'started_at'         => now(),
                'ip_address'         => request()->ip(),
                'user_agent'         => substr((string) request()->userAgent(), 0, 255),

                // NET kolonlar
                'initiator_user_id'  => $initiatorUserId,
                'initiator_name'     => $initiatorName,
                'initiator_role'     => $initiatorRole,
            ]);

            try {
                $gateway = PaymentGatewayFactory::make();

                $result = $gateway->refund($refund, [
                    'order_code'  => $order->code,
                    'payment_ref' => $paymentAttempt->gateway_reference,
                ]);

                if (!empty($result['success'])) {
                    $refund->forceFill([
                        'status'            => RefundAttempt::STATUS_SUCCESS,
                        'gateway_reference' => $result['gateway_reference'] ?? null,
                        'raw_request'       => $result['raw_request']  ?? null,
                        'raw_response'      => $result['raw_response'] ?? null,
                        'completed_at'      => now(),
                    ])->save();

                    return $refund;
                }

                $refund->forceFill([
                    'status'        => RefundAttempt::STATUS_FAILED,
                    'error_code'    => $result['error_code'] ?? null,
                    'error_message' => $result['message'] ?? 'Refund failed',
                    'raw_request'   => $result['raw_request']  ?? null,
                    'raw_response'  => $result['raw_response'] ?? null,
                    'completed_at'  => now(),
                ])->save();

                return $refund;

            } catch (\Throwable $e) {
                Log::error('Refund exception', [
                    'refund_attempt_id' => $refund->id,
                    'exception' => $e,
                ]);

                $refund->forceFill([
                    'status'        => RefundAttempt::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                    'completed_at'  => now(),
                ])->save();

                return $refund;
            }
        });
    }
}
