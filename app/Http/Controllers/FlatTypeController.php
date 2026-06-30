<?php

namespace App\Http\Controllers;

use App\DataTables\FlatTypesDataTable;
use App\Models\FlatType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlatTypeController extends Controller
{
    private const FLAT_TYPE_NAMES = ['1BHK', '2BHK', '3BHK', '4BHK', '5BHK'];

    public function index(FlatTypesDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('flat_type_view'), 403);
        try {
            return $dataTable->render('flat_types.index');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatTypeController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function create()
    {
        abort_if(! \Auth::user()->can('flat_type_create'), 403);
        try {
            return view('flat_types.create');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatTypeController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('flat_type_create'), 403);
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|in:'.implode(',', self::FLAT_TYPE_NAMES).'|unique:flat_types,name',
                'owner_maintenance_fee' => 'required|numeric|min:0',
                'rental_maintenance_fee' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive',
            ]);

            FlatType::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Flat Type created successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatTypeController@store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(FlatType $flatType)
    {
        abort_if(! \Auth::user()->can('flat_type_edit'), 403);
        try {
            return view('flat_types.edit', compact('flatType'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatTypeController@edit: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function update(Request $request, FlatType $flatType)
    {
        abort_if(! \Auth::user()->can('flat_type_edit'), 403);
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|in:'.implode(',', self::FLAT_TYPE_NAMES).'|unique:flat_types,name,'.$flatType->id,
                'owner_maintenance_fee' => 'required|numeric|min:0',
                'rental_maintenance_fee' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive',
            ]);

            $flatType->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Flat Type updated successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatTypeController@update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(FlatType $flatType)
    {
        abort_if(! \Auth::user()->can('flat_type_delete'), 403);
        try {
            $flatType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Flat Type deleted successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatTypeController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
