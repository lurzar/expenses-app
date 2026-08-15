<?php

use App\Models\User;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Services\PlanningCache;
use App\Modules\Planning\Services\PlanningService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;

beforeEach(function (): void {
    Cache::flush();
});

test('planning collection cache uses the fixed-decimal payload namespace', function () {
    $key = app(PlanningCache::class)->indexKey(42);

    expect($key)->toBe('planning:index:v2:user:42')
        ->and($key)->not->toContain('planning:index:v1:');
});

test('repeated planning collection reads use the cached result', function () {
    $user = User::factory()->create();
    $original = Planning::factory()->for($user)->create();

    $this->actingAs($user);

    $firstRead = app(PlanningService::class)->getAllPlannings();
    Planning::factory()->for($user)->create(['month' => 9]);
    $secondRead = app(PlanningService::class)->getAllPlannings();

    expect($firstRead->modelKeys())->toBe([$original->getKey()])
        ->and($secondRead->modelKeys())->toBe([$original->getKey()]);
});

test('planning collection cache entries are isolated by authenticated user', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $firstPlanning = Planning::factory()->for($firstUser)->create();
    $secondPlanning = Planning::factory()->for($secondUser)->create();

    $this->actingAs($firstUser);
    $firstResult = app(PlanningService::class)->getAllPlannings();

    $this->actingAs($secondUser);
    $secondResult = app(PlanningService::class)->getAllPlannings();

    expect($firstResult->modelKeys())->toBe([$firstPlanning->getKey()])
        ->and($secondResult->modelKeys())->toBe([$secondPlanning->getKey()]);
});

test('empty planning collections are cached', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect(app(PlanningService::class)->getAllPlannings())->toBeEmpty();

    Planning::factory()->for($user)->create();

    expect(app(PlanningService::class)->getAllPlannings())->toBeEmpty();
});

test('planning collection cache expires after five minutes', function () {
    $user = User::factory()->create();
    $original = Planning::factory()->for($user)->create();

    $this->actingAs($user);

    expect(app(PlanningService::class)->getAllPlannings()->modelKeys())
        ->toBe([$original->getKey()]);

    $newPlanning = Planning::factory()->for($user)->create(['month' => 9]);

    expect(app(PlanningService::class)->getAllPlannings()->modelKeys())
        ->toBe([$original->getKey()]);

    $this->travel(301)->seconds();

    expect(app(PlanningService::class)->getAllPlannings()->modelKeys())
        ->toContain($original->getKey(), $newPlanning->getKey());
});

test('creating a planning invalidates the authenticated user collection', function () {
    $user = User::factory()->create();
    $original = Planning::factory()->for($user)->create();

    $this->actingAs($user);

    expect(app(PlanningService::class)->getAllPlannings()->modelKeys())
        ->toBe([$original->getKey()]);

    $this->post(route('planning.store'), validPlanningCachePayload())
        ->assertRedirect(route('planning.index'));

    expect(app(PlanningService::class)->getAllPlannings())
        ->toHaveCount(2);
});

test('deleting a planning invalidates the authenticated user collection', function () {
    $user = User::factory()->create();
    $planning = Planning::factory()->for($user)->create();

    $this->actingAs($user);

    expect(app(PlanningService::class)->getAllPlannings()->modelKeys())
        ->toBe([$planning->getKey()]);

    $this->delete(route('planning.destroy', $planning))
        ->assertRedirect(route('planning.index'));

    expect(app(PlanningService::class)->getAllPlannings())
        ->toBeEmpty();
});

test('planning mutations retain other users collection entries', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    Planning::factory()->for($firstUser)->create();
    Planning::factory()->for($secondUser)->create();

    $cache = app(PlanningCache::class);

    $this->actingAs($firstUser);
    app(PlanningService::class)->getAllPlannings();

    $this->actingAs($secondUser);
    app(PlanningService::class)->getAllPlannings();

    $this->actingAs($firstUser)
        ->post(route('planning.store'), validPlanningCachePayload())
        ->assertRedirect(route('planning.index'));

    expect(Cache::has($cache->indexKey($firstUser->getKey())))->toBeFalse()
        ->and(Cache::has($cache->indexKey($secondUser->getKey())))->toBeTrue();
});

test('deleting an account invalidates its planning collection', function () {
    $user = User::factory()->create();
    Planning::factory()->for($user)->create();

    $this->actingAs($user);
    app(PlanningService::class)->getAllPlannings();

    $key = app(PlanningCache::class)->indexKey($user->getKey());

    expect(Cache::has($key))->toBeTrue();

    $this->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect('/');

    expect(Cache::has($key))->toBeFalse();
});

test('restoring an account invalidates its planning collection', function () {
    $user = User::factory()->create();
    $key = app(PlanningCache::class)->indexKey($user->getKey());

    $user->delete();
    Cache::put($key, collect(['stale']), 300);

    $user->restore();

    expect(Cache::has($key))->toBeFalse();
});

test('a rolled back planning mutation retains the existing cache entry', function () {
    $user = User::factory()->create();
    $planning = Planning::factory()->for($user)->create();
    $cache = app(PlanningCache::class);
    $key = $cache->indexKey($user->getKey());

    Cache::put($key, new EloquentCollection([$planning]), 300);

    $this->mock(ActivityRecorder::class, function (MockInterface $mock): void {
        $mock->shouldReceive('record')
            ->once()
            ->andThrow(new RuntimeException('Activity recorder unavailable'));
    });

    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($user)->post(
        route('planning.store'),
        validPlanningCachePayload(),
    ))->toThrow(RuntimeException::class, 'Activity recorder unavailable');

    expect(Cache::has($key))->toBeTrue()
        ->and(Cache::get($key)->modelKeys())->toBe([$planning->getKey()]);

    $this->assertDatabaseCount('plannings', 1);
});

/**
 * @return array<string, mixed>
 */
function validPlanningCachePayload(): array
{
    return [
        'month' => 9,
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
    ];
}
