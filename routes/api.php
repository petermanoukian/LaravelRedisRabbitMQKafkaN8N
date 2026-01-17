<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Api\AuthController;


Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);
    Route::get('/user', [AuthController::class, 'loggedUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


/*
Route::prefix('admin')
    ->name('api.admin.')
    ->middleware(['auth:sanctum', 'admin.type:2']) // enforce API admin type
    ->group(function () {
        require __DIR__ . '/api/admin/admin.php';
    });
*/
// Level 2 admins: read access
Route::prefix('admin')
    ->name('api.admin.')
    ->middleware(['auth:sanctum', 'admin.type:2'])
    ->group(function () {
        require __DIR__ . '/api/admin/adminread.php';
    });

// Level 1 admins: write access
Route::prefix('admin')
    ->name('api.admin.')
    ->middleware(['auth:sanctum', 'admin.type:1'])
    ->group(function () {
        require __DIR__ . '/api/admin/adminwrite.php';
    });
