<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class TestNestpayHash extends Command
{
    protected $signature = 'nestpay:hash-test
                            {path : RAW dosya yolu}
                            {--dump-plain : Plaintext preview yazdır}';

    protected $description = 'Offline Nestpay Ver3 response HASH verification (bank ORDER kontratına göre).';

    /**
     * Bankanın maille verdiği sıralama.
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
    ];

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Bu komut production ortamında çalıştırılamaz.');
            return self::FAILURE;
        }

        $path = (string) $this->argument('path');

        if (! is_file($path)) {
            $this->error("Dosya bulunamadı: {$path}");
            return self::FAILURE;
        }

        $raw = (string) file_get_contents($path);

        $storeKey = trim((string) config('icr.payments.nestpay.store_key'));
        if ($storeKey === '') {
            $this->error('STORE KEY boş.');
            return self::FAILURE;
        }

        $payload = $this->parseRaw($raw);

        $receivedHash = trim((string) ($payload['HASH'] ?? $payload['hash'] ?? ''));

        if ($receivedHash === '') {
            $this->error('HASH alanı bulunamadı.');
            return self::FAILURE;
        }

        [$computed, $plaintext] = $this->computeHash($payload, $storeKey);

        $this->line('---');
        $this->line('Received: ' . $receivedHash);
        $this->line('Computed: ' . $computed);
        $this->line('Plaintext len: ' . strlen($plaintext));
        $this->line('---');

        if (hash_equals($computed, $receivedHash)) {
            $this->info('MATCH ✅');

            if ($this->option('dump-plain')) {
                $this->line('Plaintext preview (first 400):');
                $this->line(substr($plaintext, 0, 400));
            }

            return self::SUCCESS;
        }

        $this->error('NO MATCH ❌');

        if ($this->option('dump-plain')) {
            $this->line('Plaintext preview (first 400):');
            $this->line(substr($plaintext, 0, 400));
        }

        return self::FAILURE;
    }

    private function parseRaw(string $raw): array
    {
        $result = [];

        foreach (explode('&', $raw) as $pair) {
            if ($pair === '') continue;

            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');

            // Bankanın verdiği string boşluklu → urldecode kullanıyoruz
            $key = urldecode($key);
            $value = urldecode($value);

            $result[$key] = $value;
        }

        return $result;
    }

    private function computeHash(array $payload, string $storeKey): array
    {
        $map = [];

        foreach ($payload as $k => $v) {
            if (! is_string($k)) continue;
            $map[strtolower($k)] = is_scalar($v) ? (string) $v : '';
        }

        $hashVal = '';

        foreach (self::ORDER as $key) {
            $value = $map[strtolower($key)] ?? '';
            $hashVal .= $this->escape($value) . '|';
        }

        $hashVal .= $this->escape($storeKey);

        $hex = hash('sha512', $hashVal);
        $computed = base64_encode(pack('H*', $hex));

        return [$computed, $hashVal];
    }

    private function escape(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('|', '\|', $value);
        return $value;
    }
}
