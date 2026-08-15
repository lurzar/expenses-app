<?php

use App\Models\User;
use App\Modules\Planning\Models\Planning;
use Database\Seeders\UserSeeder;

test('the supported user seeder creates distinct planning periods', function () {
    $this->seed(UserSeeder::class);

    expect(User::query()->count())->toBe(11)
        ->and(Planning::query()->count())->toBe(110)
        ->and(
            Planning::query()
                ->selectRaw('user_id, month, year, COUNT(*) as period_count')
                ->groupBy('user_id', 'month', 'year')
                ->havingRaw('COUNT(*) > 1')
                ->exists(),
        )->toBeFalse();
});
