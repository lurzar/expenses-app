<?php

namespace App\Modules\Planning\Database\Factories;

use App\Modules\Planning\Models\Planning;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<Planning>
 */
class PlanningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array{
     *     month: mixed,
     *     year: mixed,
     *     salary: int,
     *     sections: array<string, list<array{item: string, amount: int}>>,
     *     totals: array<string, string>
     * }
     */
    public function definition(): array
    {
        return [
            'month' => Arr::random(getMonthList()->toArray()),
            'year' => Arr::random(getYearList()->toArray()),
            'salary' => fake()->randomNumber(4),
            'sections' => [
                'savings' => [
                    [
                        'item' => Str::random(5),
                        'amount' => fake()->randomNumber(4),
                    ],
                ],
                'commitments' => [
                    [
                        'item' => Str::random(5),
                        'amount' => fake()->randomNumber(4),
                    ],
                ],
                'others' => [
                    [
                        'item' => Str::random(5),
                        'amount' => fake()->randomNumber(4),
                    ],
                ],
            ],
            'totals' => [
                'savings' => (string) fake()->randomNumber(4),
                'commitments' => (string) fake()->randomNumber(4),
                'others' => (string) fake()->randomNumber(4),
            ],
        ];
    }
}
