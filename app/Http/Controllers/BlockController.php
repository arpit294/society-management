<?php

namespace App\Http\Controllers;

use App\DataTables\BlocksDataTable;
use App\Models\Block;
use App\Models\Flat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlockController extends Controller
{
    /**
     * Display a listing of the blocks.
     *
     * @return mixed
     */
    public function index(BlocksDataTable $dataTable)
    {
        $blocks = Block::withCount('flats')->get();
        $totalFlats = Block::sum('total_flats');
        $totalActualFlats = Flat::count();

        return $dataTable->render('blocks.index', compact('blocks', 'totalFlats', 'totalActualFlats'));
    }

    /**
     * Show the form for creating a new block.
     *
     * @return View
     */
    public function create()
    {
        return view('blocks.create');
    }

    /**
     * Store a newly created block in storage.
     *
     * @return JsonResponse
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
     * Show the form for editing the specified block.
     *
     * @return View
     */
    public function edit(Block $block)
    {
        return view('blocks.edit', compact('block'));
    }

    /**
     * Update the specified block in storage.
     *
     * @return JsonResponse
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
     * Remove the specified block from storage.
     *
     * @return JsonResponse
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
