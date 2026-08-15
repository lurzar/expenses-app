<?php

use App\Models\User;
use App\Modules\Planning\Models\Planning;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('legacy planning rows normalize deterministically and the schema rollback remains available', function () {
    $migration = require base_path('app/Modules/Planning/Database/Migrations/2026_08_16_000000_enforce_planning_money_integrity.php');
    $migration->down();

    $user = User::factory()->create();
    $planningId = (string) Str::ulid();

    DB::table('plannings')->insert([
        'planning_id' => $planningId,
        'user_id' => $user->getKey(),
        'month' => 'August',
        'year' => '2026',
        'salary' => 5000.0,
        'sections' => json_encode([
            'savings' => [['item' => ' Emergency fund ', 'amount' => 800]],
            'commitments' => [['item' => 'Rent', 'amount' => '2000']],
            'others' => [['item' => 'Living', 'amount' => 700.0]],
        ], JSON_THROW_ON_ERROR),
        'totals' => json_encode([
            'saving' => 1000,
            'balance' => 1500,
            'commitment' => 2000,
            'other' => 700,
        ], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
        'deleted_at' => null,
    ]);

    $migration->up();

    $planning = Planning::query()->where('planning_id', $planningId)->firstOrFail();

    expect($planning->month)->toBe(8)
        ->and($planning->year)->toBe(2026)
        ->and($planning->salary)->toBe('5000.00')
        ->and($planning->saving_rate)->toBe('20.00')
        ->and($planning->sections?->get('savings'))->toBe([
            ['item' => 'Emergency fund', 'amount' => '800.00'],
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

    $migration->down();

    $legacy = DB::table('plannings')->where('planning_id', $planningId)->first();

    expect($legacy)->not->toBeNull()
        ->and($legacy->month)->toBe('August')
        ->and($legacy->year)->toBe('2026')
        ->and(Schema::hasColumn('plannings', 'saving_rate'))->toBeFalse();

    $migration->up();
});
