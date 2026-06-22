<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\FlatTypeController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MaintenanceBillController;
use App\Http\Controllers\NameTransferBillController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\RoleAndPermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
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
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alias');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Users
    Route::resource('users', UserController::class)->except(['show']);

    // Flats
    Route::get('flats/{flat}/transfer', [FlatController::class, 'transferCreate'])->name('flats.transfer.create');
    Route::post('flats/{flat}/transfer', [FlatController::class, 'transferStore'])->name('flats.transfer.store');
    Route::resource('flats', FlatController::class);
    Route::get('api/flats-by-block/{block_id}', [ResidentController::class, 'getFlatsByBlock'])->name('api.flats-by-block');
    Route::get('api/flat-owner/{flat_id}', [ResidentController::class, 'getFlatOwner'])->name('api.flat-owner');

    // Blocks
    Route::resource('blocks', BlockController::class)->except(['show']);

    // Complains
    Route::resource('complains', ComplainController::class)->except(['show']);

    // Residents
    Route::post('residents/import', [ResidentController::class, 'import'])->name('residents.import');
    Route::get('residents/import/template', [ResidentController::class, 'downloadTemplate'])->name('residents.import.template');
    Route::resource('residents', ResidentController::class)->except(['show']);
    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['show']);

    // Expense Categories
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);

    // Flat Types
    Route::resource('flat-types', FlatTypeController::class)->except(['show']);

    // Maintenance Bills
    Route::get('maintenance-bills/resident-info/{user_id}', [MaintenanceBillController::class, 'getResidentInfo'])->name('maintenance-bills.resident-info');
    Route::delete('maintenance-bills/individual/{id}', [MaintenanceBillController::class, 'destroyIndividual'])->name('maintenance-bills.destroy-individual');
    Route::get('maintenance-bills/details/{id}', [MaintenanceBillController::class, 'details'])->name('maintenance-bills.details');
    Route::get('maintenance-bills/download-invoice/{id}', [MaintenanceBillController::class, 'downloadInvoice'])->name('maintenance-bills.download-invoice');
    Route::post('name-transfer-bills/{bill}/approve', [NameTransferBillController::class, 'approve'])->name('name-transfer-bills.approve');
    Route::resource('name-transfer-bills', NameTransferBillController::class)->except(['create', 'store', 'show', 'edit']);
    Route::resource('maintenance-bills', MaintenanceBillController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::post('maintenance-bills/{maintenanceBill}/update-status', [MaintenanceBillController::class, 'updateStatus'])->name('maintenance-bills.update-status');

    // Name Transfer Bills
    Route::get('name-transfer-bills', [NameTransferBillController::class, 'index'])->name('name-transfer-bills.index');
    Route::post('name-transfer-bills/{bill}/update-status', [NameTransferBillController::class, 'updateStatus'])->name('name-transfer-bills.update-status');
    Route::delete('name-transfer-bills/{bill}', [NameTransferBillController::class, 'destroy'])->name('name-transfer-bills.destroy');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'store'])->name('settings.store');

    Route::resource('roles', RoleAndPermissionController::class)->except(['index', 'show', 'create']);
});

//
