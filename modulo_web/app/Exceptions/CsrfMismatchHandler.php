<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Trata 419 (CSRF token mismatch) sem expor a tela de erro padrão do Laravel.
 *
 * Importante: o Laravel converte TokenMismatchException → HttpException(419) antes
 * dos render callbacks. Por isso recebemos HttpException e identificamos a causa
 * via $e->getPrevious().
 */
class CsrfMismatchHandler
{
    public function __invoke(HttpException $e, Request $request)
    {
        if (! $this->isCsrfMismatch($e) || $request->expectsJson()) {
            return null;
        }

        $this->logMismatch($request);
        $this->cleanupSession($request);
        $this->flashError($request);

        return redirect()->route('login');
    }

    private function isCsrfMismatch(HttpException $e): bool
    {
        return $e->getStatusCode() === 419
            && $e->getPrevious() instanceof TokenMismatchException;
    }

    private function logMismatch(Request $request): void
    {
        try {
            Log::warning('419: CSRF token mismatch', [
                'request' => [
                    'url'          => $request->fullUrl(),
                    'method'       => $request->method(),
                    'ip'           => $request->ip(),
                    'user_agent'   => $request->userAgent(),
                    'header_xcsrf' => $request->header('X-CSRF-TOKEN'),
                    'form_token'   => $request->input('_token'),
                ],
                'session' => [
                    'id'      => $request->hasSession() ? $request->session()->getId() : null,
                    'token'   => $request->hasSession() ? $request->session()->token() : null,
                    'user_id' => auth()->id(),
                ],
            ]);
        } catch (\Throwable) {
            // log nunca pode quebrar o handler
        }
    }

    private function cleanupSession(Request $request): void
    {
        try {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Log::info('session: 419 — logout + invalidate + regenerateToken', [
                'url'        => $request->fullUrl(),
                'session_id' => $request->session()->getId(),
            ]);
        } catch (\Throwable $sessionError) {
            Log::warning('419: falha ao limpar sessão corrompida', [
                'url'   => $request->fullUrl(),
                'error' => $sessionError->getMessage(),
            ]);
            try {
                $request->session()->regenerateToken();
            } catch (\Throwable $tokenError) {
                Log::error('419: falha ao regenerar token após sessão corrompida', [
                    'url'   => $request->fullUrl(),
                    'error' => $tokenError->getMessage(),
                ]);
            }
        }
    }

    private function flashError(Request $request): void
    {
        try {
            $request->session()->flash(
                'error',
                'Sua sessão expirou. Por favor, faça login novamente.'
            );
        } catch (\Throwable) {}
    }
}
