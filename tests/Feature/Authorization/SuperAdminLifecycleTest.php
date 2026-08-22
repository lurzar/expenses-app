<?php

use App\Models\User;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\Exceptions\SuperAdminLifecycleException;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\RoleAssignmentService;
use App\Modules\Authorization\Services\SuperAdminLifecycleService;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

test('the console workflow grants super-admin to one verified active account and revokes existing sessions', function () {
    $target = User::factory()->create(['remember_token' => 'known-remember-token']);

    $this->artisan('authorization:super-admin', [
        'user' => $target->user_id,
        '--force' => true,
    ])->expectsOutput("Super-admin access granted for account {$target->user_id}.")
        ->assertSuccessful();

    $target->refresh();

    expect($target->hasRole(RoleName::SuperAdmin->value))->toBeTrue()
        ->and($target->hasRole(RoleName::User->value))->toBeTrue()
        ->and($target->authorization_version)->toBe(1)
        ->and($target->remember_token)->not->toBe('known-remember-token')
        ->and(Artisan::output())->not->toContain($target->email);

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'authorization.super_admin_granted',
        'actor_id' => null,
        'subject_type' => 'account',
        'subject_id' => $target->user_id,
        'metadata' => null,
    ]);
});

test('repeated provisioning is idempotent', function () {
    $target = User::factory()->create();

    $this->artisan('authorization:super-admin', [
        'user' => $target->user_id,
        '--force' => true,
    ])->assertSuccessful();

    $this->artisan('authorization:super-admin', [
        'user' => $target->user_id,
        '--force' => true,
    ])->expectsOutput("Account {$target->user_id} already has super-admin access.")
        ->assertSuccessful();

    expect($target->fresh()->authorization_version)->toBe(1)
        ->and(DB::table('activity_logs')->where('event', 'authorization.super_admin_granted')->count())->toBe(1);
});

test('provisioning rejects unverified and soft-deleted accounts without revealing account data', function (string $state) {
    $target = $state === 'unverified'
        ? User::factory()->unverified()->create()
        : User::factory()->create();

    if ($state === 'deleted') {
        $target->delete();
    }

    $this->artisan('authorization:super-admin', [
        'user' => $target->user_id,
        '--force' => true,
    ])->expectsOutput('The target account is not eligible for super-admin access.')
        ->assertFailed();

    expect($target->fresh()?->hasRole(RoleName::SuperAdmin->value))->not->toBeTrue()
        ->and(Artisan::output())->not->toContain($target->email);
})->with(['unverified', 'deleted']);

test('provisioning requires activity capture', function () {
    config()->set('activity-log.enabled', false);
    $target = User::factory()->create();

    $this->artisan('authorization:super-admin', [
        'user' => $target->user_id,
        '--force' => true,
    ])->expectsOutput('Super-admin changes require activity logging.')
        ->assertFailed();

    expect($target->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeFalse();
});

test('interactive provisioning must be explicitly confirmed', function () {
    $target = User::factory()->create();

    $this->artisan('authorization:super-admin', ['user' => $target->user_id])
        ->expectsConfirmation("Grant super-admin access to account {$target->user_id}?", 'no')
        ->expectsOutput('Super-admin access was not changed.')
        ->assertFailed();

    expect($target->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeFalse();
});

test('rotation grants the replacement before removing the previous super-admin and revokes both sessions', function () {
    $previous = User::factory()->create(['remember_token' => 'previous-token']);
    $replacement = User::factory()->create(['remember_token' => 'replacement-token']);
    $previous->assignRole(RoleName::SuperAdmin->value);

    $this->artisan('authorization:super-admin', [
        'user' => $replacement->user_id,
        '--replace' => $previous->user_id,
        '--force' => true,
    ])->expectsOutput("Super-admin access rotated to account {$replacement->user_id}.")
        ->assertSuccessful();

    $previous->refresh();
    $replacement->refresh();

    expect($previous->hasRole(RoleName::SuperAdmin->value))->toBeFalse()
        ->and($replacement->hasRole(RoleName::SuperAdmin->value))->toBeTrue()
        ->and($previous->authorization_version)->toBe(1)
        ->and($replacement->authorization_version)->toBe(1)
        ->and($previous->remember_token)->not->toBe('previous-token')
        ->and($replacement->remember_token)->not->toBe('replacement-token')
        ->and(Artisan::output())->not->toContain($previous->email)
        ->and(Artisan::output())->not->toContain($replacement->email);

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'authorization.super_admin_rotated',
        'actor_id' => null,
        'subject_type' => 'account',
        'subject_id' => $replacement->user_id,
        'metadata' => json_encode(['previous_account_id' => $previous->user_id]),
    ]);
});

test('rotation rolls back assignments session state and activity together', function () {
    $previous = User::factory()->create(['remember_token' => 'previous-token']);
    $replacement = User::factory()->create(['remember_token' => 'replacement-token']);
    $previous->assignRole(RoleName::SuperAdmin->value);

    $recorder = $this->mock(ActivityRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('activity unavailable'));

    expect(fn () => app(SuperAdminLifecycleService::class)->rotate($previous, $replacement))
        ->toThrow(RuntimeException::class, 'activity unavailable');

    expect($previous->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeTrue()
        ->and($replacement->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeFalse()
        ->and($previous->fresh()->authorization_version)->toBe(0)
        ->and($replacement->fresh()->authorization_version)->toBe(0)
        ->and($previous->fresh()->remember_token)->toBe('previous-token')
        ->and($replacement->fresh()->remember_token)->toBe('replacement-token');

    $this->assertDatabaseCount('activity_logs', 0);
});

test('the final active super-admin cannot lose the protected role', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);

    expect(fn () => app(RoleAssignmentService::class)->remove(
        $operator,
        $operator,
        RoleName::SuperAdmin,
    ))->toThrow(SuperAdminLifecycleException::class, 'The final active super-admin cannot be removed.');

    expect($operator->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeTrue()
        ->and($operator->fresh()->authorization_version)->toBe(0);
});

test('a super-admin can be removed when another active operator remains', function () {
    $actor = User::factory()->create();
    $subject = User::factory()->create(['remember_token' => 'subject-token']);
    $actor->assignRole(RoleName::SuperAdmin->value);
    $subject->assignRole(RoleName::SuperAdmin->value);

    expect(app(RoleAssignmentService::class)->remove(
        $actor,
        $subject,
        RoleName::SuperAdmin,
    ))->toBeTrue();

    expect($subject->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeFalse()
        ->and($subject->fresh()->authorization_version)->toBe(1)
        ->and($subject->fresh()->remember_token)->not->toBe('subject-token');

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'authorization.super_admin_removed',
        'actor_id' => $actor->user_id,
        'subject_type' => 'account',
        'subject_id' => $subject->user_id,
        'metadata' => null,
    ]);
});

test('the final active super-admin cannot delete their account', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($operator)
        ->withSession(['auth.authorization_version' => $operator->authorization_version]);

    expect(session('auth.authorization_version'))->toBe(0);

    $this->from(route('profile.edit'))
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertSessionHasErrorsIn('userDeletion', 'account')
        ->assertRedirect(route('profile.edit'));

    expect($operator->fresh())->not->toBeNull()
        ->and($operator->fresh()->trashed())->toBeFalse();
});

test('the final active super-admin cannot become unverified through an email change', function () {
    $operator = User::factory()->create(['email' => 'before@example.test']);
    $operator->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($operator)
        ->withSession(['auth.authorization_version' => $operator->authorization_version]);

    $this->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => $operator->name,
            'email' => 'after@example.test',
        ])
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('profile.edit'));

    expect($operator->fresh()->email)->toBe('before@example.test')
        ->and($operator->fresh()->email_verified_at)->not->toBeNull();
});

test('a non-final super-admin loses existing sessions when becoming unverified', function () {
    $operator = User::factory()->create([
        'email' => 'before@example.test',
        'remember_token' => 'known-remember-token',
    ]);
    $otherOperator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $otherOperator->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($operator)
        ->withSession(['auth.authorization_version' => $operator->authorization_version])
        ->patch(route('profile.update'), [
            'name' => $operator->name,
            'email' => 'after@example.test',
        ])
        ->assertRedirect(route('profile.edit'));

    $operator->refresh();

    expect($operator->email)->toBe('after@example.test')
        ->and($operator->email_verified_at)->toBeNull()
        ->and($operator->authorization_version)->toBe(1)
        ->and($operator->remember_token)->not->toBe('known-remember-token');

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'account.profile_updated',
        'actor_id' => $operator->user_id,
        'subject_type' => 'account',
        'subject_id' => $operator->user_id,
        'metadata' => json_encode(['changed_fields' => ['email']]),
    ]);

    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a non-final super-admin loses retained sessions across deletion and restoration', function () {
    $operator = User::factory()->create(['remember_token' => 'known-remember-token']);
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

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'account.deleted',
        'actor_id' => $operator->user_id,
        'subject_type' => 'account',
        'subject_id' => $operator->user_id,
        'metadata' => null,
    ]);

    $deletedOperator->restore();

    $this->actingAs($deletedOperator)
        ->withSession(['auth.authorization_version' => 0])
        ->get(route('profile.edit'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('development seed data never creates a privileged account or default super-admin credential', function () {
    $this->seed(UserSeeder::class);

    expect(User::query()->where('email', 'superadmin@app.com')->exists())->toBeFalse()
        ->and(User::role(RoleName::SuperAdmin->value)->exists())->toBeFalse()
        ->and(User::query()->where('email', 'demo@example.test')->exists())->toBeTrue();
});

test('authorization session migration rolls back without deleting users or assignments', function () {
    $operator = User::factory()->create();
    $operator->assignRole(RoleName::SuperAdmin->value);
    $migration = require app_path('Modules/Authorization/Database/Migrations/2026_08_16_110000_add_authorization_version_to_users.php');

    $migration->down();

    expect(Schema::hasColumn('users', 'authorization_version'))->toBeFalse()
        ->and(User::query()->whereKey($operator->getKey())->exists())->toBeTrue()
        ->and($operator->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeTrue();

    $migration->up();

    expect(Schema::hasColumn('users', 'authorization_version'))->toBeTrue()
        ->and($operator->fresh()->authorization_version)->toBe(0);
});

test('provisioning fails closed when the protected authorization catalog is unavailable', function () {
    $target = User::factory()->create();
    Role::findByName(RoleName::SuperAdmin->value)->delete();

    $this->artisan('authorization:super-admin', [
        'user' => $target->user_id,
        '--force' => true,
    ])->expectsOutput('The authorization catalog is not synchronized.')
        ->assertFailed();

    expect($target->fresh()->hasRole(RoleName::SuperAdmin->value))->toBeFalse();
});
