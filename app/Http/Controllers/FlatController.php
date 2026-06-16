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
use Illuminate\Validation\ValidationException;

class FlatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FlatsDatatables $dataTable)
    {
        $blocks = Block::all();
        return $dataTable->render('flats.index', compact('blocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all blocks and flat types to populate the dropdowns in the form
        $blocks = Block::all();
        // Only get active flat types for the dropdown
        $flatTypes = FlatType::where('status', 'active')->get();
        return view('flats.create', compact('blocks', 'flatTypes'));
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'block_id' => 'nullable|integer|exists:blocks,id',
            'flat_no' => 'required|string|max:255',
            'floor_no' => 'required|integer|min:0',
            'flat_type_id' => 'required|integer|exists:flat_types,id',
            'status' => 'required|string|max:255',
        ]);

        // Check if a block is selected and ensure the provided floor_no does not exceed the block's total_floor
        if (!empty($validatedData['block_id'])) {
            $block = Block::find($validatedData['block_id']);
            if ($block && $validatedData['floor_no'] > $block->total_floor) {
                throw ValidationException::withMessages([
                    'floor_no' => ['Floor No cannot be greater than ' . $block->total_floor . ' for the selected block.']
                ]);
            }
        }

        Flat::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat created successfully.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Flat $flat)
    {
        $history = Resident::with('user')
            ->where('flat_id', $flat->id)
            ->orderBy('move_in_date', 'desc')
            ->get();

        return view('flats.history', compact('flat', 'history'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Flat $flat)
    {
        $blocks = Block::all();
        $flatTypes = FlatType::all();
        return view('flats.edit', compact('flat', 'blocks', 'flatTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Flat $flat)
    {
        $validatedData = $request->validate([
            'block_id' => 'nullable|integer|exists:blocks,id',
            'flat_no' => 'required|string|max:255',
            'floor_no' => 'required|integer|min:0',
            'flat_type_id' => 'required|integer|exists:flat_types,id',
            'status' => 'required|string|max:255',
        ]);

        // Check the selected floor number is valid or not based on block table
        if (!empty($validatedData['block_id'])) {
            $block = Block::find($validatedData['block_id']);
            if ($block && $validatedData['floor_no'] > $block->total_floor) {
                throw ValidationException::withMessages([
                    'floor_no' => ['Floor No cannot be greater than ' . $block->total_floor . ' for the selected block.']
                ]);
            }
        }

        $flat->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Flat updated successfully.',
        ]);
    }

    /**
     * Remove the specified flat from id
     */
    public function destroy(Flat $flat)
    {
        $flat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flat deleted successfully.',
        ]);
    }

    public function transferCreate(Flat $flat)
    {
        $currentOwner = Resident::with('user')
            ->where('flat_id', $flat->id)
            ->where('type', 'owner')
            ->where(function ($q) {
                $q->whereNull('move_out_date')
                  ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
            ->first();

        // if there's no owner, they should just add an owner via Resident features
        if (!$currentOwner) {
            return response('<div class="p-4 text-center text-danger">This flat does not currently have an active owner to transfer from.</div>');
        }

        return view('flats.transfer', compact('flat', 'currentOwner'));
    }

    public function transferStore(Request $request, Flat $flat)
    {
        $validatedData = $request->validate([
            'new_owner_name' => 'required|string|max:255',
            'new_owner_email' => 'required|email',
            'new_owner_phone' => 'nullable|string|max:20',
            'new_owner_aadhar' => 'required|string|max:20',
            'transfer_date' => 'required|date',
            'payment_method' => 'required|in:pending,cash,upi',
            'transaction_id' => 'nullable|string|max:255',
            'payment_slip' => 'nullable|required_if:payment_method,upi|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. End current owner's residency
            $currentOwner = Resident::where('flat_id', $flat->id)
                ->where('type', 'owner')
                ->where(function ($q) {
                    $q->whereNull('move_out_date')
                      ->orWhere('move_out_date', '>=', now()->startOfDay());
                })
                ->first();

            if (!$currentOwner) {
                throw new \Exception('No active owner found.');
            }

            // 1. Create or find new user
            $newUser = User::firstOrCreate(
                ['email' => $validatedData['new_owner_email']],
                [
                    'name' => $validatedData['new_owner_name'],
                    'phone' => $validatedData['new_owner_phone'],
                    'aadhar_id' => $validatedData['new_owner_aadhar'],
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'role' => 'owner',
                    'status' => 'active'
                ]
            );

            // 2. Generate Name Transfer Request (Bill)
            $settings = Setting::pluck('value', 'key');
            $fee = isset($settings['name_transfer_fee']) ? (float)$settings['name_transfer_fee'] : 0;

            $status = $validatedData['payment_method'] === 'pending' ? 'pending' : 'paid';
            if ($fee == 0) {
                $status = 'paid'; // Automatically paid if no fee
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

            if ($status === 'paid' && $fee > 0) {
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
                'message' => 'Ownership transferred successfully.',
            ]);
        } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                'success' => false,
                'message' => 'Error transferring ownership: ' . $e->getMessage(),
            ], 500);
        }
    }
}
