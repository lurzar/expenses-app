<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

if (app()->environment('local') || config('telescope.enabled') === true) {
    $retentionHours = filter_var(
        config('telescope.prune_hours', 168),
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]],
    ) ?: 168;

    Schedule::command("telescope:prune --hours={$retentionHours}")
        ->daily()
        ->withoutOverlapping();
}
