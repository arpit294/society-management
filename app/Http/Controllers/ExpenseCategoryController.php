<?php

namespace App\Http\Controllers;

use App\DataTables\ExpenseCategoriesDataTable;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function index(ExpenseCategoriesDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('expense_category_view'), 403);
        try {
            return $dataTable->render('expense_categories.index');
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ExpenseCategoryController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function create()
    {
        abort_if(! \Auth::user()->can('expense_category_create'), 403);
        try {
            return view('expense_categories.create');
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ExpenseCategoryController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('expense_category_create'), 403);
        try {
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ExpenseCategoryController@store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        abort_if(! \Auth::user()->can('expense_category_edit'), 403);
        try {
            return view('expense_categories.edit', compact('expenseCategory'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ExpenseCategoryController@edit: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        abort_if(! \Auth::user()->can('expense_category_edit'), 403);
        try {
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ExpenseCategoryController@update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        abort_if(! \Auth::user()->can('expense_category_delete'), 403);
        try {
            $expenseCategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense Category deleted successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ExpenseCategoryController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
