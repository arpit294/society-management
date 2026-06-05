<?php

namespace App\Http\Controllers;

use App\DataTables\FlatTypesDataTable;
use App\Models\FlatType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlatTypeController extends Controller
{
    /**
     * Display a listing of flat types.
     *
     * @return mixed
     */
    public function index(FlatTypesDataTable $dataTable)
    {
        return $dataTable->render('flat_types.index');
    }

    /**
     * Show the form for creating a new flat type.
     *
     * @return View
     */
    public function create()
    {
        return view('flat_types.create');
    }

    /**
     * Store a newly created flat type in storage.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:flat_types,name',
            'maintenance_fee' => 'required|numeric|min:0',
            'penalty_per_day' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        FlatType::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat Type created successfully.',
        ]);
    }

    /**
     * Show the form for editing the specified flat type.
     *
     * @return View
     */
    public function edit(FlatType $flatType)
    {
        return view('flat_types.edit', compact('flatType'));
    }

    /**
     * Update the specified flat type in storage.
     *
     * @return JsonResponse
     */
    public function update(Request $request, FlatType $flatType)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:flat_types,name,'.$flatType->id,
            'maintenance_fee' => 'required|numeric|min:0',
            'penalty_per_day' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $flatType->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat Type updated successfully.',
        ]);
    }

    /**
     * Remove the specified flat type from storage.
     *
     * @return JsonResponse
     */
    public function destroy(FlatType $flatType)
    {
        $flatType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flat Type deleted successfully.',
        ]);
    }

    
}
