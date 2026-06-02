<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\User;
use App\Models\ExpenseCategory;
use App\DataTables\ExpensesDataTable;

class ExpenseController extends Controller
{
    public function index(ExpensesDataTable $dataTable)
    {
        return $dataTable->render('expenses.index');
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
            $filename = time() . '_' . $file->getClientOriginalName();
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
            if ($expense->invoice && file_exists(public_path('uploads/invoices/' . $expense->invoice))) {
                unlink(public_path('uploads/invoices/' . $expense->invoice));
            }
            $file = $request->file('invoice');
            $filename = time() . '_' . $file->getClientOriginalName();
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
        if ($expense->invoice && file_exists(public_path('uploads/invoices/' . $expense->invoice))) {
            unlink(public_path('uploads/invoices/' . $expense->invoice));
        }
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully.',
        ]);
    }
}
