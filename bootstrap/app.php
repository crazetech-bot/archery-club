<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            $host          = request()->getHost();
            $centralDomain = env('TENANT_DOMAIN', 'fmsport.biz');

            // Load tenant routes only when the request comes from a subdomain
            if ($host !== $centralDomain && str_ends_with($host, '.' . $centralDomain)) {
                Route::middleware('web')->group(base_path('routes/tenant.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'tenant'      => \App\Http\Middleware\InitializeTenancyBySubdomain::class,
            'subscribed'  => \App\Http\Middleware\EnsureSubscribed::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // When a session or CSRF cookie can't be decrypted (e.g. after an APP_KEY
        // change or a stale browser cookie), redirect to login and clear the bad
        // cookie rather than returning a 500 to the user.
        $exceptions->renderable(function (
            \Illuminate\Contracts\Encryption\DecryptException $e,
            \Illuminate\Http\Request $request
        ) {
            return redirect('/login')
                ->withCookie(\Illuminate\Support\Facades\Cookie::forget('laravel_session'))
                ->withCookie(\Illuminate\Support\Facades\Cookie::forget('XSRF-TOKEN'));
        });
    })->create();
