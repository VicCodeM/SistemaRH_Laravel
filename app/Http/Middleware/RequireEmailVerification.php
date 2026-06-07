<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireEmailVerification
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('auth.require_email_verification', false)) {
            return $next($request);
        }

        if ($request->user()?->hasVerifiedEmail()) {
            return $next($request);
        }

        return redirect()->route('verification.notice');
    }
}
