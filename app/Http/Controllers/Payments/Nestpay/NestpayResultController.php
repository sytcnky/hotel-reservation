<?php

namespace App\Http\Controllers\Payments\Nestpay;

use App\Http\Controllers\Controller;
use App\Models\PaymentAttempt;
use Illuminate\Http\Request;

class NestpayResultController extends Controller
{
    public function show(Request $request)
    {
        $oid  = trim((string) $request->query('oid'));
        $hint = trim((string) $request->query('hint')); // ok|fail|...

        [$attempt, $checkoutCode] = $this->resolveAttemptAndCheckoutCode($oid);

        return view('pages.payment.result', [
            'oid'         => $oid,
            'hint'        => $hint,
            'attempt'     => $attempt,
            'checkoutCode'=> $checkoutCode,
        ]);
    }

    /**
     * @return array{0: ?PaymentAttempt, 1: ?string}
     */
    private function resolveAttemptAndCheckoutCode(string $oid): array
    {
        $oid = trim($oid);
        if ($oid === '') {
            return [null, null];
        }

        // OID standardı: PA-<id>
        if (str_starts_with($oid, 'PA-')) {
            $idPart = trim(substr($oid, 3));
            $attemptId = ctype_digit($idPart) ? (int) $idPart : 0;

            if ($attemptId > 0) {
                $attempt = PaymentAttempt::query()
                    ->with(['checkoutSession'])
                    ->where('id', $attemptId)
                    ->withoutTrashed()
                    ->first();

                $checkoutCode = (string) ($attempt?->checkoutSession?->code ?? '');
                return [$attempt, $checkoutCode !== '' ? $checkoutCode : null];
            }
        }

        return [null, null];
    }
}
