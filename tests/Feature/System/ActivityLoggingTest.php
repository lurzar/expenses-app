<?php

use App\Models\User;
use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Models\ActivityLog;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Planning\Models\Planning;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery\MockInterface;

test('activity logging has a dedicated persistence boundary', function () {
    expect(Schema::hasTable('activity_logs'))->toBeTrue();
});

test('activity logging stores only approved identifiers and metadata', function () {
    expect(Schema::getColumnListing('activity_logs'))->toBe([
        'id',
        'activity_id',
        'event',
        'actor_id',
        'subject_type',
        'subject_id',
        'metadata',
        'created_at',
    ]);
});

test('creating a planning records one minimized server-side activity', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/planning', [
            'month' => 8,
            'year' => 2026,
            'salary' => '5000.00',
            'saving_rate' => '10',
            'totals' => [
                'savings' => '500.00',
                'commitments' => '1500.00',
                'others' => '500.00',
            ],
            'savings_values' => [],
            'commitments_values' => [],
            'others_values' => [],
        ])
        ->assertRedirect(route('planning.index'));

    $planning = $user->plannings()->firstOrFail();

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'planning.created',
        'actor_id' => $user->user_id,
        'subject_type' => 'planning',
        'subject_id' => $planning->planning_id,
        'metadata' => null,
    ]);

    expect(DB::table('activity_logs')->count())->toBe(1);
});

test('deleting a planning records one minimized server-side activity', function () {
    $user = User::factory()->create();
    $planning = Planning::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('planning.destroy', $planning))
        ->assertRedirect(route('planning.index'));

    $this->assertSoftDeleted($planning);
    $this->assertDatabaseHas('activity_logs', [
        'event' => 'planning.deleted',
        'actor_id' => $user->user_id,
        'subject_type' => 'planning',
        'subject_id' => $planning->planning_id,
        'metadata' => null,
    ]);

    expect(DB::table('activity_logs')->count())->toBe(1);
});

test('registering an account records one minimized server-side activity', function () {
    $this->post('/register', [
        'name' => 'Example Operator',
        'email' => 'operator@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect('/dashboard');

    $user = User::query()->where('email', 'operator@example.test')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('activity_logs', [
        'event' => 'account.registered',
        'actor_id' => $user->user_id,
        'subject_type' => 'account',
        'subject_id' => $user->user_id,
        'metadata' => null,
    ]);

    expect(DB::table('activity_logs')->count())->toBe(1);
});

test('updating a profile records changed field names without values', function () {
    $user = User::factory()->create([
        'name' => 'Before Name',
        'email' => 'before@example.test',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'After Name',
            'email' => 'after@example.test',
        ])
        ->assertRedirect(route('profile.edit'));

    $activity = DB::table('activity_logs')->first();

    expect($activity)->not->toBeNull();

    $metadata = json_decode($activity->metadata, true, flags: JSON_THROW_ON_ERROR);

    expect($activity->event)->toBe('account.profile_updated')
        ->and($activity->actor_id)->toBe($user->user_id)
        ->and($activity->subject_type)->toBe('account')
        ->and($activity->subject_id)->toBe($user->user_id)
        ->and($metadata)->toBe(['changed_fields' => ['name', 'email']])
        ->and($activity->metadata)->not->toContain('Before Name')
        ->and($activity->metadata)->not->toContain('After Name')
        ->and($activity->metadata)->not->toContain('before@example.test')
        ->and($activity->metadata)->not->toContain('after@example.test');
});

test('deleting an account records one account event without cascade duplicates', function () {
    $user = User::factory()->create();
    $planning = Planning::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect('/');

    $this->assertSoftDeleted($user);
    $this->assertSoftDeleted($planning);
    $this->assertDatabaseHas('activity_logs', [
        'event' => 'account.deleted',
        'actor_id' => $user->user_id,
        'subject_type' => 'account',
        'subject_id' => $user->user_id,
        'metadata' => null,
    ]);
    $this->assertDatabaseMissing('activity_logs', [
        'event' => 'planning.deleted',
        'subject_id' => $planning->planning_id,
    ]);

    expect(DB::table('activity_logs')->count())->toBe(1);
});

test('disabled activity logging records no activity', function () {
    config()->set('activity-log.enabled', false);
    $user = User::factory()->create();

    $this->actingAs($user)->post('/planning', [
        'month' => 8,
        'year' => 2026,
        'salary' => '5000.00',
        'saving_rate' => '10',
        'totals' => [],
        'savings_values' => [],
        'commitments_values' => [],
        'others_values' => [],
    ])->assertRedirect(route('planning.index'));

    expect(DB::table('activity_logs')->count())->toBe(0);
});

test('a recorder failure rolls back the owning planning mutation', function () {
    $user = User::factory()->create();
    $this->mock(ActivityRecorder::class, function (MockInterface $mock): void {
        $mock->shouldReceive('record')
            ->once()
            ->andThrow(new RuntimeException('Activity recorder unavailable'));
    });

    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($user)->post('/planning', [
        'month' => 8,
        'year' => 2026,
        'salary' => '5000.00',
        'saving_rate' => '10',
        'totals' => [],
        'savings_values' => [],
        'commitments_values' => [],
        'others_values' => [],
    ]))->toThrow(RuntimeException::class, 'Activity recorder unavailable');

    $this->assertDatabaseCount('plannings', 0);
    $this->assertDatabaseCount('activity_logs', 0);
});

test('activity logging uses the approved retention default', function () {
    expect(config('activity-log.retention_days'))->toBe(365)
        ->and(file_get_contents(base_path('.env.example')))->toContain('ACTIVITY_LOG_RETENTION_DAYS=365');
});

test('activity logging provides a dedicated pruning command', function () {
    expect(Artisan::all())->toHaveKey('activity-log:prune');
});

test('activity pruning deletes expired entries and retains recent entries', function () {
    DB::table('activity_logs')->insert([
        [
            'activity_id' => (string) Str::ulid(),
            'event' => 'planning.created',
            'actor_id' => null,
            'subject_type' => 'planning',
            'subject_id' => (string) Str::ulid(),
            'metadata' => null,
            'created_at' => now()->subDays(366),
        ],
        [
            'activity_id' => (string) Str::ulid(),
            'event' => 'planning.created',
            'actor_id' => null,
            'subject_type' => 'planning',
            'subject_id' => (string) Str::ulid(),
            'metadata' => null,
            'created_at' => now()->subDays(364),
        ],
    ]);

    $this->artisan('activity-log:prune')
        ->expectsOutput('1 activity entry pruned.')
        ->assertSuccessful();

    expect(DB::table('activity_logs')->count())->toBe(1)
        ->and(DB::table('activity_logs')->value('created_at'))->not->toBeNull();
});

test('activity logging schedules daily pruning with approved retention', function () {
    config()->set('activity-log.retention_days', 365);

    require base_path('routes/console.php');

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => Str::contains($event->command ?? '', 'activity-log:prune --days=365'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 0 * * *');
});

test('the recorder rejects metadata outside the approved event contract', function () {
    $recorder = app(ActivityRecorder::class);

    expect(fn () => $recorder->record(
        ActivityEvent::AccountProfileUpdated,
        (string) Str::ulid(),
        'account',
        (string) Str::ulid(),
        [
            'changed_fields' => ['password'],
            'email' => 'sensitive@example.test',
        ],
    ))->toThrow(InvalidArgumentException::class);

    $this->assertDatabaseCount('activity_logs', 0);
});

test('activity records are immutable through the application model', function () {
    $activity = app(ActivityRecorder::class)->record(
        ActivityEvent::PlanningCreated,
        (string) Str::ulid(),
        'planning',
        (string) Str::ulid(),
    );

    expect($activity)->toBeInstanceOf(ActivityLog::class)
        ->and(fn () => $activity->update(['event' => 'planning.deleted']))
        ->toThrow(LogicException::class)
        ->and(fn () => $activity->delete())
        ->toThrow(LogicException::class);

    $this->assertDatabaseCount('activity_logs', 1);
});

test('the recorder rejects an event with the wrong subject type', function () {
    expect(fn () => app(ActivityRecorder::class)->record(
        ActivityEvent::AccountRegistered,
        (string) Str::ulid(),
        'planning',
        (string) Str::ulid(),
    ))->toThrow(InvalidArgumentException::class);

    $this->assertDatabaseCount('activity_logs', 0);
});

test('the recorder rejects personal data in identifier fields', function () {
    expect(fn () => app(ActivityRecorder::class)->record(
        ActivityEvent::AccountRegistered,
        'operator@example.test',
        'account',
        (string) Str::ulid(),
    ))->toThrow(InvalidArgumentException::class);

    $this->assertDatabaseCount('activity_logs', 0);
});

test('failed validation records no activity', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/planning', [
            'year' => '2026',
            'salary' => '5000.00',
            'saving_rate' => '10',
        ])
        ->assertSessionHasErrors('month');

    $this->assertDatabaseCount('plannings', 0);
    $this->assertDatabaseCount('activity_logs', 0);
});

test('an unchanged profile records no activity', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
        ])
        ->assertRedirect(route('profile.edit'));

    $this->assertDatabaseCount('activity_logs', 0);
});

test('malformed enablement fails closed', function () {
    config()->set('activity-log.enabled', 'typo');

    $activity = app(ActivityRecorder::class)->record(
        ActivityEvent::PlanningCreated,
        (string) Str::ulid(),
        'planning',
        (string) Str::ulid(),
    );

    expect($activity)->toBeNull();
    $this->assertDatabaseCount('activity_logs', 0);
});

test('invalid manual retention is rejected without deleting data', function () {
    app(ActivityRecorder::class)->record(
        ActivityEvent::PlanningCreated,
        (string) Str::ulid(),
        'planning',
        (string) Str::ulid(),
    );

    $this->artisan('activity-log:prune', ['--days' => 0])
        ->expectsOutput('The --days option must be a positive integer.')
        ->assertFailed();

    $this->assertDatabaseCount('activity_logs', 1);
});
