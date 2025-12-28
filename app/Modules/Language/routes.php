<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Language\Controllers\LanguageController;

Route::middleware('web')->group(function () {
    Route::get('/language/{lang?}', [LanguageController::class, 'index'])->name('language');
});
