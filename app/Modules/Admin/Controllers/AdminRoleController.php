<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Admin\Requests\StoreRoleRequest;
use App\Modules\Admin\Requests\UpdateRoleRequest;
use App\Modules\Authorization\Exceptions\RoleManagementException;
use App\Modules\Authorization\PermissionCatalog;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

final class AdminRoleController extends Controller
{
    public function store(StoreRoleRequest $request, RoleManagementService $roles): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        try {
            $roles->create($actor, $request->validated('name'), $request->validated('permissions'));
        } catch (RoleManagementException $exception) {
            return back()->withErrors([$exception->field => $exception->getMessage()]);
        }

        return to_route('admin.roles.index')->with('success', __('admin.role_created'));
    }

    public function update(UpdateRoleRequest $request, string $role, RoleManagementService $roles): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        try {
            $roles->update(
                $actor,
                $role,
                $request->validated('name'),
                $request->validated('permissions'),
                $request->validated('updated_at'),
            );
        } catch (RoleManagementException $exception) {
            return back()->withErrors([$exception->field => $exception->getMessage()]);
        }

        return to_route('admin.roles.index')->with('success', __('admin.role_updated'));
    }

    public function destroy(Request $request, string $role, RoleManagementService $roles): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        try {
            $roles->retire($actor, $role);
        } catch (RoleManagementException $exception) {
            return back()->withErrors([$exception->field => $exception->getMessage()]);
        }

        return to_route('admin.roles.index')->with('success', __('admin.role_retired'));
    }

    public function index(PermissionCatalog $catalog): Response
    {
        $roles = Role::query()
            ->with('permissions:name')
            ->withCount('users')
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'name' => $role->name,
                'protected' => RoleName::tryFrom($role->name) !== null,
                'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
                'unknown_permissions' => $role->permissions
                    ->pluck('name')
                    ->diff($catalog->names())
                    ->sort()
                    ->values()
                    ->all(),
                'assignment_count' => $role->users_count,
                'updated_at' => $role->updated_at?->toISOString(),
            ])
            ->values()
            ->all();

        $permissionGroups = collect($catalog->definitions())
            ->map(fn ($permission): array => [
                'name' => $permission->value,
                'module' => $permission->module(),
                'label' => __($permission->labelKey()),
                'description' => __($permission->descriptionKey()),
            ])
            ->groupBy('module')
            ->map(fn ($permissions, string $module): array => [
                'module' => $module,
                'permissions' => $permissions->values()->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permission_groups' => $permissionGroups,
        ]);
    }
}
