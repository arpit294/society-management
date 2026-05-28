<?php

use App\Http\Controllers\ForegetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    //this route is stands for redirect to the reset pass email enters page
    Route::get('/forgot-password', function () {
        return view('authentication.forget-password');
    })->name('password.request');


    //this is for handling form submission
    Route::post('/forgot-password', [ForegetPasswordController::class, 'submit'])->name('password.email');

    //password reset form
    Route::get('/reset-password/{token}', function (string $token) {
        return view('authentication.reset_password', ['token' => $token]);
    })->name('password.reset');


    Route::post('/reset-password',  [ForegetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('/');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::resource('users', UserController::class);

    Route::view('flats', 'flates.index')->name('flats.index');
    Route::view('flats/create', 'flates.index')->name('flats.create');
});

//
Route::view('/flat', "flats.index");
