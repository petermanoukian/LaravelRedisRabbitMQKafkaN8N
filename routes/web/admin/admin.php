<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| All routes in this file are automatically wrapped in the "auth" middleware
| and prefixed with "admin". They are grouped under the "admin." namespace.
|
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
require __DIR__ . '/cats.php';