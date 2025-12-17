<?php

namespace App\Services;

final class PaymentGatewayFactory
{
    public static function make(): PaymentGatewayInterface
    {
        return self::makeFor((string) config('icr.payments.driver'));
    }

    public static function makeFor(string $driver): PaymentGatewayInterface
    {
        return match ($driver) {
            'demo' => app(\App\Services\DemoPaymentGateway::class),
            // 'isbank' => app(\App\Services\IsbankPaymentGateway::class),

            default => throw new \RuntimeException(
                "Geçerli bir ödeme sağlayıcı bulunamadı: {$driver}"
            ),
        };
    }
}
