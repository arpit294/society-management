<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\User;
use App\Models\ExpenseCategory;
use App\DataTables\ExpensesDataTable;
use App\Models\MaintenanceBill;
use App\Models\NameTransferBill;

class ExpenseController extends Controller
{
    public function index(ExpensesDataTable $dataTable)
    {
        abort_if(\Gate::denies('expense_view'), 403);
        $totalExpenses = Expense::sum('total_amount');
        $thisMonthExpenses = Expense::whereMonth('created_at', date('m'))
                                    ->whereYear('created_at', date('Y'))
                                    ->sum('total_amount');
        $totalInvoices = Expense::whereNotNull('invoice')->count();
        $totalMaintenanceIncome = MaintenanceBill::where('status', 'paid')->sum('total_amount')
                                + NameTransferBill::where('status', 'paid')->sum('amount');
        $categories = ExpenseCategory::all();
        $users = User::whereIn('role', ['secretary', 'committee_member'])->get();

        return $dataTable->render('expenses.index', compact('totalExpenses', 'thisMonthExpenses', 'totalInvoices', 'totalMaintenanceIncome', 'categories', 'users'));
    }

    public function create()
    {
        abort_if(\Gate::denies('expense_create'), 403);
        $users = User::whereIn('role', ['secretary', 'committee_member'])->get();
        $categories = ExpenseCategory::where('status', 'active')->get();
        return view('expenses.create', compact('users', 'categories'));
    }

    public function store(Request $request)
    {
        abort_if(\Gate::denies('expense_create'), 403);
        if ($request->has('expense_date') && strlen($request->expense_date) === 7) {
            $request->merge(['expense_date' => $request->expense_date . '-01']);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
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
        abort_if(\Gate::denies('expense_edit'), 403);
        $users = User::whereIn('role', ['secretary', 'committee_member'])->get();
        $categories = ExpenseCategory::where('status', 'active')->get();
        return view('expenses.edit', compact('expense', 'users', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        abort_if(\Gate::denies('expense_edit'), 403);
        if ($request->has('expense_date') && strlen($request->expense_date) === 7) {
            $request->merge(['expense_date' => $request->expense_date . '-01']);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:expense_categories,id',
            'invoice' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);
        
        // Delete old file if exists
        if ($request->hasFile('invoice')) {

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
        abort_if(\Gate::denies('expense_delete'), 403);
        // Delete  invoice file if exists
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



