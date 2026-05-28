<?php

namespace App\Http\Controllers;

use App\DataTables\BlocksDataTable;
use App\Models\Block;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BlocksDataTable $dataTable)
    {
        return $dataTable->render('blocks.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blocks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'block_name' => 'required|string|max:255',
            'total_floor' => 'required|integer|min:0',
            'total_flats' => 'required|integer|min:0',
        ]);

        Block::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Block created successfully.',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Block $block)
    {
        return view('blocks.edit', compact('block'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Block $block)
    {
        $validatedData = $request->validate([
            'block_name' => 'required|string|max:255',
            'total_floor' => 'required|integer|min:0',
            'total_flats' => 'required|integer|min:0',
        ]);

        $block->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Block updated successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Block $block)
    {
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => 'Block deleted successfully.',
        ]);
    }
}


//
