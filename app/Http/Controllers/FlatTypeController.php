<?php

namespace App\Http\Controllers;

use App\DataTables\FlatTypesDataTable;
use App\Models\FlatType;
use Illuminate\Http\Request;

class FlatTypeController extends Controller
{
    public function index(FlatTypesDataTable $dataTable)
    {
        abort_if(\Gate::denies('flat_type_view'), 403);
        return $dataTable->render('flat_types.index');
    }

    public function create()
    {
        abort_if(\Gate::denies('flat_type_create'), 403);
        return view('flat_types.create');
    }

    public function store(Request $request)
    {
        abort_if(\Gate::denies('flat_type_create'), 403);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:flat_types,name',
            'owner_maintenance_fee' => 'required|numeric|min:0',
            'rental_maintenance_fee' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        FlatType::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat Type created successfully.',
        ]);
    }

    public function edit(FlatType $flatType)
    {
        abort_if(\Gate::denies('flat_type_edit'), 403);
        return view('flat_types.edit', compact('flatType'));
    }

    public function update(Request $request, FlatType $flatType)
    {
        abort_if(\Gate::denies('flat_type_edit'), 403);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:flat_types,name,'.$flatType->id,
            'owner_maintenance_fee' => 'required|numeric|min:0',
            'rental_maintenance_fee' => 'required|numeric|min:0',
            'penalty_per_day' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $flatType->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat Type updated successfully.',
        ]);
    }

    public function destroy(FlatType $flatType)
    {
        abort_if(\Gate::denies('flat_type_delete'), 403);
        $flatType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flat Type deleted successfully.',
        ]);
    }
}
