<?php

use App\Exceptions\StateConflictException;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAuthor;
use App\Http\Middleware\EnsureManualScoreIntent;
use App\Http\Middleware\EnsureUserIsOperator;
use App\Http\Middleware\EnsureUserIsPeserta;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        channels: __DIR__ . '/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'author' => EnsureUserIsAuthor::class,
            'operator' => EnsureUserIsOperator::class,
            'peserta' => EnsureUserIsPeserta::class,
            'manual_score_intent' => EnsureManualScoreIntent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (StateConflictException $exception, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 409);
            }

            return response($exception->getMessage(), 409);
        });
    })
    ->withProviders([
        App\Providers\EventServiceProvider::class,
    ])
    ->create();
