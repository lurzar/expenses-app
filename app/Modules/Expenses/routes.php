<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Expenses\Controllers\ExpensesController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/{expenses}', [ExpensesController::class, 'show'])->name('expenses.show');
});
