<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! config('auth.require_email_verification', false)) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo reenviar el correo de verificación. Intenta de nuevo en unos minutos.');
        }

        return back()->with('status', 'verification-link-sent');
    }
}
