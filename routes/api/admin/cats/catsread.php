<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\CatController;



Route::prefix('cats')->name('cats.')->group(function () {
    
    Route::get('/', [CatController::class, 'index'])->name('index');
    Route::get('/index', [CatController::class, 'index'])->name('index');

    Route::get('/redis', [CatController::class, 'indexRedisJson'])->name('indexRedis');
    Route::get('/json', [CatController::class, 'indexJson'])->name('indexJson');
    Route::get('/{id}', [CatController::class, 'show'])->name('show');

});
