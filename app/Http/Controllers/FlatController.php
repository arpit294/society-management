<?php

namespace App\Http\Controllers;

use App\DataTables\FlatsDatatables;
use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FlatController extends Controller
{
    /**
     * Display a listing of flats.
     *
     * @return mixed
     */
    public function index(FlatsDatatables $dataTable)
    {
        return $dataTable->render('flats.index');
    }

    /**
     * Show the form for creating a new flat.
     *
     * @return View
     */
    public function create()
    {
        $blocks = Block::all();
        $flatTypes = FlatType::where('status', 'active')->get();

        return view('flats.create', compact('blocks', 'flatTypes'));
    }

    /**
     * Store a newly created flat in storage.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'block_id' => 'nullable|integer|exists:blocks,id',
            'flat_no' => 'required|string|max:255',
            'floor_no' => 'required|integer|min:0',
            'flat_type_id' => 'required|integer|exists:flat_types,id',
            'status' => 'required|string|max:255',
        ]);

        // Check if a block is selected and ensure the provided floor_no does not exceed the block's total_floor
        if (! empty($validatedData['block_id'])) {
            $block = Block::find($validatedData['block_id']);
            if ($block && $validatedData['floor_no'] > $block->total_floor) {
                throw ValidationException::withMessages([
                    'floor_no' => ['Floor No cannot be greater than '.$block->total_floor.' for the selected block.'],
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
     * Show the form for editing the specified flat.
     *
     * @return View
     */
    public function edit(Flat $flat)
    {
        $blocks = Block::all();
        $flatTypes = FlatType::all();

        return view('flats.edit', compact('flat', 'blocks', 'flatTypes'));
    }

    /**
     * Update the specified flat in storage.
     *
     * @return JsonResponse
     */
    public function update(Request $request, Flat $flat)
    {
        $validatedData = $request->validate([
            'block_id' => 'nullable|integer|exists:blocks,id',
            'flat_no' => 'required|string|max:255',
            'floor_no' => 'required|integer|min:0',
            'flat_type_id' => 'required|integer|exists:flat_types,id',
            'status' => 'required|string|max:255',
        ]);

        // Check if a block is selected and ensure the provided floor_no does not exceed the block's total_floor
        if (! empty($validatedData['block_id'])) {
            $block = Block::find($validatedData['block_id']);
            if ($block && $validatedData['floor_no'] > $block->total_floor) {
                throw ValidationException::withMessages([
                    'floor_no' => ['Floor No cannot be greater than '.$block->total_floor.' for the selected block.'],
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
     * Remove the specified flat from storage.
     *
     * @return JsonResponse
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
