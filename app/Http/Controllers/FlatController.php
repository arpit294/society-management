<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlatController extends Controller
{
    public function index()
    {
        $flats = DB::table('flats')->orderByDesc('id')->get();
        return view('flats.index', compact('flats'));
    }

    public function create()
    {
        return view('flats.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'block_id' => ['nullable', 'integer'],
            'flat_no' => ['required', 'string', 'max:255'],
            'floor_no' => ['required', 'string', 'max:255'],
            'flat_type' => ['required', 'string', 'max:255'],
            'maintenance_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        DB::table('flats')->insert(array_merge($validated, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return redirect()->route('flats.index')->with('success', 'Flat created successfully.');
    }

    public function show(string $id)
    {
        abort_if(!$id, 404);

        $flat = DB::table('flats')->where('id', $id)->first();
        abort_unless($flat, 404);

        return view('flats.show', compact('flat'));
    }

    public function edit(string $id)
    {
        $flat = DB::table('flats')->where('id', $id)->first();
        abort_unless($flat, 404);

        return view('flats.edit', compact('flat'));
    }

    public function update(Request $request, string $id)
    {
        $flat = DB::table('flats')->where('id', $id)->first();
        abort_unless($flat, 404);

        $validated = $request->validate([
            'block_id' => ['nullable', 'integer'],
            'flat_no' => ['required', 'string', 'max:255'],
            'floor_no' => ['required', 'string', 'max:255'],
            'flat_type' => ['required', 'string', 'max:255'],
            'maintenance_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        DB::table('flats')
            ->where('id', $id)
            ->update(array_merge($validated, ['updated_at' => now()]));

        return redirect()->route('flats.index')->with('success', 'Flat updated successfully.');
    }

    public function destroy(string $id)
    {
        $flat = DB::table('flats')->where('id', $id)->first();
        abort_unless($flat, 404);

        DB::table('flats')->where('id', $id)->delete();

        return redirect()->route('flats.index')->with('success', 'Flat deleted successfully.');
    }
}
