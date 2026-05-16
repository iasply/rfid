<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken
{
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            $sessionToken   = $request->session()->token();
            $requestToken   = $this->getTokenFromRequest($request);

            Log::warning('419: CSRF token mismatch', [
                // o que veio na requisição
                'request' => [
                    'url'              => $request->fullUrl(),
                    'method'           => $request->method(),
                    'ip'               => $request->ip(),
                    'user_agent'       => $request->userAgent(),
                    'header_xcsrf'     => $request->header('X-CSRF-TOKEN'),
                    'header_xsrf'      => $request->header('X-XSRF-TOKEN'),
                    'form_token'       => $request->input('_token'),
                    'resolved_token'   => $requestToken,
                ],
                // o que está no banco (sessão)
                'session' => [
                    'id'               => $request->session()->getId(),
                    'token'            => $sessionToken,
                    'token_match'      => $sessionToken === $requestToken,
                    'user_id'          => auth()->id(),
                ],
            ]);
            throw $e;
        }
    }
}
