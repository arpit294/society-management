<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesDataTable;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\MaintenanceBill;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ExpenseController
 *
 * Manages society expenses, allowing secretaries or committee members
 * to log outward cash flow, attach invoices, and categorize spending.
 */
class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses along with summary widgets.
     *
     * @return mixed
     */
    public function index(ExpensesDataTable $dataTable)
    {
        // Calculate key metrics for the Expense Dashboard
        $totalExpenses = Expense::sum('total_amount');
        $thisMonthExpenses = Expense::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('total_amount');

        $totalInvoices = Expense::whereNotNull('invoice')->count();
        $totalMaintenanceIncome = MaintenanceBill::where('status', 'paid')->sum('total_amount');

        $categories = ExpenseCategory::all();
        $users = User::whereIn('role', ['secretary', 'committee_member'])->get();

        return $dataTable->render('expenses.index', compact('totalExpenses', 'thisMonthExpenses', 'totalInvoices', 'totalMaintenanceIncome', 'categories', 'users'));
    }

    /**
     * Show the form for creating a new expense record.
     *
     * @return View
     */
    public function create()
    {
        $users = User::whereIn('role', ['secretary', 'committee_member'])->get();
        $categories = ExpenseCategory::where('status', 'active')->get();

        return view('expenses.create', compact('users', 'categories'));
    }

    /**
     * Store a newly created expense in the database.
     * Handles optional invoice file uploads.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // Normalize month-only dates (e.g. YYYY-MM) to the first of the month
        if ($request->has('expense_date') && strlen($request->expense_date) === 7) {
            $request->merge(['expense_date' => $request->expense_date.'-01']);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:expense_categories,id',
            'invoice' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Process file upload if an invoice was provided
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

    /**
     * Show the form for editing the specified expense.
     *
     * @return View
     */
    public function edit(Expense $expense)
    {
        $users = User::whereIn('role', ['secretary', 'committee_member'])->get();
        $categories = ExpenseCategory::where('status', 'active')->get();

        return view('expenses.edit', compact('expense', 'users', 'categories'));
    }

    /**
     * Update the specified expense in the database.
     * Deletes the old invoice file if a new one is uploaded.
     *
     * @return JsonResponse
     */
    public function update(Request $request, Expense $expense)
    {
        // Normalize month-only dates (e.g. YYYY-MM) to the first of the month
        if ($request->has('expense_date') && strlen($request->expense_date) === 7) {
            $request->merge(['expense_date' => $request->expense_date.'-01']);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:expense_categories,id',
            'invoice' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Process file upload and cleanup old file
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

    /**
     * Remove the specified expense from storage.
     * Ensures associated invoice files are also deleted.
     *
     * @return JsonResponse
     */
    public function destroy(Expense $expense)
    {
        // Delete invoice file if exists before removing the DB record
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
