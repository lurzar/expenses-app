<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Planning\Models\Planning;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoAccount = User::factory()->demoAccount()->create();
        $users = User::factory(10)->unverified()->create();

        $users->prepend($demoAccount)->each(function (User $user): void {
            Planning::factory()
                ->count(10)
                ->for($user)
                ->sequence(fn (Sequence $sequence): array => [
                    'month' => $sequence->index + 1,
                    'year' => 2026,
                ])
                ->create();
        });
    }
}
