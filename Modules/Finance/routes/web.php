<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\ExpenseCategoryController;
use Modules\Finance\Http\Controllers\ExpenseController;
use Modules\Finance\Http\Controllers\MaintenanceBillController;
use Modules\Finance\Http\Controllers\NameTransferBillController;
use Modules\Finance\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Finance Module Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->group(function () {
    // 1. Expenses Management
    Route::middleware('permission:expense_view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::patch('expenses/{expense}', [ExpenseController::class, 'update']);
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // 2. Expense Categories
    Route::middleware('permission:expense_category_view')->group(function () {
        Route::get('expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::get('expense-categories/create', [ExpenseCategoryController::class, 'create'])->name('expense-categories.create');
        Route::post('expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::get('expense-categories/{expense_category}/edit', [ExpenseCategoryController::class, 'edit'])->name('expense-categories.edit');
        Route::put('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
        Route::patch('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'update']);
        Route::delete('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
    });

    // 3. Maintenance Bills / Payments
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

    // 4. Name Transfer Bills
    Route::middleware('permission:name_transfer_bill_view')->group(function () {
        Route::get('name-transfer-bills', [NameTransferBillController::class, 'index'])->name('name-transfer-bills.index');
        Route::post('name-transfer-bills/{bill}/approve', [NameTransferBillController::class, 'approve'])->name('name-transfer-bills.approve');
        Route::post('name-transfer-bills/{bill}/update-status', [NameTransferBillController::class, 'updateStatus'])->name('name-transfer-bills.update-status');
        Route::delete('name-transfer-bills/{bill}', [NameTransferBillController::class, 'destroy'])->name('name-transfer-bills.destroy');
    });

    // 5. Financial Reports
    Route::middleware('permission:setting_view')->group(function () {
        Route::get('reports/maintenance/export', [ReportController::class, 'exportReport'])->name('reports.maintenance.export');
        Route::get('reports/expense/export', [ReportController::class, 'exportExpenseReport'])->name('reports.expense.export');
        Route::get('reports/summary/export', [ReportController::class, 'exportSummaryReport'])->name('reports.summary.export');
        Route::get('reports/maintenance', [ReportController::class, 'maintenanceReport'])->name('reports.maintenance');
        Route::get('reports/maintenance/users-yearly-data', [ReportController::class, 'usersYearlyData'])->name('reports.usersYearly.data');
    });
});
