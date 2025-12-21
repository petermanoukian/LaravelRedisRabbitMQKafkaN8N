<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/clear', function () {
    $exitCode = \Artisan::call('config:cache');
    $exitCode = \Artisan::call('cache:clear');
    $exitCode = \Artisan::call('route:clear');
    $exitCode = \Artisan::call('view:clear');
    $exitCode = \Artisan::call('config:clear');

    return '<h1>Clear Config cleared</h1>';
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/register', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');


Route::middleware(['auth', 'admin.type:1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        require __DIR__ . '/web/admin/admin.php';
    });
