<?php

use App\Models\User;
use App\Modules\Planning\Models\Planning;
use Illuminate\Database\QueryException;
use Inertia\Testing\AssertableInertia as Assert;

function validPlanningPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'month' => 8,
        'year' => 2026,
        'salary' => '5000.00',
        'saving_rate' => '20.00',
        'savings_values' => [['item' => 'Emergency fund', 'amount' => '800.00']],
        'commitments_values' => [['item' => 'Rent', 'amount' => '2000.00']],
        'others_values' => [['item' => 'Living', 'amount' => '700.00']],
        'totals' => [
            'target_savings' => '999999.99',
            'balance' => '999999.99',
        ],
    ], $overrides);
}

test('planning totals are calculated by the server and client totals are ignored', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('planning.store'), validPlanningPayload())
        ->assertRedirect(route('planning.index'));

    $planning = Planning::query()->sole();

    expect($planning->salary)->toBe('5000.00')
        ->and($planning->saving_rate)->toBe('20.00')
        ->and($planning->sections?->all())->toBe([
            'savings' => [['item' => 'Emergency fund', 'amount' => '800.00']],
            'commitments' => [['item' => 'Rent', 'amount' => '2000.00']],
            'others' => [['item' => 'Living', 'amount' => '700.00']],
        ])
        ->and($planning->totals?->all())->toBe([
            'target_savings' => '1000.00',
            'savings' => '800.00',
            'commitments' => '2000.00',
            'others' => '700.00',
            'spending' => '2700.00',
            'allocated' => '3500.00',
            'balance' => '1500.00',
        ]);
});

test('planning money and period boundaries are validated', function (array $override, string $errorKey) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('planning.create'))
        ->post(route('planning.store'), validPlanningPayload($override))
        ->assertRedirect(route('planning.create'))
        ->assertSessionHasErrors($errorKey);

    expect(Planning::query()->count())->toBe(0);
})->with([
    'negative income' => [['salary' => '-1'], 'salary'],
    'income precision' => [['salary' => '1.001'], 'salary'],
    'scientific income' => [['salary' => '1e3'], 'salary'],
    'saving rate range' => [['saving_rate' => '100.01'], 'saving_rate'],
    'saving rate precision' => [['saving_rate' => '20.001'], 'saving_rate'],
    'invalid month' => [['month' => 13], 'month'],
    'invalid year' => [['year' => 1999], 'year'],
    'negative item' => [['savings_values' => [['item' => 'Bad', 'amount' => '-0.01']]], 'savings_values.0.amount'],
    'excess allocation' => [['savings_values' => [['item' => 'Too much', 'amount' => '5000.01']]], 'calculation'],
]);

test('one active plan is allowed per owner and period while a soft deleted period can be recreated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('planning.store'), validPlanningPayload());

    $this->actingAs($user)
        ->post(route('planning.store'), validPlanningPayload(['salary' => '6000.00']))
        ->assertSessionHasErrors('month');

    $first = Planning::query()->sole();
    $first->delete();

    $this->actingAs($user)
        ->post(route('planning.store'), validPlanningPayload(['salary' => '6000.00']))
        ->assertRedirect(route('planning.index'));

    expect(Planning::withTrashed()->count())->toBe(2);
});

test('the database enforces active owner period uniqueness atomically', function () {
    $user = User::factory()->create();
    $first = Planning::factory()->for($user)->create();

    expect(fn () => Planning::factory()->for($user)->create())
        ->toThrow(QueryException::class);

    $first->delete();

    expect(Planning::factory()->for($user)->create())->toBeInstanceOf(Planning::class);
});

test('planning projections expose one canonical exact money payload', function (string $routeName, string $component, string $path) {
    $user = User::factory()->create();
    $planning = Planning::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route($routeName, $path === 'planning' ? $planning->planning_id : []))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where($path.'.salary', '5000.00')
            ->where($path.'.saving_rate', '20.00')
            ->where($path.'.totals.balance', '1500.00')
            ->missing($path.'.id')
            ->missing($path.'.user_id'));
})->with([
    'Planning detail' => ['planning.show', 'Planning/Show', 'planning'],
    'Expenses detail' => ['expenses.show', 'Expenses/Show', 'planning'],
    'Dashboard collection' => ['dashboard', 'Dashboard/Index', 'plannings.0'],
]);
