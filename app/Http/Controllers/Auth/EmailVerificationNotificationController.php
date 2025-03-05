<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        // Send an email verification notification asynchronously 
        // $request->user()->sendEmailVerificationNotification();

        // Dispatch an email verification notification job
        SendEmailVerification::dispatch($request->user());

        return back()->with('status', 'verification-link-sent');
    }
}
