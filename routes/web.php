<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Landing
Route::get('/', function () {
    return view('welcome');
});

// Language
Route::get('/language/{lang?}', [LanguageController::class, 'index'])->name('language');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Planning
    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
    Route::post('/planning', [PlanningController::class, 'store'])->name('planning.store');
    // Expenses
    Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses.index');
});

require __DIR__.'/auth.php';
