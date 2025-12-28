<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Planning\Controllers\PlanningController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('planning', PlanningController::class)->except(['update', 'edit']);
});
