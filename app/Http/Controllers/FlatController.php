<?php

namespace App\Http\Controllers;

use App\DataTables\FlatsDatatables;
use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\MaintenanceBill;
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
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
            $flatTypes = FlatType::where('status', config('status.general.active'))->get();
            $globalBillingMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');

            return $dataTable->render('flats.index', compact('blocks', 'flatTypes', 'globalBillingMethod'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public static function syncDefaultFlatTypes()
    {
        $allDefaults = [
            // Flat / Apartment Categories
            ['name' => '1 BHK', 'owner_maintenance_fee' => 1000, 'rental_maintenance_fee' => 1500, 'category_type' => 'flat', 'status' => 'active', 'description' => '1 Bedroom, Hall, Kitchen'],
            ['name' => '2 BHK', 'owner_maintenance_fee' => 1500, 'rental_maintenance_fee' => 2000, 'category_type' => 'flat', 'status' => 'active', 'description' => '2 Bedroom, Hall, Kitchen'],
            ['name' => '3 BHK', 'owner_maintenance_fee' => 2500, 'rental_maintenance_fee' => 3000, 'category_type' => 'flat', 'status' => 'active', 'description' => '3 Bedroom, Hall, Kitchen'],
            ['name' => '4 BHK', 'owner_maintenance_fee' => 3500, 'rental_maintenance_fee' => 4500, 'category_type' => 'flat', 'status' => 'active', 'description' => '4 Bedroom, Hall, Kitchen'],
            ['name' => '5 BHK', 'owner_maintenance_fee' => 5000, 'rental_maintenance_fee' => 6000, 'category_type' => 'flat', 'status' => 'active', 'description' => '5 Bedroom, Hall, Kitchen'],
            ['name' => 'Flat / Apartment', 'owner_maintenance_fee' => 2000, 'rental_maintenance_fee' => 2500, 'category_type' => 'flat', 'status' => 'active', 'description' => 'Standard Flat / Apartment'],
            ['name' => 'Studio Apartment', 'owner_maintenance_fee' => 800, 'rental_maintenance_fee' => 1200, 'category_type' => 'flat', 'status' => 'active', 'description' => 'Studio Unit / 1 RK'],
            
            // Other Residential Unit Types
            ['name' => 'Penthouse', 'owner_maintenance_fee' => 6000, 'rental_maintenance_fee' => 7500, 'category_type' => 'penthouse', 'status' => 'active', 'description' => 'Luxury Penthouse Unit'],
            ['name' => 'Duplex', 'owner_maintenance_fee' => 4500, 'rental_maintenance_fee' => 5500, 'category_type' => 'duplex', 'status' => 'active', 'description' => 'Multi-level Duplex Unit'],
            ['name' => 'Villa / Bungalow', 'owner_maintenance_fee' => 4000, 'rental_maintenance_fee' => 5000, 'category_type' => 'villa', 'status' => 'active', 'description' => 'Independent Villa / Bungalow'],
            ['name' => 'Row House', 'owner_maintenance_fee' => 3000, 'rental_maintenance_fee' => 3800, 'category_type' => 'rowhouse', 'status' => 'active', 'description' => 'Row House Unit'],
            ['name' => 'Tenement', 'owner_maintenance_fee' => 2000, 'rental_maintenance_fee' => 2500, 'category_type' => 'tenement', 'status' => 'active', 'description' => 'Tenement Unit'],
            ['name' => 'Plot / Land', 'owner_maintenance_fee' => 1000, 'rental_maintenance_fee' => 1200, 'category_type' => 'plot', 'status' => 'active', 'description' => 'Open Plot / Land'],

            // Commercial Categories
            ['name' => 'Commercial Shop', 'owner_maintenance_fee' => 1500, 'rental_maintenance_fee' => 2000, 'category_type' => 'shop', 'status' => 'active', 'description' => 'Commercial Shop'],
            ['name' => 'Office Space', 'owner_maintenance_fee' => 2500, 'rental_maintenance_fee' => 3000, 'category_type' => 'office', 'status' => 'active', 'description' => 'Commercial Office Space'],
            ['name' => 'Showroom', 'owner_maintenance_fee' => 5000, 'rental_maintenance_fee' => 6000, 'category_type' => 'showroom', 'status' => 'active', 'description' => 'Retail Showroom'],
            ['name' => 'Warehouse / Godown', 'owner_maintenance_fee' => 3500, 'rental_maintenance_fee' => 4000, 'category_type' => 'warehouse', 'status' => 'active', 'description' => 'Storage Warehouse / Godown'],
        ];

        if (FlatType::count() === 0) {
            foreach ($allDefaults as $item) {
                FlatType::create($item);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(! \Auth::user()->can('flat_create'), 403);
        try {
            self::syncDefaultFlatTypes();
            // Get all blocks and flat types to populate the dropdowns in the form
            $blocks = Block::all();
            // Only get active flat types for the dropdown
            $flatTypes = FlatType::where('status', config('status.general.active'))->get();
            $globalBillingMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
            $structureType = \App\Models\Setting::get('society_property_type', 'flat_residential');

            return view('flats.create', compact('blocks', 'flatTypes', 'globalBillingMethod', 'structureType'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            $globalMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
            $validatedData = $request->validate([
                'block_id' => 'required|integer|exists:blocks,id',
                'unit_type' => 'nullable|string|max:255',
                'flat_no' => ['required', 'string', 'max:255', Rule::unique('flats', 'flat_no')->where('block_id', $request->block_id)],
                'floor_no' => 'nullable|integer|min:0',
                'flat_type_id' => ($globalMethod === 'per_sqft' || in_array(strtolower($request->unit_type ?? ''), ['shop', 'office', 'showroom', 'warehouse'])) ? 'nullable|integer|exists:flat_types,id' : 'required|integer|exists:flat_types,id',
                'area_sqft' => 'nullable|numeric|min:0',
                'status' => 'required|string|max:255',
            ]);

            $validatedData['unit_type'] = $validatedData['unit_type'] ?? 'flat';
            if (in_array(strtolower($validatedData['unit_type']), ['villa', 'rowhouse', 'row_house', 'plot', 'bungalow'])) {
                $validatedData['floor_no'] = 0;
            } else {
                $validatedData['floor_no'] = $validatedData['floor_no'] ?? 0;
            }

            $globalMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
            $flatTypeObj = FlatType::find($validatedData['flat_type_id'] ?? null);
            $calcMethod = $flatTypeObj ? ($flatTypeObj->calculation_method ?? 'fixed') : 'fixed';
            $isPerSqft = ($calcMethod === 'per_sqft' || $calcMethod === 'hybrid' || $globalMethod === 'per_sqft');

            if ($isPerSqft && (empty($validatedData['area_sqft']) || (float) $validatedData['area_sqft'] <= 0)) {
                throw ValidationException::withMessages([
                    'area_sqft' => ['Carpet Area (Sq. Ft.) is mandatory and must be greater than 0 because Carpet Area Based maintenance billing is active (' . ($calcMethod === 'per_sqft' || $calcMethod === 'hybrid' ? 'Category Level' : 'Global Level') . '). Otherwise area 0 hone par bill 0 ban jayega.'],
                ]);
            }

            // Check if a block is selected and ensure the provided floor_no does not exceed the block's total_floor (if block has floors)
            if (! empty($validatedData['block_id'])) {
                $block = Block::find($validatedData['block_id']);
                if ($block && $block->total_floor > 0 && $validatedData['floor_no'] > $block->total_floor) {
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
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            $flat->load('block');
            $history = Resident::with('user')
                ->where('flat_id', $flat->id)
                ->orderBy('move_in_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();


            return view('flats.history', compact('flat', 'history'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            self::syncDefaultFlatTypes();
            $blocks = Block::all();
            $flatTypes = FlatType::all();
            $globalBillingMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
            $structureType = \App\Models\Setting::get('society_property_type', 'flat_residential');

            return view('flats.edit', compact('flat', 'blocks', 'flatTypes', 'globalBillingMethod', 'structureType'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            $globalMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
            $validatedData = $request->validate([
                'block_id' => 'required|integer|exists:blocks,id',
                'unit_type' => 'nullable|string|max:255',
                'flat_no' => ['required', 'string', 'max:255', Rule::unique('flats', 'flat_no')->where('block_id', $request->block_id)->ignore($flat->id)],
                'floor_no' => 'nullable|integer|min:0',
                'flat_type_id' => ($globalMethod === 'per_sqft' || in_array(strtolower($request->unit_type ?? ''), ['shop', 'office', 'showroom', 'warehouse'])) ? 'nullable|integer|exists:flat_types,id' : 'required|integer|exists:flat_types,id',
                'area_sqft' => 'nullable|numeric|min:0',
                'status' => 'required|string|max:255',
            ]);

            $validatedData['unit_type'] = $validatedData['unit_type'] ?? 'flat';
            if (in_array(strtolower($validatedData['unit_type']), ['villa', 'rowhouse', 'row_house', 'plot', 'bungalow'])) {
                $validatedData['floor_no'] = 0;
            } else {
                $validatedData['floor_no'] = $validatedData['floor_no'] ?? 0;
            }

            $globalMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
            $flatTypeObj = FlatType::find($validatedData['flat_type_id'] ?? null);
            $calcMethod = $flatTypeObj ? ($flatTypeObj->calculation_method ?? 'fixed') : 'fixed';
            $isPerSqft = ($calcMethod === 'per_sqft' || $calcMethod === 'hybrid' || $globalMethod === 'per_sqft');

            if ($isPerSqft && (empty($validatedData['area_sqft']) || (float) $validatedData['area_sqft'] <= 0)) {
                throw ValidationException::withMessages([
                    'area_sqft' => ['Carpet Area (Sq. Ft.) is mandatory and must be greater than 0 because Carpet Area Based maintenance billing is active (' . ($calcMethod === 'per_sqft' || $calcMethod === 'hybrid' ? 'Category Level' : 'Global Level') . '). Otherwise area 0 hone par bill 0 ban jayega.'],
                ]);
            }

            // Check the selected floor number is valid or not based on block table (if block has floors)
            if (! empty($validatedData['block_id'])) {
                $block = Block::find($validatedData['block_id']);
                if ($block && $block->total_floor > 0 && $validatedData['floor_no'] > $block->total_floor) {
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
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            $flat->load('block');
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

            $pendingBills = \App\Models\MaintenanceBill::with('maintenance')
                ->where('flat_id', $flat->id)
                ->where('status', '!=', config('status.maintenance_bills.paid'))
                ->get();

            $settings = \App\Models\Setting::getAll();
            $defaultFee = isset($settings['name_transfer_fee']) ? (float) $settings['name_transfer_fee'] : 0;
            
            $users = \App\Models\User::where('status', 1)->get();

            return view('flats.transfer', compact('flat', 'currentOwner', 'pendingBills', 'defaultFee', 'users'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in FlatController@transferCreate: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Pay all pending maintenance dues for a flat
    public function payPendingDues(Request $request, Flat $flat)
    {
        abort_if(! \Auth::user()->can('maintenance_bill_create'), 403);
        try {
            $request->validate([
                'payment_method' => 'required|string',
                'transaction_id' => 'nullable|string',
            ]);

            $pendingBills = MaintenanceBill::with('resident', 'flat.flatType', 'maintenance')
                ->where('flat_id', $flat->id)
                ->where('status', '!=', config('status.maintenance_bills.paid'))
                ->get();

            if ($pendingBills->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No pending maintenance bills found for this flat.'], 400);
            }

            $batchId = uniqid('pay_');
            foreach ($pendingBills as $maintenanceBill) {
                // Capture dynamically calculated amounts before changing status to paid
                $currentPenalty = $maintenanceBill->penalty_amount;
                $currentDiscount = $maintenanceBill->discount_amount;
                $currentTotal = $maintenanceBill->total_amount;

                $maintenanceBill->batch_id = $batchId;
                $maintenanceBill->penalty_amount = $currentPenalty;
                $maintenanceBill->discount_amount = $currentDiscount;
                $maintenanceBill->total_amount = $currentTotal;
                $maintenanceBill->status = config('status.maintenance_bills.paid');
                $maintenanceBill->paid_at = now();
                $maintenanceBill->payment_method = $request->payment_method;
                $maintenanceBill->transaction_id = $request->transaction_id;
                $maintenanceBill->received_by = \Auth::id();
                $maintenanceBill->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'All pending maintenance dues paid successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FlatController@payPendingDues: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while recording payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Handle the transfer of ownership for a flat
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

            $maxSizeKb = (float) \App\Models\Setting::get('max_document_size', 2) * 1024;
            $validatedData = $request->validate([
                'user_type' => 'required|in:new,existing',
                'existing_user_id' => 'required_if:user_type,existing|nullable|exists:users,id',
                'new_owner_name' => 'required_if:user_type,new|nullable|string|max:255',
                'new_owner_email' => 'required_if:user_type,new|nullable|email',
                'new_owner_phone' => 'nullable|string|max:20',
                'new_owner_aadhar' => 'required_if:user_type,new|nullable|digits:12',
                'transfer_date' => 'required|date',
                'transfer_fee' => 'required|numeric|min:0',
                'payment_method' => 'required|in:pending,cash,upi',
                'transaction_id' => [
                    'nullable',
                    'required_if:payment_method,upi',
                    'digits:12',
                    Rule::unique('maintenance_bills', 'transaction_id'),
                    Rule::unique('name_transfer_bills', 'transaction_id'),
                    Rule::unique('prepaid_maintenances', 'transaction_id'),
                ],
                'payment_slip' => 'nullable|required_if:payment_method,upi|file|mimes:jpeg,png,jpg,pdf|max:' . $maxSizeKb,
            ], [
                'transaction_id.required_if' => 'The UTR number is required for UPI payments.',
                'transaction_id.digits' => 'The UTR number must be exactly 12 digits.',
                'transaction_id.unique' => 'This UTR number has already been used.',
                'existing_user_id.required_if' => 'Please select an existing user.',
                'new_owner_name.required_if' => 'The new owner name is required for a new user.',
                'new_owner_email.required_if' => 'The new owner email is required for a new user.',
                'new_owner_aadhar.required_if' => 'The new owner aadhar is required for a new user.',
            ], [
                'transaction_id' => 'UTR number',
            ]);

            DB::beginTransaction();
            try {
                // Check if there are any pending maintenance bills for this flat
                $pendingBills = MaintenanceBill::where('flat_id', $flat->id)
                    ->where('status', '!=', config('status.maintenance_bills.paid'))
                    ->get();

                if ($pendingBills->isNotEmpty()) {
                    throw new \Exception('Ownership transfer is restricted until all pending maintenance dues (' . $pendingBills->count() . ' pending) are paid.');
                }



                $currentOwner = Resident::where('flat_id', $flat->id)
                    ->where('type', 'owner')
                    ->where(function ($q) {
                        $q->whereNull('move_out_date')
                            ->orWhere('move_out_date', '>=', now()->startOfDay());
                    })
                    ->orderBy('move_in_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if (! $currentOwner) {
                    throw new \Exception('No active owner found.');
                }

                // 1. Create or find new user
                if ($validatedData['user_type'] === 'existing') {
                    $newUser = User::findOrFail($validatedData['existing_user_id']);
                } else {
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
                }

                // 2. Generate Name Transfer Request (Bill)
                $settings = Setting::getAll();
                $fee = $request->has('transfer_fee') && $request->input('transfer_fee') !== ''
                    ? (float) $request->input('transfer_fee')
                    : (isset($settings['name_transfer_fee']) ? (float) $settings['name_transfer_fee'] : 0);

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
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
