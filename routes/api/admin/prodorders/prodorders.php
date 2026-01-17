<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ProdorderController;

Route::prefix('prodorders')->name('prodorders.')->group(function () {
    Route::get('/', [ProdorderController::class, 'index'])->name('index');


    Route::post('/', [ProdorderController::class, 'store'])->name('store');

    // Allow PUT, PATCH, and POST for update
    Route::match(['put', 'patch', 'post'], '/update/{id}', [ProdorderController::class, 'update'])
        ->name('update');

    Route::delete('/delete/{id}', [ProdorderController::class, 'destroy'])->name('destroy');
    Route::delete('/delete-many', [ProdorderController::class, 'destroyMany'])->name('destroyMany');
});
