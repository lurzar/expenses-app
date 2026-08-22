<?php

use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Admin\Controllers\AdminRoleController;
use App\Modules\Admin\Controllers\AdminUserController;
use App\Modules\Authorization\Permissions\SystemPermission;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth',
    'verified',
    'can:'.SystemPermission::AccessAdmin->value,
])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users', [AdminUserController::class, 'index'])
        ->middleware('can:'.SystemPermission::ViewUsers->value)
        ->name('users.index');
    Route::patch('/users/{user}/roles', [AdminUserController::class, 'update'])
        ->middleware('can:'.SystemPermission::ManageUserRoles->value)
        ->name('users.roles.update');
    Route::get('/roles', [AdminRoleController::class, 'index'])
        ->middleware('can:'.SystemPermission::ManageRoles->value)
        ->name('roles.index');
    Route::post('/roles', [AdminRoleController::class, 'store'])
        ->middleware('can:'.SystemPermission::ManageRoles->value)
        ->name('roles.store');
    Route::patch('/roles/{role}', [AdminRoleController::class, 'update'])
        ->middleware('can:'.SystemPermission::ManageRoles->value)
        ->name('roles.update');
    Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])
        ->middleware('can:'.SystemPermission::ManageRoles->value)
        ->name('roles.destroy');
});
