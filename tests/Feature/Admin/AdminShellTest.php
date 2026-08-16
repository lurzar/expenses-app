<?php

use App\Models\User;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\AuthorizationSession;
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
