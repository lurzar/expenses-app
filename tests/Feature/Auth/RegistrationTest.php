<?php

use App\Modules\Auth\Middleware\RedirectIfAuthenticated;
use Inertia\Testing\AssertableInertia as Assert;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Auth/Register'));
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RedirectIfAuthenticated::HOME);
});
