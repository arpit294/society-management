<?php

namespace App\Http\Controllers;

use App\DataTables\ResidentsDataTable;
use App\Models\Block;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(ResidentsDataTable $dataTable)
    {
        $blocks = Block::all();

        return $dataTable->render('residents.index', compact('blocks'));
    }

    public function create()
    {
        $blocks = Block::all();
        $flats = Flat::all();
        $users = User::all();

        return view('residents.create', compact('blocks', 'flats', 'users'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'type' => 'required|string|in:owner,rental',
            'user_id' => 'required|exists:users,id',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date',
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
        $flats = Flat::where('block_id', $resident->block_id)->get();
        $users = User::all();

        return view('residents.edit', compact('resident', 'blocks', 'flats', 'users'));
    }

    public function update(Request $request, Resident $resident)
    {
        $validatedData = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'type' => 'required|string|in:owner,rental',
            'user_id' => 'required|exists:users,id',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date',
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
