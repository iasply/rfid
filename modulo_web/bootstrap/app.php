<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/desktop',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if (! $request->expectsJson()) {
                try {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    \Illuminate\Support\Facades\Log::info('session: 419 — logout + invalidate + regenerateToken', [
                        'url'        => $request->fullUrl(),
                        'ip'         => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'session_id' => $request->session()->getId(),
                    ]);
                } catch (\Throwable $sessionError) {
                    \Illuminate\Support\Facades\Log::warning('419: falha ao limpar sessão corrompida', [
                        'url'   => $request->fullUrl(),
                        'error' => $sessionError->getMessage(),
                    ]);
                    try {
                        $request->session()->regenerateToken();
                    } catch (\Throwable $tokenError) {
                        \Illuminate\Support\Facades\Log::error('419: falha ao regenerar token após sessão corrompida', [
                            'url'   => $request->fullUrl(),
                            'error' => $tokenError->getMessage(),
                        ]);
                    }
                }
                return redirect()->route('login')
                    ->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
            }
        });
    })->create();
