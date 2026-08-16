<?php

namespace App\Modules\Authorization;

final readonly class AuthorizationSyncResult
{
    /**
     * @param  list<string>  $createdPermissions
     * @param  list<string>  $createdRoles
     * @param  list<string>  $addedRolePermissions
     * @param  list<string>  $unknownPermissions
     * @param  list<string>  $unknownRoles
     * @param  list<string>  $unexpectedRolePermissions
     */
    public function __construct(
        public array $createdPermissions,
        public array $createdRoles,
        public array $addedRolePermissions,
        public array $unknownPermissions,
        public array $unknownRoles,
        public array $unexpectedRolePermissions,
        public int $directPermissionAssignments,
    ) {}

    public function changed(): bool
    {
        return $this->createdPermissions !== []
            || $this->createdRoles !== []
            || $this->addedRolePermissions !== [];
    }

    public function hasDrift(): bool
    {
        return $this->drift() !== [];
    }

    /** @return list<string> */
    public function drift(): array
    {
        $drift = [
            ...array_map(fn (string $name): string => "permission:{$name}", $this->unknownPermissions),
            ...array_map(fn (string $name): string => "role:{$name}", $this->unknownRoles),
            ...array_map(fn (string $name): string => "role-permission:{$name}", $this->unexpectedRolePermissions),
        ];

        if ($this->directPermissionAssignments > 0) {
            $drift[] = "direct-permission-assignments:{$this->directPermissionAssignments}";
        }

        sort($drift);

        return $drift;
    }
}
