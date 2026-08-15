<?php

namespace App\Modules\Planning\Database\Factories;

use App\Modules\Planning\Models\Planning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Planning>
 */
class PlanningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array{
     *     month: int,
     *     year: int,
     *     salary: string,
     *     saving_rate: string,
     *     sections: array<string, list<array{item: string, amount: string}>>,
     *     totals: array<string, string>
     * }
     */
    public function definition(): array
    {
        return [
            'month' => 8,
            'year' => 2026,
            'salary' => '5000.00',
            'saving_rate' => '20.00',
            'sections' => [
                'savings' => [
                    [
                        'item' => 'Emergency fund',
                        'amount' => '800.00',
                    ],
                ],
                'commitments' => [
                    [
                        'item' => 'Rent',
                        'amount' => '2000.00',
                    ],
                ],
                'others' => [
                    [
                        'item' => 'Living',
                        'amount' => '700.00',
                    ],
                ],
            ],
            'totals' => [
                'target_savings' => '1000.00',
                'savings' => '800.00',
                'commitments' => '2000.00',
                'others' => '700.00',
                'spending' => '2700.00',
                'allocated' => '3500.00',
                'balance' => '1500.00',
            ],
        ];
    }
}
