<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FlatController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    // Password Reset Routes
    Route::get('/forgot-password', function () {
        return view('authentication.forget-password');
    })->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'submit'])->name('password.email');

    Route::get('/reset-password/{token}', function (string $token) {
        return view('authentication.reset_password', ['token' => $token]);
    })->name('password.reset');

    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('/');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Users
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulkDelete');
    Route::post('users/bulk-update', [UserController::class, 'bulkUpdate'])->name('users.bulkUpdate');
    Route::resource('users', UserController::class)->except(['show']);

    // Flats
    Route::post('flats/bulk-delete', [FlatController::class, 'bulkDelete'])->name('flats.bulkDelete');
    Route::post('flats/bulk-update', [FlatController::class, 'bulkUpdate'])->name('flats.bulkUpdate');
    Route::resource('flats', FlatController::class)->except(['show']);

    // Blocks
    Route::post('blocks/bulk-delete', [BlockController::class, 'bulkDelete'])->name('blocks.bulkDelete');
    Route::resource('blocks', BlockController::class)->except(['show']);
});
