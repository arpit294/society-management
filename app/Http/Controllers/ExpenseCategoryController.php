<?php

namespace App\Http\Controllers;

use App\DataTables\ExpenseCategoriesDataTable;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of expense categories.
     *
     * @return mixed
     */
    public function index(ExpenseCategoriesDataTable $dataTable)
    {
        return $dataTable->render('expense_categories.index');
    }

    /**
     * Show the form for creating a new expense category.
     *
     * @return View
     */
    public function create()
    {
        return view('expense_categories.create');
    }

    /**
     * Store a newly created expense category in storage.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        ExpenseCategory::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense Category created successfully.',
        ]);
    }

    /**
     * Show the form for editing the specified expense category.
     *
     * @return View
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.edit', compact('expenseCategory'));
    }

    /**
     * Update the specified expense category in storage.
     *
     * @return JsonResponse
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $expenseCategory->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense Category updated successfully.',
        ]);
    }

    /**
     * Remove the specified expense category from storage.
     *
     * @return JsonResponse
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense Category deleted successfully.',
        ]);
    }
}
