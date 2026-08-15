<?php

use App\Modules\Language\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/language/{language}', [LanguageController::class, 'index'])
        ->whereIn('language', ['en', 'my'])
        ->name('language');
});
