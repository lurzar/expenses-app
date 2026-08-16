<?php

use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Authorization\Permissions\SystemPermission;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth',
    'verified',
    'can:'.SystemPermission::AccessAdmin->value,
])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
});
