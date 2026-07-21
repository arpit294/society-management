<?php

namespace App\Http\Controllers;

use App\DataTables\FlatTypesDataTable;
use App\Models\FlatType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FlatTypeController extends Controller
{
    private const FLAT_TYPE_NAMES = ['1 BHK', '2 BHK', '3 BHK', '4 BHK', '5 BHK'];

    public function index(FlatTypesDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('flat_type_view'), 403);
        try {
            \App\Http\Controllers\FlatController::syncDefaultFlatTypes();
            return $dataTable->render('flat_types.index');
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
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
            if ($e instanceof ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
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
                'name' => 'required|string|max:255|unique:flat_types,name',
                'owner_maintenance_fee' => 'required|numeric|min:0',
                'rental_maintenance_fee' => 'required|numeric|min:0',
                'calculation_method' => 'nullable|string|in:fixed,per_sqft,hybrid',
                'category_type' => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);
            $validatedData['calculation_method'] = $validatedData['calculation_method'] ?? 'fixed';
            $validatedData['category_type'] = $validatedData['category_type'] ?? 'residential';

            FlatType::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => Setting::label('unit_type', 'Flat Type') . ' created successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
                'name' => 'required|string|max:255|unique:flat_types,name,'.$flatType->id,
                'owner_maintenance_fee' => 'required|numeric|min:0',
                'rental_maintenance_fee' => 'required|numeric|min:0',
                'calculation_method' => 'nullable|string|in:fixed,per_sqft,hybrid',
                'category_type' => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            $validatedData['calculation_method'] = $validatedData['calculation_method'] ?? 'fixed';
            $validatedData['category_type'] = $validatedData['category_type'] ?? 'residential';

            $flatType->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => Setting::label('unit_type', 'Flat Type') . ' updated successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            if (\App\Models\Flat::where('flat_type_id', $flatType->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete because flats are currently assigned to this unit type.',
                ], 422);
            }
            $flatType->delete();

            return response()->json([
                'success' => true,
                'message' => \App\Models\Setting::label('unit_type', 'Flat Type') . ' deleted successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
