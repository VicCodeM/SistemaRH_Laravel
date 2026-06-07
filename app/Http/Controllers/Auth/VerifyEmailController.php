<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if (! config('auth.require_email_verification', false)) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
            try {
                $request->user()->sendWelcomeVerifiedNotification();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
