<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Landing\Controllers\LandingController;

Route::middleware('web')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
});
