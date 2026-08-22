<?php

namespace App\Modules\Admin\Data;

use App\Models\User;
use App\Modules\Authorization\RoleName;

/**
 * @phpstan-type AdminUserPayload array{
 *     user_id: string,
 *     name: string,
 *     email: string,
 *     verified: bool,
 *     roles: list<string>,
 *     authorization_version: int
 * }
 */
final class AdminUserData
{
    /** @return AdminUserPayload */
    public static function fromModel(User $user): array
    {
        $manageableRoles = [RoleName::Admin->value, RoleName::SuperAdmin->value];
        $roles = [];

        foreach ($user->roles->pluck('name')->all() as $role) {
            if (is_string($role) && in_array($role, $manageableRoles, true)) {
                $roles[] = $role;
            }
        }

        sort($roles);

        return [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'verified' => $user->email_verified_at !== null,
            'roles' => $roles,
            'authorization_version' => $user->authorization_version,
        ];
    }
}
