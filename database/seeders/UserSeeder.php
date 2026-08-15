<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->superAdmin()->hasPlannings(10)->create();
        
        User::factory(10)->unverified()->hasPlannings(10)->create();
    }
}
