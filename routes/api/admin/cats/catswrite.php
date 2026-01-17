<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\CatController;



Route::prefix('cats')->name('cats.')->group(function () {
    

    Route::match(['put', 'patch', 'post'], '/update/{id}', [CatController::class, 'update'])
        ->name('update');

    Route::delete('/delete/{id}', [CatController::class, 'destroy'])->name('destroy');
    Route::delete('/delete-many', [CatController::class, 'destroyMany'])->name('destroyMany');

    Route::post('/', [CatController::class, 'store'])->name('store'); // standard API create

    
});
