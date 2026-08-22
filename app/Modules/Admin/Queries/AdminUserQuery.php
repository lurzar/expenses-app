<?php

namespace App\Modules\Admin\Queries;

use App\Models\User;
use App\Modules\Admin\Data\AdminUserData;
use App\Modules\Authorization\RoleName;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/** @phpstan-import-type AdminUserPayload from AdminUserData */
final class AdminUserQuery
{
    /**
     * @param  array{search: string, status: string, role: string}  $filters
     * @return LengthAwarePaginator<int, AdminUserPayload>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()
            ->select(['id', 'user_id', 'name', 'email', 'email_verified_at', 'authorization_version'])
            ->with(['roles:id,name']);

        if ($filters['search'] !== '') {
            $search = '%'.$filters['search'].'%';
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        if ($filters['status'] === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($filters['status'] === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        if (in_array($filters['role'], [RoleName::Admin->value, RoleName::SuperAdmin->value], true)) {
            $query->whereHas('roles', fn (Builder $query): Builder => $query
                ->whereRaw('roles.name = ? and roles.guard_name = ?', [$filters['role'], 'web']));
        } elseif ($filters['role'] === 'none') {
            $query->whereDoesntHave('roles', fn (Builder $query): Builder => $query
                ->whereRaw('roles.name in (?, ?) and roles.guard_name = ?', [
                    RoleName::Admin->value,
                    RoleName::SuperAdmin->value,
                    'web',
                ]));
        }

        return $query
            ->orderBy('name')
            ->orderBy('user_id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => AdminUserData::fromModel($user));
    }
}
