<?php

namespace App\Modules\Authorization\Services;

use App\Models\User;
use Illuminate\Contracts\Session\Session;

final class AuthorizationSession
{
    public const KEY = 'auth.authorization_version';

    public function bind(Session $session, User $user): void
    {
        $session->put(self::KEY, $user->authorization_version);
    }

    public function hasVersion(Session $session): bool
    {
        return $session->has(self::KEY);
    }

    public function isCurrent(Session $session, User $user): bool
    {
        $version = $session->get(self::KEY);

        return is_int($version) && $version === $user->authorization_version;
    }
}
