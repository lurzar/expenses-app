<?php

use App\Models\User;
use App\Modules\Planning\Models\Planning;
use App\Modules\Planning\Services\PlanningService;
use Inertia\Testing\AssertableInertia as Assert;

test('owners can view planning through its public id without numeric identifiers', function () {
    $owner = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('planning.show', $planning->planning_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Planning/Show')
            ->where('auth.user.user_id', $owner->user_id)
            ->missing('auth.user.id')
            ->where('planning.planning_id', $planning->planning_id)
            ->missing('planning.id')
            ->missing('planning.user_id'));
});

test('owners can view expenses through the planning public id', function () {
    $owner = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('expenses.show', $planning->planning_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Expenses/Show')
            ->where('planning.planning_id', $planning->planning_id)
            ->missing('planning.id')
            ->missing('planning.user_id'));
});

test('owners can delete their planning through its public id', function () {
    $owner = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->delete(route('planning.destroy', $planning->planning_id))
        ->assertRedirect(route('planning.index'))
        ->assertSessionHas('success', "{$planning->name} plan deleted.");

    expect($planning->fresh()->trashed())->toBeTrue();
});

test('a failed owner deletion returns to the plan with an announced error', function () {
    $owner = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();
    $this->mock(PlanningService::class)
        ->shouldReceive('delete')
        ->once()
        ->andThrow(new RuntimeException('database unavailable'));

    $this->actingAs($owner)
        ->from(route('planning.show', $planning->planning_id))
        ->delete(route('planning.destroy', $planning->planning_id))
        ->assertRedirect(route('planning.show', $planning->planning_id))
        ->assertSessionHas('error', 'The plan could not be deleted. Try again.');

    expect($planning->fresh()->trashed())->toBeFalse();
});

test('planning projections expose only owned records through public identifiers', function (string $routeName, string $component) {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();
    Planning::factory()->for($otherUser)->create();

    $this->actingAs($owner)
        ->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->has('plannings', 1)
            ->where('plannings.0.planning_id', $planning->planning_id)
            ->missing('plannings.0.id')
            ->missing('plannings.0.user_id'));
})->with([
    'Planning' => ['planning.index', 'Planning/Index'],
    'Expenses' => ['expenses.index', 'Expenses/Index'],
    'Dashboard' => ['dashboard', 'Dashboard/Index'],
]);

test('users cannot view another users planning', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->get(route('planning.show', $planning))
        ->assertForbidden();
});

test('users cannot delete another users planning', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->delete(route('planning.destroy', $planning))
        ->assertForbidden();

    expect($planning->fresh()->trashed())->toBeFalse();
});

test('users cannot view another users planning as an expense', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $planning = Planning::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->get(route('expenses.show', $planning))
        ->assertForbidden();
});
