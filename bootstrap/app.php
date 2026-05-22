<?php

use App\Http\Middleware\EnsureAdminIsMaster;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'admin.master' => EnsureAdminIsMaster::class,
        ]);

        $middleware->preventRequestForgery(except: [
            'activities/*/like',
            'webhook/mercadopago',
        ]);

        $middleware->api(append: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            PreventRequestForgery::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request) => true);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('donations:expire')->everyFiveMinutes();
    })
    ->create();
