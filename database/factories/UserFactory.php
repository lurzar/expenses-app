<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array{
     *     name: string,
     *     email: string,
     *     email_verified_at: Carbon,
     *     password: string,
     *     remember_token: null
     * }
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create the non-privileged verified account used by development seed data.
     */
    public function demoAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Demo Account',
            'email' => 'demo@example.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
    }
}
