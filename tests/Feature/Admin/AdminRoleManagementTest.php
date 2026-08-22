<?php

use App\Models\User;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\AuthorizationSession;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('only super-admins can open the role management workspace', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);
    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);

    $this->actingAs($superAdmin)
        ->withSession([AuthorizationSession::KEY => $superAdmin->authorization_version])
        ->get('/admin/roles')
        ->assertOk();

    $this->actingAs($admin)
        ->withSession([AuthorizationSession::KEY => $admin->authorization_version])
        ->get('/admin/roles')
        ->assertForbidden();
});

test('a super-admin can create a custom role from catalog permissions', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($superAdmin)
        ->withSession([AuthorizationSession::KEY => $superAdmin->authorization_version])
        ->post('/admin/roles', [
            'name' => 'planning-reviewer',
            'permissions' => ['planning.view'],
        ])
        ->assertRedirect('/admin/roles');

    expect(DB::table('roles')->where('name', 'planning-reviewer')->where('guard_name', 'web')->exists())->toBeTrue();
});

test('a custom role cannot impersonate a protected role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($superAdmin)
        ->withSession([AuthorizationSession::KEY => $superAdmin->authorization_version])
        ->from('/admin/roles')
        ->post('/admin/roles', [
            'name' => 'Super Admin',
            'permissions' => ['planning.view'],
        ])
        ->assertSessionHasErrors('name')
        ->assertRedirect('/admin/roles');
});

test('a super-admin can update a custom role permission mapping', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);
    $role = Role::create(['name' => 'planning-reviewer', 'guard_name' => 'web']);
    $role->syncPermissions(['planning.view']);

    $this->actingAs($superAdmin)
        ->withSession([AuthorizationSession::KEY => $superAdmin->authorization_version])
        ->patch('/admin/roles/planning-reviewer', [
            'name' => 'planning-auditor',
            'permissions' => ['planning.create', 'planning.view'],
            'updated_at' => $role->updated_at->toISOString(),
        ])
        ->assertRedirect('/admin/roles');

    expect(Role::findByName('planning-auditor')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['planning.create', 'planning.view']);
});

test('an assigned custom role cannot be retired', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);
    $role = Role::create(['name' => 'planning-reviewer', 'guard_name' => 'web']);
    $assignedUser = User::factory()->create();
    $assignedUser->assignRole($role);

    $this->actingAs($superAdmin)
        ->withSession([AuthorizationSession::KEY => $superAdmin->authorization_version])
        ->from('/admin/roles')
        ->delete('/admin/roles/planning-reviewer')
        ->assertSessionHasErrors('roles')
        ->assertRedirect('/admin/roles');

    expect(Role::findByName('planning-reviewer'))->not->toBeNull();
});

test('the role workspace exposes safe role and catalog data without internal identifiers', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);
    $role = Role::create(['name' => 'planning-reviewer', 'guard_name' => 'web']);
    $role->syncPermissions(['planning.view']);

    $this->actingAs($superAdmin)
        ->withSession([AuthorizationSession::KEY => $superAdmin->authorization_version])
        ->get('/admin/roles')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Roles/Index')
            ->where('roles.1.name', 'planning-reviewer')
            ->where('roles.1.protected', false)
            ->where('roles.1.assignment_count', 0)
            ->has('roles.1.updated_at')
            ->missing('roles.1.id')
            ->has('permission_groups'));
});
