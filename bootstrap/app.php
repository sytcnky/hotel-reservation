<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocaleFromRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie/permission middleware aliasları
        $middleware->alias([
            'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        ]);

        // Sadece web grubunda, session'dan sonra locale ayarlansın
        $middleware->web(append: [
            SetLocaleFromRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * PCI/PII policy:
         * - Validation fail olduğunda request input session'a "old input" olarak flash'lanabilir.
         * - Kart verileri (PAN/CVC/expiry) kesinlikle flash'lanmamalı.
         *
         * Not: Bu liste globaldir; sadece payment değil tüm formlar için güvenlik bariyeridir.
         */
        $exceptions->dontFlash([
            // Cardholder
            'cardholder',

            // PAN (card number)
            'cardnumber',
            'card_number',
            'pan',
            'number',

            // Expiry
            'exp-month',
            'exp_month',
            'exp-year',
            'exp_year',
            'expiry',
            'expires',
            'exp',

            // CVC/CVV
            'cvc',
            'cvv',
            'cvv2',

            // Demo helper input (non-sensitive ama payment akışıyla birlikte taşınmasın)
            'demo_outcome',
        ]);
    })
    ->create();
