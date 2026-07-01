<?php

namespace App\Http\Controllers;

use App\DataTables\FlatsDatatables;
use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\NameTransferBill;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FlatController extends Controller
{
    private function ensureBlockHasFlatCapacity(int $blockId, ?int $ignoreFlatId = null): void
    {
        $block = Block::find($blockId);

        if (! $block || $block->total_flats <= 0) {
            return;
        }

        $flatCountQuery = Flat::where('block_id', $block->id);

        if ($ignoreFlatId) {
            $flatCountQuery->where('id', '!=', $ignoreFlatId);
        }

        if ($flatCountQuery->count() >= $block->total_flats) {
            throw ValidationException::withMessages([
                'block_id' => ["Block {$block->block_name} already has the maximum {$block->total_flats} flats."],
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(FlatsDatatables $dataTable)
    {
        abort_if(! \Auth::user()->can('flat_view'), 403);
        try {
            $blocks = Block::all();

            return $dataTable->render('flats.index', compact('blocks'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(! \Auth::user()->can('flat_create'), 403);
        try {
            // Get all blocks and flat types to populate the dropdowns in the form
            $blocks = Block::all();
            // Only get active flat types for the dropdown
            $flatTypes = FlatType::where('status', config('status.general.active'))->get();

            return view('flats.create', compact('blocks', 'flatTypes'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        abort_if(! \Auth::user()->can('flat_create'), 403);
        try {
            $validatedData = $request->validate([
                'block_id' => 'required|integer|exists:blocks,id',
                'flat_no' => 'required|string|max:255',
                'floor_no' => 'required|integer|min:0',
                'flat_type_id' => 'required|integer|exists:flat_types,id',
                'status' => 'required|string|max:255',
            ]);

            // Check if a block is selected and ensure the provided floor_no does not exceed the block's total_floor
            if (! empty($validatedData['block_id'])) {
                $block = Block::find($validatedData['block_id']);
                if ($block && $validatedData['floor_no'] > $block->total_floor) {
                    throw ValidationException::withMessages([
                        'floor_no' => ['Floor No cannot be greater than ' . $block->total_floor . ' for the selected block.'],
                    ]);
                }

                $this->ensureBlockHasFlatCapacity((int) $validatedData['block_id']);
            }

            Flat::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Flat created successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@store: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Flat $flat)
    {
        abort_if(! \Auth::user()->can('flat_view'), 403);
        try {
            $history = Resident::with('user')
                ->where('flat_id', $flat->id)
                ->orderBy('move_in_date', 'desc')
                ->get();

            return view('flats.history', compact('flat', 'history'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@show: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Flat $flat)
    {
        abort_if(! \Auth::user()->can('flat_edit'), 403);
        try {
            $blocks = Block::all();
            $flatTypes = FlatType::all();

            return view('flats.edit', compact('flat', 'blocks', 'flatTypes'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@edit: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Flat $flat)
    {
        abort_if(! \Auth::user()->can('flat_edit'), 403);
        try {
            $validatedData = $request->validate([
                'block_id' => 'required|integer|exists:blocks,id',
                'flat_no' => 'required|string|max:255',
                'floor_no' => 'required|integer|min:0',
                'flat_type_id' => 'required|integer|exists:flat_types,id',
                'status' => 'required|string|max:255',
            ]);

            // Check the selected floor number is valid or not based on block table
            if (! empty($validatedData['block_id'])) {
                $block = Block::find($validatedData['block_id']);
                if ($block && $validatedData['floor_no'] > $block->total_floor) {
                    throw ValidationException::withMessages([
                        'floor_no' => ['Floor No cannot be greater than ' . $block->total_floor . ' for the selected block.'],
                    ]);
                }

                $this->ensureBlockHasFlatCapacity((int) $validatedData['block_id'], $flat->id);
            }

            $flat->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Flat updated successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@update: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified flat from id
     */
    public function destroy(Flat $flat)
    {
        abort_if(! \Auth::user()->can('flat_delete'), 403);
        try {
            $flat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Flat deleted successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function transferCreate(Flat $flat)
    {
        abort_if(! \Auth::user()->can('flat_edit'), 403);
        try {
            $currentOwner = Resident::with('user')
                ->where('flat_id', $flat->id)
                ->where('type', 'owner')
                ->where(function ($q) {
                    $q->whereNull('move_out_date')
                        ->orWhere('move_out_date', '>=', now()->startOfDay());
                })
                ->orderByRaw('move_out_date IS NOT NULL') // nulls first
                ->latest('move_in_date')
                ->first();

            // if there's no owner, they should just add an owner via Resident features
            if (! $currentOwner) {
                return response('<div class="p-4 text-center text-danger">This flat does not currently have an active owner to transfer from.</div>');
            }

            return view('flats.transfer', compact('flat', 'currentOwner'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@transferCreate: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function transferStore(Request $request, Flat $flat)
    {
        abort_if(! \Auth::user()->can('flat_edit'), 403);
        try {
            if ($request->has('transaction_id')) {
                $request->merge([
                    'transaction_id' => $request->input('payment_method') === 'upi'
                        ? trim((string) $request->input('transaction_id'))
                        : null,
                ]);
            }

            $validatedData = $request->validate([
                'new_owner_name' => 'required|string|max:255',
                'new_owner_email' => 'required|email',
                'new_owner_phone' => 'nullable|string|max:20',
                'new_owner_aadhar' => 'required|digits:12',
                'transfer_date' => 'required|date',
                'payment_method' => 'required|in:pending,cash,upi',
                'transaction_id' => [
                    'nullable',
                    'required_if:payment_method,upi',
                    'digits:12',
                    Rule::unique('maintenance_bills', 'transaction_id'),
                    Rule::unique('name_transfer_bills', 'transaction_id'),
                    Rule::unique('prepaid_maintenances', 'transaction_id'),
                ],
                'payment_slip' => 'nullable|required_if:payment_method,upi|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ], [
                'transaction_id.required_if' => 'The UTR number is required for UPI payments.',
                'transaction_id.digits' => 'The UTR number must be exactly 12 digits.',
                'transaction_id.unique' => 'This UTR number has already been used.',
            ], [
                'transaction_id' => 'UTR number',
            ]);

            DB::beginTransaction();
            try {
                // 1. End current owner's residency
                $currentOwner = Resident::where('flat_id', $flat->id)
                    ->where('type', 'owner')
                    ->where(function ($q) {
                        $q->whereNull('move_out_date')
                            ->orWhere('move_out_date', '>=', now()->startOfDay());
                    })
                    ->first();

                if (! $currentOwner) {
                    throw new \Exception('No active owner found.');
                }

                // 1. Create or find new user
                $newUser = User::firstOrCreate(
                    ['email' => $validatedData['new_owner_email']],
                    [
                        'name' => $validatedData['new_owner_name'],
                        'phone' => $validatedData['new_owner_phone'],
                        'aadhar_id' => $validatedData['new_owner_aadhar'],
                        'password' => Hash::make('password123'),
                        'role' => 'owner',
                        'status' => config('status.general.active'),
                    ]
                );

                // 2. Generate Name Transfer Request (Bill)
                $settings = Setting::getAll();
                $fee = isset($settings['name_transfer_fee']) ? (float) $settings['name_transfer_fee'] : 0;

                $status = $validatedData['payment_method'] === 'pending' ? config('status.name_transfer_bills.pending') : config('status.name_transfer_bills.paid');
                if ($fee == 0) {
                    $status = config('status.name_transfer_bills.paid'); // Automatically paid if no fee
                }

                $billData = [
                    'flat_id' => $flat->id,
                    'old_owner_id' => $currentOwner->user_id,
                    'new_owner_id' => $newUser->id,
                    'amount' => $fee,
                    'transfer_date' => $validatedData['transfer_date'],
                    'status' => $status,
                    'is_approved' => false,
                ];

                if ($status === config('status.name_transfer_bills.paid') && $fee > 0) {
                    $billData['paid_at'] = now();
                    $billData['payment_method'] = $validatedData['payment_method'];

                    if ($validatedData['payment_method'] === 'upi') {
                        $billData['transaction_id'] = $validatedData['transaction_id'] ?? null;

                        if ($request->hasFile('payment_slip')) {
                            $file = $request->file('payment_slip');
                            $filename = time() . '_' . $file->getClientOriginalName();
                            $file->move(public_path('uploads/invoices'), $filename);
                            $billData['payment_slip'] = $filename;
                        }
                    }
                }

                NameTransferBill::create($billData);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Request sent successfully. Waiting for approval.',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@transferStore: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error transferring ownership: ' . $e->getMessage(),
            ], 500);
        }
    }
}
