<?php

use App\Modules\Planning\Controllers\PlanningController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('planning', PlanningController::class)->except(['update', 'edit']);
});
