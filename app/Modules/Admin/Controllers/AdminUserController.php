<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Admin\Queries\AdminUserQuery;
use App\Modules\Admin\Requests\AdminUserIndexRequest;
use App\Modules\Admin\Requests\UpdateUserRolesRequest;
use App\Modules\Authorization\Exceptions\RoleAssignmentException;
use App\Modules\Authorization\Exceptions\SuperAdminLifecycleException;
use App\Modules\Authorization\Permissions\SystemPermission;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\RoleAssignmentService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AdminUserController extends Controller
{
    public function index(AdminUserIndexRequest $request, AdminUserQuery $users): Response
    {
        $filters = $request->filters();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users->paginate($filters),
            'filters' => $filters,
            'capabilities' => [
                'manage_super_admin' => $request->user()?->can(SystemPermission::ManageSuperAdmin->value) ?? false,
            ],
            'role_options' => $this->roleOptions(),
        ]);
    }

    public function update(
        UpdateUserRolesRequest $request,
        User $user,
        RoleAssignmentService $roles,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        $desiredRoles = $request->roles();
        $changesSuperAdmin = $user->hasRole(RoleName::SuperAdmin->value)
            !== collect($desiredRoles)->contains(RoleName::SuperAdmin);

        abort_if(
            $changesSuperAdmin && ! $actor->can(SystemPermission::ManageSuperAdmin->value),
            403,
        );

        try {
            $changed = $roles->syncAdministrativeRoles(
                $actor,
                $user,
                $desiredRoles,
                (int) $request->validated('authorization_version'),
            );
        } catch (RoleAssignmentException $exception) {
            return back()->withErrors([$exception->field => $exception->getMessage()]);
        } catch (SuperAdminLifecycleException $exception) {
            return back()->withErrors(['roles' => $exception->getMessage()]);
        }

        return back()->with(
            'success',
            $changed ? __('admin.roles_updated') : __('admin.roles_unchanged'),
        );
    }

    /** @return list<array{value: string, label: string, description: string, permissions: list<string>}> */
    private function roleOptions(): array
    {
        return [
            [
                'value' => RoleName::Admin->value,
                'label' => __('admin.role_admin'),
                'description' => __('admin.role_admin_description'),
                'permissions' => [
                    __(SystemPermission::ViewUsers->labelKey()),
                    __(SystemPermission::ManageUserRoles->labelKey()),
                ],
            ],
            [
                'value' => RoleName::SuperAdmin->value,
                'label' => __('admin.role_super_admin'),
                'description' => __('admin.role_super_admin_description'),
                'permissions' => [
                    __(SystemPermission::ViewUsers->labelKey()),
                    __(SystemPermission::ManageUserRoles->labelKey()),
                    __(SystemPermission::ManageSuperAdmin->labelKey()),
                ],
            ],
        ];
    }
}
