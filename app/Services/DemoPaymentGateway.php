<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\RefundAttempt;

class DemoPaymentGateway implements PaymentGateway3dsInterface
{
    public function processPayment(
        Order $order,
        PaymentAttempt $attempt,
        array $cardData
    ): array {
        // Şimdilik “non-3ds” senaryosu için kalsın.
        // 3DS akışında PaymentController artık bunu çağırmayacak.
        $requestedOutcome = $cardData['demo_outcome'] ?? null;

        $defaultOutcome = config('icr.payments.demo_mode', 'success');
        $outcome = $requestedOutcome ?: $defaultOutcome;

        usleep(2_000_000);

        $gatewayRef = 'DEMO-PAY-' . strtoupper(bin2hex(random_bytes(6)));

        if ($outcome === 'success') {
            return [
                'success'           => true,
                'gateway_reference' => $gatewayRef,
                'error_code'        => null,
                'message'           => null,
                'raw_request'       => ['demo' => true, 'flow' => 'non_3ds', 'outcome' => 'success'],
                'raw_response'      => ['demo' => true, 'result' => 'approved', 'gateway_reference' => $gatewayRef],
            ];
        }

        return [
            'success'           => false,
            'gateway_reference' => $gatewayRef,
            'error_code'        => 'DECLINED',
            'message'           => 'Demo: Ödeme reddedildi (fail seçildi).',
            'raw_request'       => ['demo' => true, 'flow' => 'non_3ds', 'outcome' => 'fail'],
            'raw_response'      => ['demo' => true, 'result' => 'declined', 'gateway_reference' => $gatewayRef, 'error_code' => 'DECLINED'],
        ];
    }

    public function start3ds(
        CheckoutSession $checkout,
        PaymentAttempt $attempt,
        array $cardData
    ): array {
        // Banka gecikmesi simülasyonu (1sn)
        usleep(1_000_000);

        $digits = preg_replace('/\D+/', '', (string) ($cardData['cardnumber'] ?? ''));
        $last4  = $digits !== '' ? substr($digits, -4) : null;

        $rawRequest = [
            'demo'      => true,
            'flow'      => '3ds_start',
            'last4'     => $last4,
            'exp_month' => $cardData['exp_month'] ?? null,
            'exp_year'  => $cardData['exp_year'] ?? null,
        ];

        $gatewayRef = 'DEMO-3DS-' . strtoupper(bin2hex(random_bytes(6)));

        $rawResponse = [
            'demo'              => true,
            'result'            => 'challenge_required',
            'gateway_reference' => $gatewayRef,
        ];

        return [
            'success'           => true,
            'gateway_reference' => $gatewayRef,
            'message'           => null,
            'raw_request'       => $rawRequest,
            'raw_response'      => $rawResponse,
        ];
    }

    public function complete3ds(
        CheckoutSession $checkout,
        PaymentAttempt $attempt,
        array $payload = []
    ): array {
        // Banka gecikmesi simülasyonu (1sn)
        usleep(1_000_000);

        $result = (string) ($payload['result'] ?? 'fail'); // success | fail

        $rawRequest = [
            'demo'   => true,
            'flow'   => '3ds_complete',
            'result' => $result,
        ];

        if ($result === 'success') {
            $rawResponse = [
                'demo'              => true,
                'result'            => 'approved',
                'gateway_reference' => $attempt->gateway_reference,
                'auth_code'         => 'A' . random_int(100000, 999999),
            ];

            return [
                'success'           => true,
                'gateway_reference' => $attempt->gateway_reference,
                'error_code'        => null,
                'message'           => null,
                'raw_request'       => $rawRequest,
                'raw_response'      => $rawResponse,
            ];
        }

        $rawResponse = [
            'demo'              => true,
            'result'            => 'declined',
            'gateway_reference' => $attempt->gateway_reference,
            'error_code'        => 'DECLINED',
            'message'           => 'Demo 3D: Doğrulama başarısız.',
        ];

        return [
            'success'           => false,
            'gateway_reference' => $attempt->gateway_reference,
            'error_code'        => 'DECLINED',
            'message'           => 'Demo 3D: Doğrulama başarısız.',
            'raw_request'       => $rawRequest,
            'raw_response'      => $rawResponse,
        ];
    }

    public function simulateDraftPayment(array $validated, ?string $demoOutcome): array
    {
        // Bu artık 3DS akışında kullanılmayacak. (Geri uyum için bırakıyoruz.)
        usleep(2_000_000);

        $outcome = $demoOutcome === 'success' ? 'success' : 'fail';

        $digits = preg_replace('/\D+/', '', (string) ($validated['cardnumber'] ?? ''));
        $last4  = $digits !== '' ? substr($digits, -4) : null;

        $rawRequest = [
            'demo'      => true,
            'flow'      => 'draft',
            'last4'     => $last4,
            'exp_month' => $validated['exp-month'] ?? null,
            'exp_year'  => $validated['exp-year'] ?? null,
            'outcome'   => $outcome,
        ];

        $gatewayRef = 'DEMO-DRAFT-' . strtoupper(bin2hex(random_bytes(6)));

        if ($outcome === 'success') {
            return [
                'success'           => true,
                'gateway_reference' => $gatewayRef,
                'error_code'        => null,
                'message'           => null,
                'raw_request'       => $rawRequest,
                'raw_response'      => [
                    'demo'              => true,
                    'result'            => 'approved',
                    'gateway_reference' => $gatewayRef,
                ],
            ];
        }

        return [
            'success'           => false,
            'gateway_reference' => $gatewayRef,
            'error_code'        => 'DECLINED',
            'message'           => 'Demo: Ödeme reddedildi (fail seçildi).',
            'raw_request'       => $rawRequest,
            'raw_response'      => [
                'demo'              => true,
                'result'            => 'declined',
                'gateway_reference' => $gatewayRef,
                'error_code'        => 'DECLINED',
            ],
        ];
    }

    public function refund(RefundAttempt $refundAttempt, array $payload = []): array
    {
        usleep(10_000_000);

        $gatewayRef = 'DEMO-RF-' . strtoupper(bin2hex(random_bytes(6)));

        return [
            'success'           => true,
            'gateway_reference' => $gatewayRef,
            'error_code'        => null,
            'message'           => null,
            'raw_request'       => [
                'demo'      => true,
                'refund_id' => $refundAttempt->id,
                'amount'    => $refundAttempt->amount,
                'currency'  => $refundAttempt->currency,
                'payload'   => $payload,
            ],
            'raw_response'      => [
                'demo'              => true,
                'result'            => 'accepted',
                'gateway_reference' => $gatewayRef,
            ],
        ];
    }
}
