<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(\App\DataTables\ResidentsDataTable $dataTable)
    {
        $blocks = \App\Models\Block::all();
        return $dataTable->render('residents.index', compact('blocks'));
    }

    public function create()
    {
        $blocks = \App\Models\Block::all();
        $flats = \App\Models\Flat::all();
        $users = \App\Models\User::all();
        return view('residents.create', compact('blocks', 'flats', 'users'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validatedData = $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'type' => 'required|string|in:owner,rental',
            'user_id' => 'required|exists:users,id',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date',
        ]);

        \App\Models\Resident::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Resident created successfully.',
        ]);
    }

    public function edit(\App\Models\Resident $resident)
    {
        $blocks = \App\Models\Block::all();
        $flats = \App\Models\Flat::where('block_id', $resident->block_id)->get();
        $users = \App\Models\User::all();
        return view('residents.edit', compact('resident', 'blocks', 'flats', 'users'));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Resident $resident)
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

    public function destroy(\App\Models\Resident $resident)
    {
        $resident->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resident deleted successfully.',
        ]);
    }

    public function getFlatsByBlock($block_id)
    {
        $flats = \App\Models\Flat::where('block_id', $block_id)->get();
        return response()->json($flats);
    }
}

