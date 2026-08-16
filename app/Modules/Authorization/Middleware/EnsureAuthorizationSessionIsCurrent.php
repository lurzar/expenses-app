<?php

namespace App\Modules\Authorization\Middleware;

use App\Models\User;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\AuthorizationSession;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class EnsureAuthorizationSessionIsCurrent
{
    public function __construct(private readonly AuthorizationSession $authorizationSession) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (! $user instanceof User || ! $request->hasSession()) {
            return $next($request);
        }

        if (! $this->authorizationSession->hasVersion($request->session())) {
            if ($user->hasRole(RoleName::SuperAdmin->value) || $user->authorization_version !== 0) {
                return $this->logout($request);
            }

            $this->authorizationSession->bind($request->session(), $user);

            return $next($request);
        }

        if (! $this->authorizationSession->isCurrent($request->session(), $user)) {
            return $this->logout($request);
        }

        return $next($request);
    }

    private function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
