<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlatController;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('/');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    //resource controller
    Route::resource('users', UserController::class);
});



Route::middleware('auth')->group(function () {
    Route::resource('flats', FlatController::class);
    Route::resource('blocks', \App\Http\Controllers\BlockController::class);

    // Data endpoint for yajra DataTables (blocks)
    Route::get('blocks/data', [\App\Http\Controllers\BlocksDataController::class, 'data'])->name('blocks.data');
});
