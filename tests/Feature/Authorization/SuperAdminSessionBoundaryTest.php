<?php

use App\Models\User;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\Exceptions\SuperAdminLifecycleException;
use App\Modules\Authorization\RoleName;

test('an unverified super-admin loses retained sessions across deletion and restoration', function () {
    $operator = User::factory()->unverified()->create(['remember_token' => 'known-remember-token']);
    $otherOperator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $otherOperator->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($operator)
        ->withSession(['auth.authorization_version' => $operator->authorization_version])
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect('/');

    $deletedOperator = User::withTrashed()->findOrFail($operator->getKey());

    expect($deletedOperator->authorization_version)->toBe(1)
        ->and($deletedOperator->remember_token)->not->toBe('known-remember-token');

    $deletedOperator->restore();

    $this->actingAs($deletedOperator)
        ->withSession(['auth.authorization_version' => 0])
        ->get(route('profile.edit'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('direct deletion cannot bypass the audited super-admin lifecycle', function () {
    $operator = User::factory()->create();
    $otherOperator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $otherOperator->assignRole(RoleName::SuperAdmin->value);

    expect(fn () => $operator->delete())
        ->toThrow(
            SuperAdminLifecycleException::class,
            'Protected super-admin status changes must use the audited lifecycle.',
        );

    expect($operator->fresh()->trashed())->toBeFalse()
        ->and($operator->fresh()->authorization_version)->toBe(0);
});

test('direct unverification cannot bypass the audited super-admin lifecycle', function () {
    $operator = User::factory()->create();
    $otherOperator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $otherOperator->assignRole(RoleName::SuperAdmin->value);
    $operator->email_verified_at = null;

    expect(fn () => $operator->save())
        ->toThrow(
            SuperAdminLifecycleException::class,
            'Protected super-admin status changes must use the audited lifecycle.',
        );

    expect($operator->fresh()->email_verified_at)->not->toBeNull()
        ->and($operator->fresh()->authorization_version)->toBe(0);
});

test('a super-admin cannot become unverified when activity capture is disabled', function () {
    $operator = User::factory()->create(['email' => 'before@example.test']);
    $otherOperator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $otherOperator->assignRole(RoleName::SuperAdmin->value);
    config()->set('activity-log.enabled', false);

    $this->actingAs($operator)
        ->withSession(['auth.authorization_version' => $operator->authorization_version])
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => $operator->name,
            'email' => 'after@example.test',
        ])
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('profile.edit'));

    expect($operator->fresh()->email)->toBe('before@example.test')
        ->and($operator->fresh()->email_verified_at)->not->toBeNull()
        ->and($operator->fresh()->authorization_version)->toBe(0);
});

test('a super-admin cannot be deleted when activity capture is disabled', function () {
    $operator = User::factory()->create();
    $otherOperator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $otherOperator->assignRole(RoleName::SuperAdmin->value);
    config()->set('activity-log.enabled', false);

    $this->actingAs($operator)
        ->withSession(['auth.authorization_version' => $operator->authorization_version])
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertSessionHasErrorsIn('userDeletion', 'account')
        ->assertRedirect(route('profile.edit'));

    expect($operator->fresh())->not->toBeNull()
        ->and($operator->fresh()->trashed())->toBeFalse()
        ->and($operator->fresh()->authorization_version)->toBe(0);
});

test('failed activity capture rolls back super-admin unverification and session revocation', function () {
    $operator = User::factory()->create([
        'email' => 'before@example.test',
        'remember_token' => 'known-remember-token',
    ]);
    $otherOperator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $otherOperator->assignRole(RoleName::SuperAdmin->value);

    $recorder = $this->mock(ActivityRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('activity unavailable'));
    $this->withoutExceptionHandling();

    $this->actingAs($operator)
        ->withSession(['auth.authorization_version' => $operator->authorization_version]);

    expect(fn () => $this->patch(route('profile.update'), [
        'name' => $operator->name,
        'email' => 'after@example.test',
    ]))->toThrow(RuntimeException::class, 'activity unavailable');

    $operator->refresh();

    expect($operator->email)->toBe('before@example.test')
        ->and($operator->email_verified_at)->not->toBeNull()
        ->and($operator->authorization_version)->toBe(0)
        ->and($operator->remember_token)->toBe('known-remember-token');
});

test('login binds the session to the current authorization version', function () {
    $operator = User::factory()->create(['authorization_version' => 7]);
    $operator->assignRole(RoleName::SuperAdmin->value);

    $this->post('/login', [
        'email' => $operator->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard')
        ->assertSessionHas('auth.authorization_version', 7);

    $this->assertAuthenticatedAs($operator);
});

test('a stale privileged session is logged out before protected content is returned', function (?int $sessionVersion) {
    $operator = User::factory()->create(['authorization_version' => 2]);
    $operator->assignRole(RoleName::SuperAdmin->value);

    $request = $this->actingAs($operator);

    if ($sessionVersion !== null) {
        $request->withSession(['auth.authorization_version' => $sessionVersion]);
    }

    $request->get(route('dashboard'))->assertRedirect(route('login'));

    $this->assertGuest();
})->with([
    'mismatched version' => 1,
    'pre-lifecycle session without a version' => null,
]);

test('an existing ordinary session adopts the initial authorization version without disruption', function () {
    $user = User::factory()->create(['authorization_version' => 0]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionHas('auth.authorization_version', 0);

    $this->assertAuthenticatedAs($user);
});

test('a versionless ordinary session is rejected after a privileged lifecycle change', function () {
    $user = User::factory()->create(['authorization_version' => 1]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
