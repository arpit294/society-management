<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\FlatDocumentController;
use App\Http\Controllers\FlatTypeController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MaintenanceBillController;
use App\Http\Controllers\NameTransferBillController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
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
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('permission:dashboard_view')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alias');
    });

    Route::middleware('permission:user_view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:flat_view')->group(function () {
        Route::get('flats', [FlatController::class, 'index'])->name('flats.index');
        Route::get('flats/create', [FlatController::class, 'create'])->name('flats.create');
        Route::post('flats', [FlatController::class, 'store'])->name('flats.store');
        Route::get('flats/{flat}', [FlatController::class, 'show'])->name('flats.show');
        Route::get('flats/{flat}/edit', [FlatController::class, 'edit'])->name('flats.edit');
        Route::put('flats/{flat}', [FlatController::class, 'update'])->name('flats.update');
        Route::patch('flats/{flat}', [FlatController::class, 'update']);
        Route::delete('flats/{flat}', [FlatController::class, 'destroy'])->name('flats.destroy');
        Route::get('flats/{flat}/transfer', [FlatController::class, 'transferCreate'])->name('flats.transfer.create');
        Route::post('flats/{flat}/transfer', [FlatController::class, 'transferStore'])->name('flats.transfer.store');
    });

    Route::middleware('permission:resident_view')->group(function () {
        Route::get('api/flats-by-block/{block_id}', [ResidentController::class, 'getFlatsByBlock'])->name('api.flats-by-block');
        Route::get('api/flat-owner/{flat_id}', [ResidentController::class, 'getFlatOwner'])->name('api.flat-owner');
        Route::get('api/flat-users/{flat_id}', [ResidentController::class, 'getFlatUsers'])->name('api.flat-users');
        Route::get('residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::get('residents/create', [ResidentController::class, 'create'])->name('residents.create');
        Route::post('residents', [ResidentController::class, 'store'])->name('residents.store');
        Route::get('residents/{resident}/edit', [ResidentController::class, 'edit'])->name('residents.edit');
        Route::put('residents/{resident}', [ResidentController::class, 'update'])->name('residents.update');
        Route::patch('residents/{resident}', [ResidentController::class, 'update']);
        Route::delete('residents/{resident}', [ResidentController::class, 'destroy'])->name('residents.destroy');
        Route::get('residents/export', [ResidentController::class, 'export'])->name('residents.export');
        Route::post('residents/import/preview', [ResidentController::class, 'previewImport'])->name('residents.import.preview');
        Route::post('residents/import/process', [ResidentController::class, 'processImport'])->name('residents.import.process');
        Route::get('residents/import/template', [ResidentController::class, 'downloadTemplate'])->name('residents.import.template');
    });

    Route::middleware('permission:block_view')->group(function () {
        Route::get('blocks', [BlockController::class, 'index'])->name('blocks.index');
        Route::get('blocks/create', [BlockController::class, 'create'])->name('blocks.create');
        Route::post('blocks', [BlockController::class, 'store'])->name('blocks.store');
        Route::get('blocks/{block}/edit', [BlockController::class, 'edit'])->name('blocks.edit');
        Route::put('blocks/{block}', [BlockController::class, 'update'])->name('blocks.update');
        Route::patch('blocks/{block}', [BlockController::class, 'update']);
        Route::delete('blocks/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');
    });

    Route::middleware('permission:complain_view')->group(function () {
        Route::get('complains', [ComplainController::class, 'index'])->name('complains.index');
        Route::get('complains/create', [ComplainController::class, 'create'])->name('complains.create');
        Route::post('complains', [ComplainController::class, 'store'])->name('complains.store');
        Route::get('complains/{complain}/edit', [ComplainController::class, 'edit'])->name('complains.edit');
        Route::put('complains/{complain}', [ComplainController::class, 'update'])->name('complains.update');
        Route::patch('complains/{complain}', [ComplainController::class, 'update']);
        Route::delete('complains/{complain}', [ComplainController::class, 'destroy'])->name('complains.destroy');
    });

    Route::middleware('permission:expense_view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::patch('expenses/{expense}', [ExpenseController::class, 'update']);
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    Route::middleware('permission:flat_document_view')->group(function () {
        Route::get('flat-documents', [FlatDocumentController::class, 'index'])->name('flat-documents.index');
        Route::get('flat-documents/create', [FlatDocumentController::class, 'create'])->name('flat-documents.create');
        Route::post('flat-documents', [FlatDocumentController::class, 'store'])->name('flat-documents.store');
        Route::get('flat-documents/{flat_document}', [FlatDocumentController::class, 'show'])->name('flat-documents.show');
        Route::delete('flat-documents/{flat_document}', [FlatDocumentController::class, 'destroy'])->name('flat-documents.destroy');
        Route::get('flat-documents/{flat_document}/download/{doc_key}', [FlatDocumentController::class, 'download'])->name('flat-documents.download');
    });

    Route::middleware('permission:expense_category_view')->group(function () {
        Route::get('expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::get('expense-categories/create', [ExpenseCategoryController::class, 'create'])->name('expense-categories.create');
        Route::post('expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::get('expense-categories/{expense_category}/edit', [ExpenseCategoryController::class, 'edit'])->name('expense-categories.edit');
        Route::put('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
        Route::patch('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'update']);
        Route::delete('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
    });

    Route::middleware('permission:flat_type_view')->group(function () {
        Route::get('flat-types', [FlatTypeController::class, 'index'])->name('flat-types.index');
        Route::get('flat-types/create', [FlatTypeController::class, 'create'])->name('flat-types.create');
        Route::post('flat-types', [FlatTypeController::class, 'store'])->name('flat-types.store');
        Route::get('flat-types/{flat_type}/edit', [FlatTypeController::class, 'edit'])->name('flat-types.edit');
        Route::put('flat-types/{flat_type}', [FlatTypeController::class, 'update'])->name('flat-types.update');
        Route::patch('flat-types/{flat_type}', [FlatTypeController::class, 'update']);
        Route::delete('flat-types/{flat_type}', [FlatTypeController::class, 'destroy'])->name('flat-types.destroy');
    });

    Route::middleware('permission:maintenance_bill_view')->group(function () {
        Route::get('maintenance-bills/resident-info/{user_id}', [MaintenanceBillController::class, 'getResidentInfo'])->name('maintenance-bills.resident-info');
        Route::get('maintenance-bills/details/{id}', [MaintenanceBillController::class, 'details'])->name('maintenance-bills.details');
        Route::get('maintenance-bills/download-invoice/{id}', [MaintenanceBillController::class, 'downloadInvoice'])->name('maintenance-bills.download-invoice');
        Route::get('maintenance-bills', [MaintenanceBillController::class, 'index'])->name('maintenance-bills.index');
        Route::get('maintenance-bills/create', [MaintenanceBillController::class, 'create'])->name('maintenance-bills.create');
        Route::post('maintenance-bills', [MaintenanceBillController::class, 'store'])->name('maintenance-bills.store');
        Route::delete('maintenance-bills/individual/{id}', [MaintenanceBillController::class, 'destroyIndividual'])->name('maintenance-bills.destroy-individual');
        Route::delete('maintenance-bills/{maintenanceBill}', [MaintenanceBillController::class, 'destroy'])->name('maintenance-bills.destroy');
        Route::post('maintenance-bills/{maintenanceBill}/update-status', [MaintenanceBillController::class, 'updateStatus'])->name('maintenance-bills.update-status');
    });

    Route::middleware('permission:name_transfer_bill_view')->group(function () {
        Route::get('name-transfer-bills', [NameTransferBillController::class, 'index'])->name('name-transfer-bills.index');
        Route::post('name-transfer-bills/{bill}/approve', [NameTransferBillController::class, 'approve'])->name('name-transfer-bills.approve');
        Route::post('name-transfer-bills/{bill}/update-status', [NameTransferBillController::class, 'updateStatus'])->name('name-transfer-bills.update-status');
        Route::delete('name-transfer-bills/{bill}', [NameTransferBillController::class, 'destroy'])->name('name-transfer-bills.destroy');
    });

    Route::middleware('permission:setting_view')->group(function () {
        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'store'])->name('settings.store');

        // Reports
        Route::get('reports/maintenance/export', [ReportController::class, 'exportReport'])->name('reports.maintenance.export');
        Route::get('reports/maintenance', [ReportController::class, 'maintenanceReport'])->name('reports.maintenance');
    });

    Route::middleware('permission:setting_edit')->group(function () {
        Route::resource('roles', RoleAndPermissionController::class)
            ->only(['store', 'edit', 'update', 'destroy']);
    });
});
