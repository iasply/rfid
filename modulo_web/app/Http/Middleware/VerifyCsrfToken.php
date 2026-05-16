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
            Log::warning('419: CSRF token mismatch', [
                'url'           => $request->fullUrl(),
                'method'        => $request->method(),
                'ip'            => $request->ip(),
                'user_agent'    => $request->userAgent(),
                'session_token' => $request->session()->token(),
                'request_token' => $this->getTokenFromRequest($request),
                'user_id'       => auth()->id(),
            ]);
            throw $e;
        }
    }
}
