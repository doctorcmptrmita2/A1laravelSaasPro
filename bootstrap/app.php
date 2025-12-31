<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // API route'larına web middleware'lerini ekle (SPA authentication için)
        // Session, cookie encryption ve CSRF için web middleware'leri gerekli
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'identify.tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'ensure.subscription' => \App\Http\Middleware\EnsureSubscription::class,
        ]);
        
        // Trust all proxies (Easypanel reverse proxy için)
        $middleware->trustProxies(at: '*');
        
        // API route'ları için CSRF token kontrolünü devre dışı bırak
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
