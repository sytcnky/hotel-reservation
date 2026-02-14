<?php

namespace App\Http\Controllers\Payments\Nestpay;

use App\Http\Controllers\Controller;
use App\Models\PaymentAttempt;
use Illuminate\Http\Request;

class NestpayStatusController extends Controller
{
    public function show(Request $request)
    {
        $oid = trim((string) $request->query('oid'));
        if ($oid === '') {
            return response()->json(['ok' => false, 'error' => 'missing_oid'], 400);
        }

        $attempt = $this->findAttemptByOid($oid);

        if (! $attempt) {
            return response()->json(['ok' => false, 'error' => 'unknown_oid'], 404);
        }

        $status = (string) $attempt->status;

        $payload = [
            'ok'     => true,
            'status' => $status, // pending|success|failed|...
        ];

        if ($status === PaymentAttempt::STATUS_FAILED) {
            $payload['error_code'] = $attempt->error_code ?: null;
            $payload['message'] = $attempt->error_message ?: null;
        }

        if ($status === PaymentAttempt::STATUS_SUCCESS) {
            // Order code göstermeyi şimdilik istemiyorsun; burada taşımıyoruz.
            // İleride gerekirse attempt->order_id üzerinden eklenir.
        }

        return response()->json($payload, 200);
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
            ->where('id', $attemptId)
            ->withoutTrashed()
            ->first();
    }
}
