<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\ExpenseCategoryController;
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
        $data = [
            'total_residents' => \App\Models\Resident::count(),
            'total_flats' => \App\Models\Flat::count(),
            'total_blocks' => \App\Models\Block::count(),
            'active_complaints' => \DB::table('complains')->count(), // assuming all are active for now
            'occupied_flats' => \App\Models\Flat::where('status', 'occupied')->count(),
            'empty_flats' => \App\Models\Flat::where('status', 'empty')->count(),
            'monthly_complaints' => \DB::table('complains')
                ->select(\DB::raw('MONTHNAME(created_at) as month, COUNT(*) as count'))
                ->groupBy('month')
                ->orderByRaw('MIN(created_at)')
                ->get(),
        ];
        return view('dashboard', $data);
    })->name('/');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Users
    Route::resource('users', UserController::class)->except(['show']);

    // Flats
    Route::resource('flats', FlatController::class)->except(['show']);

    // Expense Categories
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);

    // Expenses
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['show']);
    Route::resource('blocks', BlockController::class)->except(['show']);

    // Complains
    Route::resource('complains', \App\Http\Controllers\ComplainController::class)->except(['show']);

    // Residents
    Route::resource('residents', \App\Http\Controllers\ResidentController::class)->except(['show']);
    Route::get('api/flats-by-block/{block_id}', [\App\Http\Controllers\ResidentController::class, 'getFlatsByBlock'])->name('api.flats-by-block');


});

// 
