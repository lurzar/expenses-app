<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->superAdmin()->hasPlannings(10, function (array $attributes, User $user) {
            return ['user_id' => $user->id];
        })->create();
        
        User::factory(10)->unverified()->hasPlannings(10, function (array $attributes, User $user) {
            return ['user_id' => $user->id];
        })->create();
    }
}
