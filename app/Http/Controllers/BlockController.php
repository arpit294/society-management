<?php

namespace App\Http\Controllers;

use App\DataTables\BlocksDataTable;
use App\Helpers\ModuleHelper;
use App\Models\Block;
use App\Models\Complain;
use App\Models\Flat;
use App\Models\FlatDocument;
use App\Models\MaintenanceBill;
use App\Models\NameTransferBill;
use App\Models\Setting;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
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
     * @param BlocksDataTable $dataTable
     * @return Response
     */
    public function index(BlocksDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('block_view'), 403, 'Unauthorized access.');

        try {
            Flat::syncAllOccupancyStatus();

            $blocks = Block::withCount([
                'flats',
                'flats as occupied_flats_count' => function ($query) {
                    $query->where('status', config('status.flats.occupied'));
                },
            ])->get();

            foreach ($blocks as $block) {
                if ($block->flats_count > $block->total_flats) {
                    $block->total_flats = $block->flats_count;
                    $block->saveQuietly();
                }
            }

            $totalFlats = Block::sum('total_flats');
            $totalActualFlats = Flat::count();
            if ($totalFlats < $totalActualFlats) {
                $totalFlats = $totalActualFlats;
            }
            $totalOccupiedFlats = Flat::where('status', config('status.flats.occupied'))->count();

            return $dataTable->render('blocks.index', compact('blocks', 'totalFlats', 'totalActualFlats', 'totalOccupiedFlats'));
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
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
     * @param Request $request
     * @return JsonResponse
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

            $blockLabel = \App\Models\Setting::label('block', 'Block');
            return response()->json([
                'success' => true,
                'message' => "{$blockLabel} created successfully.",
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Block $block
     * @return Response
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
     * @param Request $request
     * @param Block $block
     * @return JsonResponse
     * @throws ValidationException
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
                $unitPlural = strtolower(\App\Models\Setting::label('unit_plural', 'flats'));
                $blockLabel = strtolower(\App\Models\Setting::label('block', 'block'));
                throw ValidationException::withMessages([
                    'total_flats' => ["Total {$unitPlural} capacity cannot be less than the {$existingFlatsCount} {$unitPlural} records already created for this {$blockLabel}."],
                ]);
            }

            $block->update($validatedData);

            $blockLabel = \App\Models\Setting::label('block', 'Block');
            return response()->json([
                'success' => true,
                'message' => "{$blockLabel} updated successfully.",
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Block $block
     * @return JsonResponse
     */
    public function destroy(Block $block)
    {
        abort_if(! \Auth::user()->can('block_delete'), 403, 'Unauthorized access.');

        try {
            DB::transaction(function () use ($block) {
                $flatIds = Flat::where('block_id', $block->id)->pluck('id');
                if ($flatIds->isNotEmpty()) {
                    Complain::whereIn('flat_id', $flatIds)->delete();
                    FlatDocument ::whereIn('flat_id', $flatIds)->delete();
                    if (ModuleHelper::isFinanceActive() && ModuleHelper::hasModel(\App\Models\NameTransferBill::class, 'name_transfer_bills')) {
                        NameTransferBill::whereIn('flat_id', $flatIds)->delete();
                    }
                    \App\Models\Resident::whereIn('flat_id', $flatIds)->delete();
                }
                if (ModuleHelper::isFinanceActive() &&  ModuleHelper::hasModel(MaintenanceBill::class, 'maintenance_bills')) {
                    MaintenanceBill::where('block_id', $block->id)->delete();
                }
                Flat::where('block_id', $block->id)->delete();
                $block->delete();
            });

            $blockLabel = Setting::label('block', 'Block');
            return response()->json([
                'success' => true,
                'message' => "{$blockLabel} deleted successfully.",
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }
}