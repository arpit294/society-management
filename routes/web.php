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
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.alias');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Users
    Route::resource('users', UserController::class)->except(['show']);

    // Flats
    Route::resource('flats', FlatController::class)->except(['show']);
    Route::get('api/flats-by-block/{block_id}', [\App\Http\Controllers\ResidentController::class, 'getFlatsByBlock'])->name('api.flats-by-block');

    // Blocks
    Route::resource('blocks', BlockController::class)->except(['show']);

    // Complains
    Route::resource('complains', \App\Http\Controllers\ComplainController::class)->except(['show']);

    // Residents
    Route::resource('residents', \App\Http\Controllers\ResidentController::class)->except(['show']);
    // Expenses
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['show']);

    // Expense Categories
    Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->except(['show']);

    // Flat Types
    Route::resource('flat-types', \App\Http\Controllers\FlatTypeController::class)->except(['show']);

    // Maintenance Bills
    Route::get('maintenance-bills/resident-info/{user_id}', [\App\Http\Controllers\MaintenanceBillController::class, 'getResidentInfo'])->name('maintenance-bills.resident-info');
    Route::delete('maintenance-bills/individual/{id}', [\App\Http\Controllers\MaintenanceBillController::class, 'destroyIndividual'])->name('maintenance-bills.destroy-individual');
    Route::get('maintenance-bills/details/{id}', [\App\Http\Controllers\MaintenanceBillController::class, 'details'])->name('maintenance-bills.details');
    Route::get('maintenance-bills/download-invoice/{id}', [\App\Http\Controllers\MaintenanceBillController::class, 'downloadInvoice'])->name('maintenance-bills.download-invoice');
    Route::resource('maintenance-bills', \App\Http\Controllers\MaintenanceBillController::class);
    Route::post('maintenance-bills/{maintenanceBill}/update-status', [\App\Http\Controllers\MaintenanceBillController::class, 'updateStatus'])->name('maintenance-bills.update-status');
});

// 
