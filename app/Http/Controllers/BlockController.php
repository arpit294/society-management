<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlockController extends Controller
{
    public function index()
    {
        $blocks = DB::table('blocks')->orderByDesc('id')->get();
        return view('blocks.index', compact('blocks'));
    }

    public function create()
    {
        return view('blocks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'block_name' => ['required', 'string', 'max:255'],
            'total_floor' => ['required', 'integer', 'min:0'],
            'total_flats' => ['required', 'integer', 'min:0'],
        ]);

        DB::table('blocks')->insert([
            ...$validated,
            'created_at' => now(),
            // migration has no updated_at column, so we don't set it
        ]);

        return redirect()->route('blocks.index')->with('success', 'Block created successfully.');
    }

    public function show(string $id)
    {
        $block = DB::table('blocks')->where('id', $id)->first();
        abort_unless($block, 404);

        return view('blocks.show', compact('block'));
    }

    public function edit(string $id)
    {
        $block = DB::table('blocks')->where('id', $id)->first();
        abort_unless($block, 404);

        return view('blocks.edit', compact('block'));
    }

    public function update(Request $request, string $id)
    {
        $block = DB::table('blocks')->where('id', $id)->first();
        abort_unless($block, 404);

        $validated = $request->validate([
            'block_name' => ['required', 'string', 'max:255'],
            'total_floor' => ['required', 'integer', 'min:0'],
            'total_flats' => ['required', 'integer', 'min:0'],
        ]);

        DB::table('blocks')
            ->where('id', $id)
            ->update($validated);

        return redirect()->route('blocks.index')->with('success', 'Block updated successfully.');
    }

    public function destroy(string $id, Request $request)
    {
        $block = DB::table('blocks')->where('id', $id)->first();
        abort_unless($block, 404);

        DB::table('blocks')->where('id', $id)->delete();

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Block deleted successfully.',
                'id' => $id,
            ]);
        }

        return redirect()->route('blocks.index')->with('success', 'Block deleted successfully.');
    }
}
