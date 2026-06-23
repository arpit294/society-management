<?php

namespace App\Http\Controllers;

use App\DataTables\ExpenseCategoriesDataTable;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function index(ExpenseCategoriesDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('expense_category_view'), 403);
        return $dataTable->render('expense_categories.index');
    }

    public function create()
    {
        abort_if(! \Auth::user()->can('expense_category_create'), 403);
        return view('expense_categories.create');
    }

    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('expense_category_create'), 403);
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validatedData['slug'] = Str::slug($validatedData['title']);
        ExpenseCategory::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense Category created successfully.',
        ]);
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        abort_if(! \Auth::user()->can('expense_category_edit'), 403);
        return view('expense_categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        abort_if(! \Auth::user()->can('expense_category_edit'), 403);
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validatedData['slug'] = Str::slug($validatedData['title']);
        $expenseCategory->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Expense Category updated successfully.',
        ]);
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        abort_if(! \Auth::user()->can('expense_category_delete'), 403);
        $expenseCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense Category deleted successfully.',
        ]);
    }
}
