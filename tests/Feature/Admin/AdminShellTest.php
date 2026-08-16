<?php

use App\Models\User;
use App\Modules\Admin\Navigation\AdminNavigationItem;
use App\Modules\Admin\Navigation\AdminNavigationRegistry;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\AuthorizationSession;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

test('guests are redirected to login from the Admin control plane', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('unverified administrators are redirected to email verification', function () {
    $operator = User::factory()->unverified()->create();
    $operator->assignRole(RoleName::Admin->value);

    $this->actingAs($operator)
        ->get('/admin')
        ->assertRedirect(route('verification.notice'));
});

test('ordinary users are denied from the Admin control plane', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

test('administrators receive the minimal localized Admin shell contract', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::Admin->value);

    $this->actingAs($operator)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Index')
            ->where('auth.capabilities.access_admin', true)
            ->has('admin.navigation', 1)
            ->where('admin.navigation.0.key', 'overview')
            ->where('admin.navigation.0.label', 'Overview')
            ->where('admin.navigation.0.description', 'Admin control plane status')
            ->where('admin.navigation.0.href', route('admin.index'))
            ->missing('admin.roles')
            ->missing('admin.permissions')
            ->missing('admin.user')
            ->missing('plannings'));
});

test('the Admin shell localizes server-owned navigation metadata', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::Admin->value);

    $this->actingAs($operator)
        ->withSession(['locale' => 'my'])
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'my')
            ->where('admin.navigation.0.label', 'Gambaran keseluruhan')
            ->where('admin.navigation.0.description', 'Status panel kawalan pentadbir'));
});

test('super administrators can enter with a current authorization session', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($operator)
        ->withSession([AuthorizationSession::KEY => $operator->authorization_version])
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Index')
            ->where('auth.capabilities.access_admin', true));
});

test('the Admin request boundary fails closed when its declared permission is missing', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::Admin->value);
    Permission::findByName('admin.access')->delete();

    $this->actingAs($operator)
        ->get('/admin')
        ->assertForbidden();
});

test('the Admin navigation registry returns only items allowed by Laravel abilities', function () {
    $operator = User::factory()->create();
    Gate::define('admin.registry.allowed', fn (User $user): bool => $user->is($operator));
    Gate::define('admin.registry.denied', fn (): bool => false);

    $registry = new AdminNavigationRegistry;
    $registry->register(new AdminNavigationItem(
        key: 'allowed',
        labelKey: 'admin.overview',
        descriptionKey: 'admin.overview_description',
        routeName: 'admin.index',
        ability: 'admin.registry.allowed',
    ));
    $registry->register(new AdminNavigationItem(
        key: 'denied',
        labelKey: 'admin.overview',
        descriptionKey: 'admin.overview_description',
        routeName: 'admin.index',
        ability: 'admin.registry.denied',
    ));

    expect($registry->availableTo($operator))->toBe([
        [
            'key' => 'allowed',
            'label' => 'Overview',
            'description' => 'Admin control plane status',
            'href' => route('admin.index'),
        ],
    ]);
});

test('the Admin navigation registry rejects duplicate extension keys', function () {
    $registry = new AdminNavigationRegistry;
    $item = new AdminNavigationItem(
        key: 'overview',
        labelKey: 'admin.overview',
        descriptionKey: 'admin.overview_description',
        routeName: 'admin.index',
        ability: 'admin.access',
    );
    $registry->register($item);

    expect(fn () => $registry->register($item))
        ->toThrow(InvalidArgumentException::class, 'Admin navigation item [overview] is already registered.');
});
