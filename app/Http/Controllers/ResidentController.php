<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(\App\DataTables\ResidentsDataTable $dataTable)
    {
        return $dataTable->render('residents.index');
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
}

