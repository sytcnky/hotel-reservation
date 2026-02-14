<?php

namespace App\Services\Payments\Nestpay;

use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;
use App\Services\PaymentGatewayInterface;
use Illuminate\Http\Request;

class NestpayGateway implements PaymentGatewayInterface
{
    public function initiateHostedPayment(
        CheckoutSession $session,
        PaymentAttempt $attempt,
        Request $request
    ): array {
        $endpoint = trim((string) config('icr.payments.nestpay.endpoint'));
        $clientId = trim((string) config('icr.payments.nestpay.client_id'));
        $storeKey = trim((string) config('icr.payments.nestpay.store_key'));

        if ($endpoint === '' || $clientId === '' || $storeKey === '') {
            return [
                'success'     => false,
                'endpoint'    => null,
                'method'      => 'POST',
                'params'      => null,
                'error_code'  => 'CONFIG_MISSING',
                'message'     => 'msg.err.payment.gateway_not_configured',
                'raw_request' => null,
            ];
        }

        $amount = max((float) $session->cart_total - (float) $session->discount_amount, 0);
        $amountStr = number_format($amount, 2, '.', '');

        $currencyIso = strtoupper(trim((string) $session->currency));
        $currency = $this->toNestpayCurrencyOrNull($currencyIso);

        if ($currency === null) {
            return [
                'success'     => false,
                'endpoint'    => null,
                'method'      => 'POST',
                'params'      => null,
                'error_code'  => 'UNSUPPORTED_CURRENCY',
                'message'     => 'msg.err.payment.currency_not_supported',
                'raw_request' => $this->safeRaw([
                    'currency'   => $currencyIso,
                    'supported'  => ['TRY', 'GBP'],
                ]),
            ];
        }

        // OID standardı: attempt bazlı tekil
        $oid = 'PA-' . (string) $attempt->id;

        $uiLocale = app()->getLocale();
        $lang = $uiLocale === 'en' ? 'en' : 'tr';

        // Banka ok/fail'a GET veya POST dönebilir → localized olmayan endpoint’ler
        $okUrl   = route('payment.nestpay.return.ok', ['oid' => $oid]);
        $failUrl = route('payment.nestpay.return.fail', ['oid' => $oid]);

        // callback localized değil
        $callbackUrl = route('payment.nestpay.callback');

        $rnd = (string) $this->rndMs();

        $params = [
            'clientid'      => $clientId,
            'oid'           => $oid,
            'amount'        => $amountStr,
            'currency'      => $currency,
            'TranType'      => 'Auth',
            'storetype'     => '3D_PAY_HOSTING',
            'lang'          => $lang,
            'okUrl'         => $okUrl,
            'failUrl'       => $failUrl,
            'callbackurl'   => $callbackUrl,
            'hashAlgorithm' => 'ver3',
            'rnd'           => $rnd,
            'encoding'      => 'utf-8',
            'refreshtime'   => '5',
        ];

        $params['HASH'] = $this->computeVer3Hash($params, $storeKey);

        return [
            'success'     => true,
            'endpoint'    => $endpoint,
            'method'      => 'POST',
            'params'      => $params,
            'error_code'  => null,
            'message'     => null,
            'raw_request' => $this->safeRaw([
                'endpoint' => $endpoint,
                'params'   => $this->safeParamsForEvidence($params),
            ]),
        ];
    }

    public function refund(\App\Models\RefundAttempt $refundAttempt, array $payload = []): array
    {
        return [
            'success'           => false,
            'gateway_reference' => null,
            'error_code'        => 'NOT_IMPLEMENTED',
            'message'           => 'Refund not implemented for Nestpay yet',
            'raw_request'       => null,
            'raw_response'      => null,
        ];
    }

    private function toNestpayCurrencyOrNull(string $iso): ?string
    {
        return match ($iso) {
            'TRY' => '949',
            'GBP' => '826',
            default => null,
        };
    }

    private function rndMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    private function computeVer3Hash(array $params, string $storeKey): string
    {
        $keys = array_keys($params);
        natcasesort($keys);

        $hashVal = '';

        foreach ($keys as $k) {
            if (! is_string($k)) {
                continue;
            }

            $lower = strtolower($k);

            if ($lower === 'hash' || $lower === 'encoding') {
                continue;
            }

            $v = $params[$k];
            $valueStr = is_scalar($v) ? (string) $v : '';

            $hashVal .= $this->escapeHashValue($valueStr) . '|';
        }

        $hashVal .= $this->escapeHashValue($storeKey);

        $hex = hash('sha512', $hashVal);
        return base64_encode(pack('H*', $hex));
    }

    private function escapeHashValue(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('|', '\|', $value);
        return $value;
    }

    private function safeRaw(?array $raw): ?array
    {
        if (app()->isProduction()) {
            return null;
        }

        return is_array($raw) ? $raw : null;
    }

    private function safeParamsForEvidence(array $params): array
    {
        return $params;
    }
}
