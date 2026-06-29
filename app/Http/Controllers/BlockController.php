<?php

namespace App\Http\Controllers;

use App\DataTables\BlocksDataTable;
use App\Models\Block;
use App\Models\Flat;
use App\Models\MaintenanceBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BlocksDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('block_view'), 403);
        $blocks = Block::withCount([
            'flats',
            'flats as occupied_flats_count' => function ($query) {
                $query->where('status', 'occupied');
            },
        ])->get();
        $totalFlats = Block::sum('total_flats');
        $totalActualFlats = Flat::count();
        $totalOccupiedFlats = Flat::where('status', 'occupied')->count();

        return $dataTable->render('blocks.index', compact('blocks', 'totalFlats', 'totalActualFlats', 'totalOccupiedFlats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(! \Auth::user()->can('block_create'), 403);
        return view('blocks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('block_create'), 403);
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

    //  Show the form for editing the specified resource.
    public function edit(Block $block)
    {
        abort_if(! \Auth::user()->can('block_edit'), 403);
        return view('blocks.edit', compact('block'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Block $block)
    {
        abort_if(! \Auth::user()->can('block_edit'), 403);
        $validatedData = $request->validate([
            'block_name' => 'required|string|max:255',
            'total_floor' => 'required|integer|min:0',
            'total_flats' => 'required|integer|min:0',
        ]);

        $existingFlats = Flat::where('block_id', $block->id)->count();
        if ($validatedData['total_flats'] < $existingFlats) {
            throw ValidationException::withMessages([
                'total_flats' => ["Total flats cannot be less than the {$existingFlats} flat records already created for this block."],
            ]);
        }

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
        abort_if(! \Auth::user()->can('block_delete'), 403);
        DB::transaction(function () use ($block) {
            // Delete related maintenance bills
            MaintenanceBill::where('block_id', $block->id)->delete();

            // Delete related flats (this will cascade delete residents in DB via foreign key constraints)
            Flat::where('block_id', $block->id)->delete();

            // Delete the block itself
            $block->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Block deleted successfully.',
        ]);
    }
}

//
