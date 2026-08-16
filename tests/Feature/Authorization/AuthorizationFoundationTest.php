<?php

use App\Models\User;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\PermissionCatalog;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\AuthorizationSynchronizer;
use App\Modules\Authorization\Services\RoleAssignmentService;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Permissions\PlanningPermission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('authorization persistence schema is installed', function () {
    expect(Schema::hasTable('roles'))->toBeTrue()
        ->and(Schema::hasTable('permissions'))->toBeTrue()
        ->and(Schema::hasTable('model_has_roles'))->toBeTrue()
        ->and(Schema::hasTable('role_has_permissions'))->toBeTrue();
});

test('authorization package configuration keeps deferred capabilities disabled', function () {
    expect(config('permission.teams'))->toBeFalse()
        ->and(config('permission.register_permission_check_method'))->toBeFalse()
        ->and(config('permission.enable_wildcard_permission'))->toBeFalse()
        ->and(config('permission.display_permission_in_exception'))->toBeFalse()
        ->and(config('permission.display_role_in_exception'))->toBeFalse()
        ->and(config('permission.cache.key'))->toBe('expenses.authorization.permission-cache');
});

test('the catalog exposes stable module-owned permissions', function () {
    $catalog = app(PermissionCatalog::class);

    expect($catalog->names())->toBe([
        'admin.access',
        'planning.create',
        'planning.delete',
        'planning.view',
    ])->and(PlanningPermission::View->module())->toBe('planning');
});

test('protected roles have only their reviewed permissions', function () {
    expect(Role::findByName(RoleName::User->value)->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['planning.create', 'planning.delete', 'planning.view'])
        ->and(Role::findByName(RoleName::Admin->value)->permissions->pluck('name')->all())
        ->toBe(['admin.access'])
        ->and(Role::findByName(RoleName::SuperAdmin->value)->permissions->pluck('name')->all())
        ->toBe(['admin.access']);
});

test('new accounts receive the base user role and Laravel Gate permissions', function () {
    $user = User::factory()->create();

    expect($user->hasExactRoles([RoleName::User->value]))->toBeTrue()
        ->and(Gate::forUser($user)->allows(PlanningPermission::View->value))->toBeTrue()
        ->and(Gate::forUser($user)->allows('admin.access'))->toBeFalse();
});

test('administrative roles receive explicit control-plane permissions without a global bypass', function (RoleName $role) {
    $operator = User::factory()->create();
    $operator->assignRole($role->value);
    $planning = Planning::factory()->for($operator)->create();
    $operator->removeRole(RoleName::User->value);

    expect(Gate::forUser($operator)->allows('admin.access'))->toBeTrue()
        ->and(Gate::forUser($operator)->allows('view', $planning))->toBeFalse();
})->with([
    'admin' => [RoleName::Admin],
    'super-admin' => [RoleName::SuperAdmin],
]);

test('Planning policy requires both capability and ownership', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();

    expect(Gate::forUser($owner)->allows('view', $planning))->toBeTrue()
        ->and(Gate::forUser($otherUser)->allows('view', $planning))->toBeFalse();

    $owner->removeRole(RoleName::User->value);

    expect(Gate::forUser($owner)->allows('view', $planning))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('delete', $planning))->toBeFalse();
});

test('missing declared permission rows fail closed until synchronization repairs them', function () {
    $user = User::factory()->create();
    Permission::findByName(PlanningPermission::View->value)->delete();

    expect(Gate::forUser($user)->allows(PlanningPermission::View->value))->toBeFalse();
});

test('undeclared generic permissions cannot bypass Planning ownership policies', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();
    Permission::create(['name' => 'view', 'guard_name' => 'web']);
    Permission::create(['name' => 'delete', 'guard_name' => 'web']);
    $attacker->givePermissionTo(['view', 'delete']);

    expect(Gate::forUser($attacker)->allows('view', $planning))->toBeFalse()
        ->and(Gate::forUser($attacker)->allows('delete', $planning))->toBeFalse();
});

test('missing capabilities fail closed at direct request boundaries', function () {
    $user = User::factory()->create();
    $planning = Planning::factory()->for($user)->create();
    $user->removeRole(RoleName::User->value);

    $this->actingAs($user)->get(route('planning.index'))->assertForbidden();
    $this->actingAs($user)->get(route('planning.create'))->assertForbidden();
    $this->actingAs($user)->post(route('planning.store'), [])->assertForbidden();
    $this->actingAs($user)->get(route('expenses.index'))->assertForbidden();
    $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    $this->actingAs($user)->get(route('planning.show', $planning))->assertForbidden();
    $this->actingAs($user)->delete(route('planning.destroy', $planning))->assertForbidden();
});

test('ordinary users receive a false Admin capability', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('auth.capabilities.access_admin', false));
});

test('shared Inertia data exposes a capability boolean without authorization internals', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::Admin->value);

    $this->actingAs($operator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.capabilities.access_admin', true)
            ->missing('auth.roles')
            ->missing('auth.permissions')
            ->missing('auth.user.id'));
});

test('permission translations remain server-side until an authorized page requests them', function () {
    $this->get(route('landing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->missing('translations.permissions'));

    expect(__('authorization::permissions.admin.access.label'))->toBe('Access Admin');
});

test('direct user permissions are retained and reported as drift', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('admin.access');

    $result = app(AuthorizationSynchronizer::class)->sync();

    expect($result->directPermissionAssignments)->toBe(1)
        ->and($result->drift())->toContain('direct-permission-assignments:1')
        ->and($user->fresh()->hasDirectPermission('admin.access'))->toBeTrue();
});

test('unexpected protected-role mappings are retained and reported as drift', function () {
    $legacyPermission = Permission::create(['name' => 'legacy.unknown', 'guard_name' => 'web']);
    $userRole = Role::findByName(RoleName::User->value);
    $userRole->givePermissionTo($legacyPermission);

    $result = app(AuthorizationSynchronizer::class)->sync();

    expect($result->unexpectedRolePermissions)->toBe(['user:legacy.unknown'])
        ->and($result->drift())->toContain('role-permission:user:legacy.unknown')
        ->and($userRole->fresh()->hasPermissionTo('legacy.unknown'))->toBeTrue();
});

test('non-web guard rows are retained and reported as malformed drift', function () {
    Permission::create(['name' => 'planning.view', 'guard_name' => 'api']);
    Role::create(['name' => 'admin', 'guard_name' => 'api']);

    $result = app(AuthorizationSynchronizer::class)->sync();

    expect($result->unknownPermissions)->toBe(['planning.view@api'])
        ->and($result->unknownRoles)->toBe(['admin@api'])
        ->and($result->drift())->toContain('permission:planning.view@api', 'role:admin@api');
});

test('role assignment mutations use package APIs and minimized transactional activity', function () {
    $actor = User::factory()->create();
    $subject = User::factory()->create();
    $service = app(RoleAssignmentService::class);

    expect($service->assign($actor, $subject, RoleName::Admin))->toBeTrue()
        ->and($service->assign($actor, $subject, RoleName::Admin))->toBeFalse()
        ->and($subject->fresh()->hasRole(RoleName::Admin->value))->toBeTrue();

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'authorization.role_assigned',
        'actor_id' => $actor->user_id,
        'subject_type' => 'account',
        'subject_id' => $subject->user_id,
        'metadata' => json_encode(['role' => 'admin']),
    ]);

    expect($service->remove($actor, $subject, RoleName::Admin))->toBeTrue()
        ->and($service->remove($actor, $subject, RoleName::Admin))->toBeFalse()
        ->and($subject->fresh()->hasRole(RoleName::Admin->value))->toBeFalse();

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'authorization.role_removed',
        'actor_id' => $actor->user_id,
        'subject_id' => $subject->user_id,
        'metadata' => json_encode(['role' => 'admin']),
    ]);
});

test('role assignment rolls back when activity capture fails', function () {
    $actor = User::factory()->create();
    $subject = User::factory()->create();
    $recorder = $this->mock(ActivityRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('activity unavailable'));
    $service = app(RoleAssignmentService::class);

    expect(fn () => $service->assign($actor, $subject, RoleName::Admin))
        ->toThrow(RuntimeException::class, 'activity unavailable');

    expect($subject->fresh()->hasRole(RoleName::Admin->value))->toBeFalse();
});

test('authorization schema rollback and recovery preserve account and Planning data', function () {
    $user = User::factory()->create();
    $planning = Planning::factory()->for($user)->create();
    $migration = require app_path('Modules/Authorization/Database/Migrations/2026_08_16_100000_create_authorization_tables.php');

    $migration->down();

    expect(Schema::hasTable('roles'))->toBeFalse()
        ->and(User::query()->whereKey($user->getKey())->exists())->toBeTrue()
        ->and(Planning::query()->whereKey($planning->getKey())->exists())->toBeTrue();

    $migration->up();

    expect($user->fresh()->hasRole(RoleName::User->value))->toBeTrue()
        ->and(Planning::query()->whereKey($planning->getKey())->exists())->toBeTrue();
});

test('authorization rollback refuses to discard non-default assignment state', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::Admin->value);
    $migration = require app_path('Modules/Authorization/Database/Migrations/2026_08_16_100000_create_authorization_tables.php');

    expect(fn () => $migration->down())
        ->toThrow(RuntimeException::class, 'Authorization rollback would discard non-default state.');

    expect(Schema::hasTable('roles'))->toBeTrue()
        ->and($operator->fresh()->hasRole(RoleName::Admin->value))->toBeTrue();
});

test('catalog synchronization is idempotent and preserves unknown data as drift', function () {
    Permission::create(['name' => 'legacy.unknown', 'guard_name' => 'web']);
    Role::create(['name' => 'legacy-role', 'guard_name' => 'web']);
    Permission::findByName('planning.view')->delete();

    $first = app(AuthorizationSynchronizer::class)->sync();
    $second = app(AuthorizationSynchronizer::class)->sync();

    expect($first->createdPermissions)->toBe(['planning.view'])
        ->and($first->unknownPermissions)->toContain('legacy.unknown')
        ->and($first->unknownRoles)->toContain('legacy-role')
        ->and($second->changed())->toBeFalse()
        ->and(Permission::findByName('legacy.unknown'))->not->toBeNull()
        ->and(Role::findByName('legacy-role'))->not->toBeNull();
});

test('the synchronization command records a minimized catalog activity', function () {
    Permission::findByName('planning.create')->delete();

    Artisan::call('authorization:sync');

    expect(Artisan::output())->toContain('Authorization catalog synchronized')
        ->and(DB::table('activity_logs')->where('event', 'authorization.catalog_synchronized')->count())->toBe(1);

    $activity = DB::table('activity_logs')->where('event', 'authorization.catalog_synchronized')->first();
    $metadata = json_decode($activity->metadata, true, flags: JSON_THROW_ON_ERROR);

    expect($activity->actor_id)->toBeNull()
        ->and($activity->subject_type)->toBe('authorization_catalog')
        ->and($activity->subject_id)->toBeNull()
        ->and($metadata)->toBe([
            'created_permissions' => ['planning.create'],
            'created_roles' => [],
            'added_role_permissions' => ['user:planning.create'],
            'drift' => [],
        ]);
});
