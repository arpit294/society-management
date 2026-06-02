<?php

namespace App\Http\Controllers;

use App\DataTables\ExpenseCategoriesDataTable;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    public function index(ExpenseCategoriesDataTable $dataTable)
    {
        return $dataTable->render('expense_categories.index');
    }

    public function create()
    {
        return view('expense_categories.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:expense_categories',
            'status' => 'required|in:active,inactive',
        ]);

        ExpenseCategory::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense Category created successfully.',
        ]);
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:expense_categories,slug,' . $expenseCategory->id,
            'status' => 'required|in:active,inactive',
        ]);

        $expenseCategory->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense Category updated successfully.',
        ]);
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense Category deleted successfully.',
        ]);
    }
}
