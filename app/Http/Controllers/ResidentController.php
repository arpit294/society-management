<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Block;
use App\Models\Flat;
use App\Models\User;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(\App\DataTables\ResidentsDataTable $dataTable)
    {
        return $dataTable->render('residents.index');
    }

    public function create()
    {
        $blocks = Block::all();
        $users = User::all();
        return view('residents.create', compact('blocks', 'users'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:owner,rental',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date|after_or_equal:move_in_date',
        ]);

        Resident::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Resident created successfully.',
        ]);
    }

    public function edit(Resident $resident)
    {
        $blocks = Block::all();
        $users = User::all();
        $flats = Flat::where('block_id', $resident->block_id)->get(); // Load flats for the current block

        return view('residents.edit', compact('resident', 'blocks', 'users', 'flats'));
    }

    public function update(Request $request, Resident $resident)
    {
        $validatedData = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:owner,rental',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date|after_or_equal:move_in_date',
        ]);

        $resident->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Resident updated successfully.',
        ]);
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resident deleted successfully.',
        ]);
    }


    public function getFlatsByBlock($block_id)
    {
        $flats = Flat::where('block_id', $block_id)->get();
        return response()->json($flats);
    }
}
