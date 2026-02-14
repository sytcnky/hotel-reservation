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
        $driver = trim((string) $driver);

        if ($driver === '' || $driver === 'none') {
            throw new \RuntimeException('Ödeme sağlayıcısı yapılandırılmamış (PAYMENT_DRIVER).');
        }

        return match ($driver) {
            'nestpay' => app()->make(\App\Services\Payments\Nestpay\NestpayGateway::class),

            default => throw new \RuntimeException(
                "Geçerli bir ödeme sağlayıcı bulunamadı: {$driver}"
            ),
        };
    }
}
