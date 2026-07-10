<?php

namespace App\Http\Controllers;

use App\DataTables\BlocksDataTable;
use App\Models\Block;
use App\Models\Flat;
use App\Models\MaintenanceBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class BlockController extends Controller
{
    /**
     * Handle common exceptions and return appropriate responses.
     *
     * @param \Exception $e
     * @param string $methodName
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    private function handleException(\Exception $e, string $methodName)
    {
        if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
            throw $e; // Re-throw specific HTTP or validation exceptions
        }

        Log::error("Error in BlockController@{$methodName}: " . $e->getMessage());

        $errorMessage = 'An unexpected error occurred. Please try again.';
        if (config('app.debug')) {
            $errorMessage = 'An error occurred: ' . $e->getMessage();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }

        return redirect()->back()->with('error', $errorMessage);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\DataTables\BlocksDataTable $dataTable
     * @return \Illuminate\Http\Response
     */
    public function index(BlocksDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('block_view'), 403, 'Unauthorized access.');

        try {
            $blocks = Block::withCount([
                'flats',
                'flats as occupied_flats_count' => function ($query) {
                    $query->where('status', config('status.flats.occupied'));
                },
            ])->get();

            $totalFlats = Block::sum('total_flats');
            $totalActualFlats = Flat::count();
            $totalOccupiedFlats = Flat::where('status', config('status.flats.occupied'))->count();

            return $dataTable->render('blocks.index', compact('blocks', 'totalFlats', 'totalActualFlats', 'totalOccupiedFlats'));
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_if(! \Auth::user()->can('block_create'), 403, 'Unauthorized access.');

        try {
            return view('blocks.create');
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('block_create'), 403, 'Unauthorized access.');

        try {
            $validatedData = $request->validate([
                'block_name' => 'required|string|max:255',
                'block_type' => 'nullable|string|max:255',
                'label_type' => 'nullable|string|max:255',
                'total_floor' => 'nullable|integer|min:0',
                'total_flats' => 'required|integer|min:0',
            ]);

            $validatedData['block_type'] = $validatedData['block_type'] ?? 'residential_tower';
            $validatedData['label_type'] = $validatedData['label_type'] ?? 'Wing';
            $validatedData['total_floor'] = $validatedData['total_floor'] ?? 0;

            Block::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Block created successfully.',
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Block $block
     * @return \Illuminate\Http\Response
     */
    public function edit(Block $block)
    {
        abort_if(! \Auth::user()->can('block_edit'), 403, 'Unauthorized access.');

        try {
            return view('blocks.edit', compact('block'));
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Block $block
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Block $block)
    {
        abort_if(! \Auth::user()->can('block_edit'), 403, 'Unauthorized access.');

        try {
            $validatedData = $request->validate([
                'block_name' => 'required|string|max:255',
                'block_type' => 'nullable|string|max:255',
                'label_type' => 'nullable|string|max:255',
                'total_floor' => 'nullable|integer|min:0',
                'total_flats' => 'required|integer|min:0',
            ]);

            $validatedData['block_type'] = $validatedData['block_type'] ?? 'residential_tower';
            $validatedData['label_type'] = $validatedData['label_type'] ?? 'Wing';
            $validatedData['total_floor'] = $validatedData['total_floor'] ?? 0;

            $existingFlatsCount = Flat::where('block_id', $block->id)->count();
            if ($validatedData['total_flats'] < $existingFlatsCount) {
                throw ValidationException::withMessages([
                    'total_flats' => ["Total flats cannot be less than the {$existingFlatsCount} flat records already created for this block."],
                ]);
            }

            $block->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Block updated successfully.',
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Block $block
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Block $block)
    {
        abort_if(! \Auth::user()->can('block_delete'), 403, 'Unauthorized access.');

        try {
            DB::transaction(function () use ($block) {
                // Delete related maintenance bills first
                MaintenanceBill::where('block_id', $block->id)->delete();

                // Delete related flats (this will cascade delete residents via foreign key constraints)
                Flat::where('block_id', $block->id)->delete();

                // Finally, delete the block itself
                $block->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Block deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }
}
