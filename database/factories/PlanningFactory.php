<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Planning>
 */
class PlanningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
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
                    ]
                ],
                'commitments' => [
                    [
                    'item' => Str::random(5),
                    'amount' => fake()->randomNumber(4),
                    ]
                ],
                'others' => [
                    [
                    'item' => Str::random(5),
                    'amount' => fake()->randomNumber(4),
                    ]
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
