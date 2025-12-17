<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAttempt;

interface PaymentGatewayInterface
{
    /**
     * Kart bilgileriyle ödemeyi işler.
     *
     * Dönüş sözleşmesi:
     * [
     *     'success' => bool,
     *     'message' => string|null,
     * ]
     */
    public function processPayment(
        Order $order,
        PaymentAttempt $attempt,
        array $cardData
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
