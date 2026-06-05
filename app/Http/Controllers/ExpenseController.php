<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesDataTable;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\MaintenanceBill;
use App\Models\User;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(ExpensesDataTable $dataTable)
    {
        $totalExpenses = Expense::sum('total_amount');
        $thisMonthExpenses = Expense::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('total_amount');
        $totalInvoices = Expense::whereNotNull('invoice')->count();
        $totalMaintenanceIncome = MaintenanceBill::where('status', 'paid')->sum('total_amount');
        $categories = ExpenseCategory::all();

        return $dataTable->render('expenses.index', compact('totalExpenses', 'thisMonthExpenses', 'totalInvoices', 'totalMaintenanceIncome', 'categories'));
    }

    public function create()
    {
        $users = User::all();
        $categories = ExpenseCategory::where('status', 'active')->get();

        return view('expenses.create', compact('users', 'categories'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:expense_categories,id',
            'invoice' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($request->hasFile('invoice')) {
            $file = $request->file('invoice');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/invoices'), $filename);
            $validatedData['invoice'] = $filename;
        }

        Expense::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense logged successfully.',
        ]);
    }

    public function edit(Expense $expense)
    {
        $users = User::all();
        $categories = ExpenseCategory::where('status', 'active')->get();

        return view('expenses.edit', compact('expense', 'users', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:expense_categories,id',
            'invoice' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($request->hasFile('invoice')) {
            // Delete old file if exists
            if ($expense->invoice && file_exists(public_path('uploads/invoices/'.$expense->invoice))) {
                unlink(public_path('uploads/invoices/'.$expense->invoice));
            }
            $file = $request->file('invoice');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/invoices'), $filename);
            $validatedData['invoice'] = $filename;
        }

        $expense->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense updated successfully.',
        ]);
    }

    public function destroy(Expense $expense)
    {
        if ($expense->invoice && file_exists(public_path('uploads/invoices/'.$expense->invoice))) {
            unlink(public_path('uploads/invoices/'.$expense->invoice));
        }
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully.',
        ]);
    }
}
