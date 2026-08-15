<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user->hasVerifiedEmail()
            ? redirect()->intended(RedirectIfAuthenticated::HOME)
            : Inertia::render('Auth/VerifyEmail', [
                'status' => session('status'),
            ]);
    }
}
