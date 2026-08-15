<?php

use App\Modules\Landing\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
});
