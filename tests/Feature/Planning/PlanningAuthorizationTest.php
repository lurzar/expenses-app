<?php

use App\Models\User;
use App\Modules\Planning\Models\Planning;

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
