<?php

namespace App\Http\Controllers;

use App\DataTables\FlatsDatatables;
use App\Models\Block;
use App\Models\Flat;
use Illuminate\Http\Request;

class FlatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FlatsDatatables $dataTable)
    {
        return $dataTable->render('flats.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $blocks = Block::all();
        return view('flats.create', compact('blocks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'block_id' => 'nullable|integer|exists:blocks,id',
            'flat_no' => 'required|string|max:255',
            'floor_no' => 'required|integer|min:0',
            'flat_type' => 'required|string|max:255',
            'maintenance_amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
        ]);

        // Check if a block is selected and ensure the provided floor_no does not exceed the block's total_floor
        if (!empty($validatedData['block_id'])) {
            $block = \App\Models\Block::find($validatedData['block_id']);
            if ($block && $validatedData['floor_no'] > $block->total_floor) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'floor_no' => ['Floor No cannot be greater than ' . $block->total_floor . ' for the selected block.']
                ]);
            }
        }

        Flat::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat created successfully.',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Flat $flat)
    {
        $blocks = Block::all();
        return view('flats.edit', compact('flat', 'blocks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Flat $flat)
    {
        $validatedData = $request->validate([
            'block_id' => 'nullable|integer|exists:blocks,id',
            'flat_no' => 'required|string|max:255',
            'floor_no' => 'required|integer|min:0',
            'flat_type' => 'required|string|max:255',
            'maintenance_amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
        ]);

        // Check if a block is selected and ensure the provided floor_no does not exceed the block's total_floor
        if (!empty($validatedData['block_id'])) {
            $block = Block::find($validatedData['block_id']);
            if ($block && $validatedData['floor_no'] > $block->total_floor) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'floor_no' => ['Floor No cannot be greater than ' . $block->total_floor . ' for the selected block.']
                ]);
            }
        }

        $flat->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat updated successfully.',
        ]);
    }

    /**
     * Remove the specified flat from id
     */
    public function destroy(Flat $flat)
    {
        $flat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flat deleted successfully.',
        ]);
    }
}
