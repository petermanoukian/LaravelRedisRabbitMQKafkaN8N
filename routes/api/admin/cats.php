<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\CatController;

/*
|--------------------------------------------------------------------------
| Admin API Cats Routes
|--------------------------------------------------------------------------
|
| All routes in this file are automatically wrapped in the "auth:sanctum"
| middleware and prefixed with "api/admin/cats". They are grouped under
| the "api.admin.cats." namespace.
|
*/

Route::prefix('cats')->name('cats.')->group(function () {
    /*
    Route::get('/', [CatController::class, 'index'])->name('index');
    Route::get('/index', [CatController::class, 'index'])->name('index');

    Route::get('/redis', [CatController::class, 'indexRedisJson'])->name('indexRedis');
    Route::get('/json', [CatController::class, 'indexJson'])->name('indexJson');
    Route::get('/{id}', [CatController::class, 'show'])->name('show');

    // Allow PUT, PATCH, and POST for update
    Route::match(['put', 'patch', 'post'], '/update/{id}', [CatController::class, 'update'])
        ->name('update');

    Route::delete('/delete/{id}', [CatController::class, 'destroy'])->name('destroy');
    Route::delete('/delete-many', [CatController::class, 'destroyMany'])->name('destroyMany');

    Route::post('/', [CatController::class, 'store'])->name('store'); // standard API create

    */
});
