<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Hosted ödeme başlatır (3D Pay Hosting vb.).
     *
     * Kart bilgisi almaz; bankaya yönlendirme (POST form) için gereken
     * endpoint + parametreleri üretir.
     *
     * Dönüş sözleşmesi:
     * [
     *   'success' => bool,
     *   'endpoint' => string|null,               // bank POST endpoint
     *   'method' => 'POST',                      // sabit
     *   'params' => array<string,string>|null,   // hidden inputs (HASH dahil)
     *   'error_code' => string|null,             // UI code registry uyumlu
     *   'message' => string|null,
     *   'raw_request' => array|null,             // non-prod evidence (prod’da null olabilir)
     * ]
     */
    public function initiateHostedPayment(
        CheckoutSession $session,
        PaymentAttempt $attempt,
        Request $request
    ): array;

    /**
     * Geri ödeme (refund) işlemi.
     *
     * Dönüş sözleşmesi:
     * [
     *   'success' => bool,
     *   'gateway_reference' => string|null,
     *   'error_code' => string|null,
     *   'message' => string|null,
     *   'raw_request' => array|null,
     *   'raw_response' => array|null,
     * ]
     */
    public function refund(
        \App\Models\RefundAttempt $refundAttempt,
        array $payload = []
    ): array;
}
