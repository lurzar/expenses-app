<?php

use App\Models\User;
use App\Modules\Authorization\Exceptions\RoleAssignmentException;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\AuthorizationSession;
use App\Modules\Authorization\Services\RoleAssignmentService;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function adminOperator(RoleName $role = RoleName::Admin): User
{
    $operator = User::factory()->create();
    $operator->assignRole($role->value);

    return $operator;
}

test('user administration routes enforce authentication verification and Laravel abilities', function () {
    $this->get('/admin/users')->assertRedirect(route('login'));

    $unverified = User::factory()->unverified()->create();
    $unverified->assignRole(RoleName::Admin->value);
    $this->actingAs($unverified)->get('/admin/users')->assertRedirect(route('verification.notice'));

    $this->actingAs(User::factory()->create())->get('/admin/users')->assertForbidden();
});

test('administrators receive a paginated minimized user payload using public identifiers', function () {
    $operator = adminOperator();
    $subject = User::factory()->create([
        'name' => 'Searchable Account',
        'email' => 'searchable@example.test',
    ]);

    $this->actingAs($operator)
        ->get('/admin/users?search=Searchable&status=verified&role=none')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->where('filters', [
                'search' => 'Searchable',
                'status' => 'verified',
                'role' => 'none',
            ])
            ->has('users.data', 1)
            ->where('users.data.0.user_id', $subject->user_id)
            ->where('users.data.0.name', 'Searchable Account')
            ->where('users.data.0.email', 'searchable@example.test')
            ->where('users.data.0.verified', true)
            ->where('users.data.0.roles', [])
            ->where('users.data.0.authorization_version', 0)
            ->missing('users.data.0.id')
            ->missing('users.data.0.password')
            ->missing('users.data.0.remember_token')
            ->missing('users.data.0.plannings')
            ->where('capabilities.manage_super_admin', false));
});

test('super administrators see the protected role capability and localized role metadata', function () {
    $operator = adminOperator(RoleName::SuperAdmin);

    $this->actingAs($operator)
        ->withSession([AuthorizationSession::KEY => $operator->authorization_version])
        ->get('/admin/users')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('capabilities.manage_super_admin', true)
            ->where('role_options.0.value', 'admin')
            ->where('role_options.1.value', 'super-admin')
            ->has('role_options.0.permissions', 2)
            ->has('role_options.1.permissions', 3));
});

test('the user query validates bounded filters', function () {
    $operator = adminOperator();

    $this->actingAs($operator)
        ->get('/admin/users?status=deleted&role=user')
        ->assertSessionHasErrors(['status', 'role']);
});

test('an admin can grant and remove admin while preserving the base user role', function () {
    $actor = adminOperator();
    $subject = User::factory()->create(['remember_token' => 'known-token']);

    $this->actingAs($actor)
        ->patch("/admin/users/{$subject->user_id}/roles", [
            'roles' => ['admin'],
            'authorization_version' => 0,
        ])->assertRedirect();

    $subject->refresh();
    expect($subject->hasExactRoles([RoleName::User->value, RoleName::Admin->value]))->toBeTrue()
        ->and($subject->authorization_version)->toBe(1)
        ->and($subject->remember_token)->not->toBe('known-token');

    $this->actingAs($actor)
        ->patch("/admin/users/{$subject->user_id}/roles", [
            'roles' => [],
            'authorization_version' => 1,
        ])->assertRedirect();

    expect($subject->fresh()->hasExactRoles([RoleName::User->value]))->toBeTrue()
        ->and(DB::table('activity_logs')->where('subject_id', $subject->user_id)->count())->toBe(2);
});

test('role submissions reject unknown and base roles', function (array $roles) {
    $actor = adminOperator();
    $subject = User::factory()->create();

    $this->actingAs($actor)
        ->patch("/admin/users/{$subject->user_id}/roles", [
            'roles' => $roles,
            'authorization_version' => 0,
        ])->assertSessionHasErrors('roles.0');

    expect($subject->fresh()->hasExactRoles([RoleName::User->value]))->toBeTrue();
})->with([
    'base role' => [['user']],
    'unknown role' => [['owner']],
    'duplicates' => [['admin', 'admin']],
]);

test('ordinary admins cannot mutate super admin membership through requests or the service', function () {
    $actor = adminOperator();
    $subject = User::factory()->create();

    $this->actingAs($actor)
        ->patch("/admin/users/{$subject->user_id}/roles", [
            'roles' => ['super-admin'],
            'authorization_version' => 0,
        ])->assertForbidden();

    expect(fn () => app(RoleAssignmentService::class)->syncAdministrativeRoles(
        $actor,
        $subject,
        [RoleName::SuperAdmin],
        0,
    ))->toThrow(RoleAssignmentException::class, 'You are not allowed to manage super-admin access.');
});

test('the service reauthorizes standard role managers and rejects non administrative desired roles', function () {
    $ordinaryUser = User::factory()->create();
    $subject = User::factory()->create();
    $service = app(RoleAssignmentService::class);

    expect(fn () => $service->syncAdministrativeRoles(
        $ordinaryUser,
        $subject,
        [RoleName::Admin],
        0,
    ))->toThrow(RoleAssignmentException::class, 'You are not allowed to manage administrative roles.');

    $actor = adminOperator();

    expect(fn () => $service->syncAdministrativeRoles(
        $actor,
        $subject,
        [RoleName::User],
        0,
    ))->toThrow(RoleAssignmentException::class, 'Only approved administrative roles can be managed.');
});

test('administrative role changes fail closed when activity capture is unavailable', function () {
    config()->set('activity-log.enabled', false);
    $actor = adminOperator();
    $subject = User::factory()->create();

    expect(fn () => app(RoleAssignmentService::class)->syncAdministrativeRoles(
        $actor,
        $subject,
        [RoleName::Admin],
        0,
    ))->toThrow(RoleAssignmentException::class, 'Role changes require activity logging.');

    expect($subject->fresh()->hasRole(RoleName::Admin->value))->toBeFalse();
});

test('a super administrator can grant and remove protected access for another active account', function () {
    $actor = adminOperator(RoleName::SuperAdmin);
    $subject = User::factory()->create();

    $this->actingAs($actor)
        ->withSession([AuthorizationSession::KEY => $actor->authorization_version])
        ->patch("/admin/users/{$subject->user_id}/roles", [
            'roles' => ['super-admin'],
            'authorization_version' => 0,
        ])->assertRedirect();

    expect($subject->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeTrue();

    $this->actingAs($actor->fresh())
        ->withSession([AuthorizationSession::KEY => $actor->fresh()->authorization_version])
        ->patch("/admin/users/{$subject->user_id}/roles", [
            'roles' => [],
            'authorization_version' => 1,
        ])->assertRedirect();

    expect($subject->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeFalse();
});

test('operators cannot remove their own administrative role', function (RoleName $role) {
    $actor = adminOperator($role);

    expect(fn () => app(RoleAssignmentService::class)->syncAdministrativeRoles(
        $actor,
        $actor,
        [],
        0,
    ))->toThrow(RoleAssignmentException::class, 'You cannot remove your own administrative access.');
})->with([
    'admin' => [RoleName::Admin],
    'super admin' => [RoleName::SuperAdmin],
]);

test('unverified users cannot receive administrative roles but existing roles can be removed', function () {
    $actor = adminOperator(RoleName::SuperAdmin);
    $subject = User::factory()->unverified()->create();

    expect(fn () => app(RoleAssignmentService::class)->syncAdministrativeRoles(
        $actor,
        $subject,
        [RoleName::Admin],
        0,
    ))->toThrow(RoleAssignmentException::class, 'Administrative access requires an active verified account.');

    $subject->assignRole(RoleName::Admin->value);

    expect(app(RoleAssignmentService::class)->syncAdministrativeRoles(
        $actor,
        $subject->fresh(),
        [],
        0,
    ))->toBeTrue();
});

test('soft deleted accounts cannot be addressed through the Admin route', function () {
    $actor = adminOperator(RoleName::SuperAdmin);
    $subject = User::factory()->create();
    $subject->delete();

    $this->actingAs($actor)
        ->withSession([AuthorizationSession::KEY => $actor->authorization_version])
        ->patch("/admin/users/{$subject->user_id}/roles", [
            'roles' => ['admin'],
            'authorization_version' => 1,
        ])->assertNotFound();
});

test('desired role state is idempotent and changed stale submissions fail without mutation', function () {
    $actor = adminOperator();
    $subject = User::factory()->create();
    $service = app(RoleAssignmentService::class);

    expect($service->syncAdministrativeRoles($actor, $subject, [RoleName::Admin], 0))->toBeTrue()
        ->and($service->syncAdministrativeRoles($actor, $subject->fresh(), [RoleName::Admin], 0))->toBeFalse();

    expect(fn () => $service->syncAdministrativeRoles($actor, $subject->fresh(), [], 0))
        ->toThrow(RoleAssignmentException::class, 'The account roles changed. Refresh and try again.');

    expect($subject->fresh()->hasRole(RoleName::Admin->value))->toBeTrue();
});

test('the user administration migration refuses to discard unexpected protected mappings', function () {
    $admin = Role::findByName(RoleName::Admin->value);
    $admin->givePermissionTo('users.manage-super-admin');
    $migration = require app_path('Modules/Authorization/Database/Migrations/2026_08_17_100000_add_user_administration_permissions.php');

    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'User administration rollback would discard authorization state.');

    expect($admin->fresh()->hasPermissionTo('users.manage-super-admin'))->toBeTrue();
});

test('the user administration migration refuses to discard direct permission assignments', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');
    $migration = require app_path('Modules/Authorization/Database/Migrations/2026_08_17_100000_add_user_administration_permissions.php');

    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'User administration rollback would discard authorization state.');

    expect($user->fresh()->hasDirectPermission('users.view'))->toBeTrue();
});

test('the user administration migration refuses to activate unsafe preexisting mappings', function () {
    $migration = require app_path('Modules/Authorization/Database/Migrations/2026_08_17_100000_add_user_administration_permissions.php');
    $migration->down();

    $permission = Permission::create([
        'name' => 'users.manage-super-admin',
        'guard_name' => 'web',
    ]);
    Role::findByName(RoleName::Admin->value)->givePermissionTo($permission);

    expect(fn () => $migration->up())
        ->toThrow(RuntimeException::class, 'Pre-existing user administration permissions require drift review.');

    expect(Role::findByName(RoleName::Admin->value)
        ->hasPermissionTo('users.manage-super-admin'))->toBeTrue();
});
