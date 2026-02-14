<?php

namespace App\Http\Controllers\Payments\Nestpay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NestpayReturnController extends Controller
{
    public function ok(Request $request)
    {
        $oid = $this->readOid($request);

        if (! app()->isProduction()) {
            $sessionCookieName = config('session.cookie');
            Log::info('nestpay_return_session_debug', [
                'hint'          => 'ok', // fail() içinde 'fail'
                'method'        => $request->method(),
                'oid'           => $oid,
                'has_session'   => $request->hasSession(),
                'session_id'    => $request->hasSession() ? $request->session()->getId() : null,
                'cookie_name'   => $sessionCookieName,
                'cookie_present'=> $request->cookies->has($sessionCookieName),
                'auth_check'    => auth()->check(),
                'user_id'       => auth()->id(),
            ]);
        }

        if (! app()->isProduction()) {
            Log::info('nestpay_return_hit', [
                'hint'   => 'ok',
                'method' => $request->method(),
                'oid'    => $oid,
                'host'   => $request->getHost(),
            ]);
        }

        return redirect()->to(localized_route('payment.result', [
            'oid'  => $oid,
            'hint' => 'ok',
        ]));
    }

    public function fail(Request $request)
    {
        $oid = $this->readOid($request);

        if (! app()->isProduction()) {
            Log::info('nestpay_return_hit', [
                'hint'   => 'fail',
                'method' => $request->method(),
                'oid'    => $oid,
                'host'   => $request->getHost(),
            ]);
        }

        return redirect()->to(localized_route('payment.result', [
            'oid'  => $oid,
            'hint' => 'fail',
        ]));
    }

    private function readOid(Request $request): string
    {
        // Banka bazen query ile, bazen POST body ile dönebilir.
        $oid = trim((string) ($request->query('oid') ?? ''));
        if ($oid !== '') {
            return $oid;
        }

        $oid = trim((string) ($request->input('oid') ?? $request->input('OID') ?? ''));
        return $oid;
    }
}
