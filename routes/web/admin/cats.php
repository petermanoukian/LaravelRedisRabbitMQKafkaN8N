<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CatController;

/*
|--------------------------------------------------------------------------
| Admin Cats Routes
|--------------------------------------------------------------------------
|
| All routes in this file are automatically wrapped in the "auth" middleware
| and prefixed with "admin/cats". They are grouped under the "admin.cats." namespace.
|
*/

Route::prefix('cats')->name('cats.')->group(function () {
    Route::get('/', [CatController::class, 'index'])->name('index');
    Route::get('/index', [CatController::class, 'index'])->name('index');
    Route::get('/create', [CatController::class, 'create'])->name('create');
    Route::post('/add', [CatController::class, 'store'])->name('add');
    Route::get('/edit/{id}', [CatController::class, 'edit'])->name('edit');

    // Allow PUT, PATCH, and POST for update
    Route::match(['put', 'patch', 'post'], '/update/{id}', [CatController::class, 'update'])
        ->name('update');

    Route::delete('/delete/{id}', [CatController::class, 'destroy'])->name('destroy');
    Route::delete('/delete-many', [CatController::class, 'destroyMany'])->name('destroyMany');
});
