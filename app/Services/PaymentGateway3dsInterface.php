<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;

interface PaymentGateway3dsInterface extends PaymentGatewayInterface
{
    /**
     * 3D akışını başlatır.
     *
     * Dönüş:
     * [
     *   'success' => bool,
     *   'gateway_reference' => string|null,
     *   'message' => string|null,
     *   'raw_request' => array|null,
     *   'raw_response' => array|null,
     * ]
     */
    public function start3ds(
        CheckoutSession $checkout,
        PaymentAttempt $attempt,
        array $cardData
    ): array;

    /**
     * 3D sonucunu finalize eder (success / fail).
     *
     * Dönüş:
     * [
     *   'success' => bool,
     *   'gateway_reference' => string|null,
     *   'error_code' => string|null,
     *   'message' => string|null,
     *   'raw_request' => array|null,
     *   'raw_response' => array|null,
     * ]
     */
    public function complete3ds(
        CheckoutSession $checkout,
        PaymentAttempt $attempt,
        array $payload = []
    ): array;
}
