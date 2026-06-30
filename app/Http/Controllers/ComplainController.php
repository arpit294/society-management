<?php

namespace App\Http\Controllers;

use App\DataTables\ComplainsDataTable;
use App\Models\Complain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ComplainController extends Controller
{
    public function index(ComplainsDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('complain_view'), 403);
        try {
            return $dataTable->render('complains.index');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ComplainController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function create()
    {
        abort_if(! \Auth::user()->can('complain_create'), 403);
        try {
            $users = User::query()->get();

            return response()->view('complains.create', compact('users'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ComplainController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('complain_create'), 403);
        try {
            $validatedData = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'user_id' => 'required|exists:users,id',
                'category' => ['required', Rule::in([
                    'Maintenance Issues',
                    'Security Issues',
                    'Cleanliness & Housekeeping',
                    'Common Facilities',
                    'other',
                ])],
            ]);

            Complain::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Complaint created successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ComplainController@store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Complain $complain)
    {
        abort_if(! \Auth::user()->can('complain_edit'), 403);
        try {
            $users = User::query()->get();

            return response()->view('complains.edit', compact('complain', 'users'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ComplainController@edit: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


    public function update(Request $request, Complain $complain)
    {
        abort_if(! \Auth::user()->can('complain_edit'), 403);
        try {
            $validatedData = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'user_id' => 'required|exists:users,id',
                'category' => ['required', Rule::in([
                    'Maintenance Issues',
                    'Security Issues',
                    'Cleanliness & Housekeeping',
                    'Common Facilities',
                    'other',
                ])],
                'status' => ['required', Rule::in(array_values(config('status.complaints')))],
                'resolution_notes' => 'nullable|string',
            ]);

            $complain->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Complaint updated successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ComplainController@update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Complain $complain)
    {
        abort_if(! \Auth::user()->can('complain_delete'), 403);
        try {
            $complain->delete();

            return response()->json([
                'success' => true,
                'message' => 'Complaint deleted successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in ComplainController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
