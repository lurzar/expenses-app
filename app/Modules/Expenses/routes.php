<?php

use App\Modules\Expenses\Controllers\ExpensesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/{expenses}', [ExpensesController::class, 'show'])->name('expenses.show');
});
