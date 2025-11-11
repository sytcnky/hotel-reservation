<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

final class EmailVerificationController extends Controller
{
    // GET /email/verify
    public function notice()
    {
        return view('pages.auth.verify-email');
    }

    // GET /email/verify/{id}/{hash}
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(localized_route('home'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect(localized_route('home'))
            ->with('status', 'email-verified');
    }

    // POST /email/verification-notification
    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(localized_route('home'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
