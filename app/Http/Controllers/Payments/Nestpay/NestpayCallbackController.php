<?php

namespace App\Http\Controllers\Payments\Nestpay;

use App\Http\Controllers\Controller;
use App\Models\PaymentAttempt;
use App\Services\CheckoutSessionGuard;
use App\Services\OrderFinalizeService;
use App\Services\PaymentAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NestpayCallbackController extends Controller
{
    /**
     * Bankanın maille verdiği "Olması Gereken Sıralama" (HASH plaintext kontratı).
     * Bu liste DIŞINDA kalan alanlar hash'e dahil edilmez.
     * HASH/encoding/countdown bu listede YOKTUR (hariç).
     */
    private const ORDER = [
        'ACQBIN',
        'acqStan',
        'amount',
        'AuthCode',
        'BillToName',
        'BillToStateProv',
        'BillToStreet1',
        'BillToStreet2',
        'callbackCall',
        'callbackurl',
        'cavv',
        'clientid',
        'clientIp',
        'currency',
        'digest',
        'dsId',
        'eci',
        'Ecom_Payment_Card_ExpDate_Month',
        'Ecom_Payment_Card_ExpDate_Year',
        'ErrMsg',
        'EXTRA.AVSAPPROVE',
        'EXTRA.AVSERRORCODEDETAIL',
        'EXTRA.CARDBRAND',
        'EXTRA.CARDISSUER',
        'EXTRA.HOSTDATE',
        'EXTRA.TRXDATE',
        'failUrl',
        'girogateParamReqHash',
        'hashAlgorithm',
        'HostRefNum',
        'lang',
        'maskedCreditCard',
        'MaskedPan',
        'MAXTIPLIMIT',
        'md',
        'mdErrorMsg',
        'mdStatus',
        'merchantID',
        'oid',
        'okUrl',
        'panFirst6',
        'panLast4',
        'PAResSyntaxOK',
        'paresTxStatus',
        'PAResVerified',
        'payResults.dsId',
        'pbirimsembol',
        'ProcReturnCode',
        'protocol',
        'querycampainghash',
        'querydcchash',
        'refreshtime',
        'Response',
        'ReturnOid',
        'rnd',
        'RREQEXTENSIONS',
        'SettleId',
        'ShipToStateProv',
        'showdcchash',
        'sID',
        'signature',
        'storetype',
        'tadres',
        'tadres2',
        'tcknvkn',
        'TDS2.acsOperatorID',
        'TDS2.acsReferenceNumber',
        'TDS2.acsTransID',
        'TDS2.authenticationType',
        'TDS2.authTimestamp',
        'TDS2.dsTransID',
        'TDS2.threeDSServerTransID',
        'TDS2.transStatus',
        'tismi',
        'TRANID',
        'TransId',
        'TranType',
        'veresEnrolledStatus',
        'version',
        'xid',
        // en sona STOREKEY value eklenecek
    ];

    public function handle(
        Request $request,
        CheckoutSessionGuard $guard,
        PaymentAttemptService $attempts,
        OrderFinalizeService $finalize
    ) {
        $rawBody = $request->getContent();

        // RAW body'yi non-prod ortamda dosyaya yaz (tunnel dahil) — tek örnek yakalamak için.
        $this->dumpRawBodyForOfflineHashTest(is_string($rawBody) ? $rawBody : '');

        // Hash doğrulama için RAW parse
        $rawPayload = $this->parseRawQueryStringForHash(is_string($rawBody) ? $rawBody : '');

        // Business payload: raw öncelik, yoksa request->all()
        $payload = ! empty($rawPayload) ? $rawPayload : $this->normalizePayload($request);

        if (! app()->isProduction()) {
            Log::info('nestpay_callback_evidence', [
                'raw_len'      => is_string($rawBody) ? strlen($rawBody) : null,
                'raw_keys'     => array_keys($rawPayload),
                'payload_keys' => array_keys($payload),
            ]);
        }

        $oid = trim((string) ($payload['oid'] ?? $payload['OID'] ?? ''));
        if ($oid === '') {
            Log::warning('nestpay_callback_missing_oid', [
                'keys' => array_keys($payload),
            ]);

            return response('Missing oid', 400);
        }

        // OID = "PA-<attemptId>" (kilitli)
        $attempt = $this->findAttemptByOid($oid);

        if (! $attempt) {
            Log::warning('nestpay_callback_unknown_attempt', [
                'oid' => $oid,
            ]);

            return response('Unknown oid', 404);
        }

        $session = $attempt->checkoutSession;

        if (! $session) {
            Log::warning('nestpay_callback_missing_session', [
                'attempt_id' => $attempt->id,
                'oid'        => $oid,
            ]);

            return response('Unknown oid', 404);
        }

        // TTL expired ise deterministic expire (bankaya Approved dönmeyelim)
        if ($guard->expireIfNeededAndFinalizeAttempt($session, 'expired_callback:' . $session->id, $attempts)) {
            Log::info('nestpay_callback_session_expired', [
                'checkout_id' => $session->id,
                'attempt_id'  => $attempt->id,
                'oid'         => $oid,
            ]);

            return response('Session expired', 409);
        }

        // Hash verify policy (prod/staging zorunlu)
        if ($this->shouldVerifyHash()) {
            $hashOk = $this->verifyHashFromRawPayloadUsingBankOrder($rawPayload);

            if (! $hashOk) {
                $this->markAttemptFailedHashInvalid($attempt, $payload);

                Log::warning('nestpay_callback_hash_invalid', [
                    'checkout_id' => $session->id,
                    'attempt_id'  => $attempt->id,
                    'oid'         => $oid,
                ]);

                return response('Invalid hash', 400);
            }
        }

        $isApproved = $this->isApproved($payload);

        try {
            if ($isApproved) {
                [$order, $createdNow] = $finalize->finalizeSuccess($session, $attempt, [
                    'success'           => true,
                    'gateway_reference' => $this->gatewayReference($payload),
                    'raw_request'       => $this->safeRaw(['source' => 'callback', 'payload' => $payload]),
                    'raw_response'      => $this->safeRaw(['source' => 'callback', 'payload' => $payload]),
                ]);

                Log::info('nestpay_callback_approved', [
                    'checkout_id'   => $session->id,
                    'attempt_id'    => $attempt->id,
                    'order_id'      => $order?->id,
                    'order_created' => $createdNow,
                    'oid'           => $oid,
                ]);

                return response('Approved', 200);
            }

            $this->markAttemptFailed($attempt, $payload);

            Log::info('nestpay_callback_declined', [
                'checkout_id' => $session->id,
                'attempt_id'  => $attempt->id,
                'oid'         => $oid,
            ]);

            return response('Approved', 200);
        } catch (\Throwable $e) {
            Log::error('nestpay_callback_exception', [
                'checkout_id' => $session->id,
                'attempt_id'  => $attempt->id ?? null,
                'oid'         => $oid,
                'message'     => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            return response('Callback error', 500);
        }
    }

    private function dumpRawBodyForOfflineHashTest(string $raw): void
    {
        if (app()->isProduction()) {
            return;
        }

        $raw = (string) $raw;
        if (trim($raw) === '') {
            return;
        }

        // Disk config’inden bağımsız sabit path
        @file_put_contents(storage_path('app/nestpay/raw.txt'), $raw);
    }

    private function normalizePayload(Request $request): array
    {
        $all = $request->all();
        return is_array($all) ? $all : [];
    }

    /**
     * HASH doğrulaması için RAW query parse:
     * Bankanın örnek string’i boşluk içeriyor → form decode davranışı (urldecode: + -> space) ile uyumlu.
     */
    private function parseRawQueryStringForHash(string $raw): array
    {
        $raw = ltrim($raw);
        if ($raw === '') {
            return [];
        }

        $result = [];
        foreach (explode('&', $raw) as $pair) {
            if ($pair === '') {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');

            $key = urldecode($key);
            $value = urldecode($value);

            $result[$key] = $value;
        }

        return $result;
    }

    private function shouldVerifyHash(): bool
    {
        // local dışında zorunlu
        if (! app()->isLocal()) {
            return true;
        }

        return (bool) config('icr.payments.nestpay.verify_callback_hash', true);
    }

    private function verifyHashFromRawPayloadUsingBankOrder(array $payload): bool
    {
        $storeKey = trim((string) config('icr.payments.nestpay.store_key'));
        if ($storeKey === '') {
            Log::error('nestpay_callback_store_key_missing');
            return false;
        }

        if (empty($payload)) {
            return false;
        }

        $receivedHash = trim((string) ($payload['HASH'] ?? $payload['hash'] ?? ''));
        if ($receivedHash === '') {
            return false;
        }

        [$computedHash, $plaintext] = $this->computeVer3HashUsingBankOrder($payload, $storeKey);

        $ok = hash_equals($computedHash, $receivedHash);

        if (! app()->isProduction()) {
            Log::info('nestpay_callback_hash_evidence', [
                'computed_ok'   => $ok,
                'plaintext_len' => strlen($plaintext),
            ]);
        }

        return $ok;
    }

    /**
     * Bankanın maille verdiği ORDER listesine göre HASH hesaplar.
     *
     * @return array{0:string,1:string} [computedHash, plaintext]
     */
    private function computeVer3HashUsingBankOrder(array $payload, string $storeKey): array
    {
        // Case-insensitive lookup map
        $map = [];
        foreach ($payload as $k => $v) {
            if (! is_string($k)) {
                continue;
            }

            $map[strtolower($k)] = is_scalar($v) ? (string) $v : '';
        }

        $hashVal = '';

        foreach (self::ORDER as $key) {
            $valueStr = $map[strtolower($key)] ?? '';
            $hashVal .= $this->escapeHashValue($valueStr) . '|';
        }

        // en sona store key value
        $hashVal .= $this->escapeHashValue($storeKey);

        $hex = hash('sha512', $hashVal);
        $computedHash = base64_encode(pack('H*', $hex));

        return [$computedHash, $hashVal];
    }

    private function escapeHashValue(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('|', '\|', $value);
        return $value;
    }

    private function findAttemptByOid(string $oid): ?PaymentAttempt
    {
        $oid = trim($oid);

        if (! str_starts_with($oid, 'PA-')) {
            return null;
        }

        $idPart = trim(substr($oid, 3));
        $attemptId = ctype_digit($idPart) ? (int) $idPart : 0;

        if ($attemptId <= 0) {
            return null;
        }

        return PaymentAttempt::query()
            ->with(['checkoutSession'])
            ->where('id', $attemptId)
            ->withoutTrashed()
            ->first();
    }

    private function isApproved(array $payload): bool
    {
        $procReturn = strtolower(trim((string) ($payload['ProcReturnCode'] ?? $payload['procreturncode'] ?? '')));
        return $procReturn === '00';
    }

    private function gatewayReference(array $payload): ?string
    {
        foreach (['TransId', 'transId', 'HostRefNum', 'hostrefnum', 'AuthCode', 'authcode'] as $k) {
            $v = trim((string) ($payload[$k] ?? ''));
            if ($v !== '') {
                return $v;
            }
        }

        return null;
    }

    private function markAttemptFailed(PaymentAttempt $attempt, array $payload): void
    {
        if ($attempt->status === PaymentAttempt::STATUS_SUCCESS && $attempt->completed_at) {
            return;
        }

        $attempt->forceFill([
            'status'        => PaymentAttempt::STATUS_FAILED,
            'error_code'    => trim((string) ($payload['ProcReturnCode'] ?? $payload['procreturncode'] ?? '')) ?: 'DECLINED',
            'error_message' => trim((string) ($payload['ErrMsg'] ?? $payload['errmsg'] ?? '')) ?: 'msg.err.payment.declined',
            'raw_request'   => $this->safeRaw(['source' => 'callback', 'payload' => $payload]),
            'raw_response'  => $this->safeRaw(['source' => 'callback', 'payload' => $payload]),
            'completed_at'  => now(),
        ])->save();
    }

    private function markAttemptFailedHashInvalid(PaymentAttempt $attempt, array $payload): void
    {
        if ($attempt->status === PaymentAttempt::STATUS_SUCCESS && $attempt->completed_at) {
            return;
        }

        $attempt->forceFill([
            'status'        => PaymentAttempt::STATUS_FAILED,
            'error_code'    => 'HASH_INVALID',
            'error_message' => 'msg.err.payment.hash_invalid',
            'raw_request'   => $this->safeRaw(['source' => 'callback', 'payload' => $payload]),
            'raw_response'  => $this->safeRaw(['source' => 'callback', 'payload' => $payload]),
            'completed_at'  => now(),
        ])->save();
    }

    private function safeRaw(?array $raw): ?array
    {
        if (app()->isProduction()) {
            return null;
        }

        return is_array($raw) ? $raw : null;
    }
}
