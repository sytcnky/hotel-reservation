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

        /**
         * Nestpay return/callback POST'ları bankadan gelir; CSRF token yoktur.
         * Kontrat gereği bu endpoint'ler CSRF hariç olmalıdır.
         */
        $middleware->validateCsrfTokens(except: [
            'payment/nestpay/callback',
            'payment/nestpay/return/ok',
            'payment/nestpay/return/fail',
        ]);


        // Tunnel Bağlantısı için eklendi
        $middleware->append(\App\Http\Middleware\TrustProxies::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            // Şimdilik boş: ödeme tarafında kart input'u yok.
        ]);
    })
    ->create();
