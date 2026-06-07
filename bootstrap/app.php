<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'email.verification' => \App\Http\Middleware\RequireEmailVerification::class,
            'municipio.acceso' => \App\Http\Middleware\CheckMunicipioAccess::class,
        ]);

        $middleware->authenticateSessions();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // DomainException → flash error amigable (regla de negocio violada)
        $exceptions->render(function (\DomainException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        });
    })->create();
