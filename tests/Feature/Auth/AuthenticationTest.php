<?php

use App\Models\User;
use App\Modules\Auth\Middleware\RedirectIfAuthenticated;
use Inertia\Testing\AssertableInertia as Assert;

test('login screen includes the latest public changelog entry', function () {
    config()->set('changelog.releases', [
        [
            'version' => '2.0.6',
            'status' => 'In development',
            'released_at' => null,
            'changes' => [
                [
                    'category' => 'Added',
                    'description' => 'Added a public changes summary to the login page.',
                ],
            ],
        ],
        [
            'version' => '2.0.5',
            'status' => 'Released',
            'released_at' => '2025-12-31',
            'changes' => [],
        ],
    ]);

    $response = $this->get('/login');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Auth/Login')
        ->where('changelog.version', '2.0.6')
        ->where('changelog.status', 'In development')
        ->where('changelog.released_at', null)
        ->where('changelog.changes.0.category', 'Added')
        ->where('changelog.changes.0.description', 'Added a public changes summary to the login page.')
        ->missing('changelog.1'));
});

test('login screen handles an empty changelog', function () {
    config()->set('changelog.releases', []);

    $response = $this->get('/login');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Auth/Login')
        ->where('changelog', null));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RedirectIfAuthenticated::HOME);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
