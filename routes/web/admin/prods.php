<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProdController;

/*
|--------------------------------------------------------------------------
| Admin Prods Routes
|--------------------------------------------------------------------------
|
| All routes in this file are automatically wrapped in the "auth" middleware
| and prefixed with "admin/prods". They are grouped under the "admin.prods." namespace.
|
 
*/


Route::prefix('prods')->name('prods.')->group(function () {
    
    Route::match(['get', 'post'], '/index/{catid?}', [ProdController::class, 'index'])
        ->name('index');

    Route::get('/create/{catid?}', [ProdController::class, 'create'])->name('create');
    Route::post('/add', [ProdController::class, 'store'])->name('add');
    Route::get('/edit/{id}', [ProdController::class, 'edit'])->name('edit');

    // Allow PUT, PATCH, and POST for update
    Route::match(['put', 'patch', 'post'], '/update/{id}', [ProdController::class, 'update'])
        ->name('update');

    Route::delete('/delete/{id}', [ProdController::class, 'destroy'])->name('destroy');
    Route::delete('/delete-many', [ProdController::class, 'destroyMany'])->name('destroyMany');
});
