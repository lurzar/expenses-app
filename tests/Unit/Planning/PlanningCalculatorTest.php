<?php

use App\Modules\Planning\Support\PlanningCalculator;

test('calculator normalizes sections and produces the canonical totals contract', function () {
    $result = (new PlanningCalculator)->calculate(
        income: '5000',
        savingRate: '20.00',
        sections: [
            'savings' => [['item' => 'Emergency fund', 'amount' => '800']],
            'commitments' => [['item' => 'Rent', 'amount' => '2000.00']],
            'others' => [['item' => 'Living', 'amount' => '700.0']],
        ],
    );

    expect($result->income)->toBe('5000.00')
        ->and($result->savingRate)->toBe('20.00')
        ->and($result->sections['savings'][0])->toBe([
            'item' => 'Emergency fund',
            'amount' => '800.00',
        ])
        ->and($result->totals)->toBe([
            'target_savings' => '1000.00',
            'savings' => '800.00',
            'commitments' => '2000.00',
            'others' => '700.00',
            'spending' => '2700.00',
            'allocated' => '3500.00',
            'balance' => '1500.00',
        ]);
});

test('calculator rejects allocations above income', function () {
    (new PlanningCalculator)->calculate(
        income: '100.00',
        savingRate: '0',
        sections: [
            'savings' => [['item' => 'Emergency fund', 'amount' => '100.01']],
            'commitments' => [],
            'others' => [],
        ],
    );
})->throws(DomainException::class, 'allocated amount cannot exceed monthly income');
