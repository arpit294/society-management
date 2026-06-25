<?php

namespace App\Http\Controllers;

use App\DataTables\ResidentsDataTable;
use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class ResidentController extends Controller
{
    // Display a listing of the resource.
    public function index(ResidentsDataTable $dataTable)
    {
        abort_if(\Gate::denies('resident_view'), 403);
        $blocks = Block::all();

        return $dataTable->render('residents.index', compact('blocks'));
    }

    // Show the form for creating a new resource.
    public function create()
    {
        abort_if(\Gate::denies('resident_create'), 403);
        $blocks = Block::all();
        $users = User::with(['resident.flat.block'])->get();

        return view('residents.create', compact('blocks', 'users'));
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        abort_if(\Gate::denies('resident_create'), 403);
        // Check if the flat already has an owner
        $flatHasOwner = Resident::where('flat_id', $request->flat_id)->where('type', 'owner')->exists();

        // Check if flat is already occupied
        $isOccupied = Resident::where('flat_id', $request->flat_id)
            ->whereNull('move_out_date')
            ->exists();

        if ($isOccupied) {
            return response()->json([
                'success' => false,
                'message' => 'This flat is already occupied by an active resident. Please move them out first before adding a new one.',
            ], 422);
        }

        $rules = [
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'type' => 'required|string|in:owner,rental',
            'user_id' => 'required|exists:users,id',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date',
        ];

        // If it's a rental and flat doesn't have an owner, they must provide one.
        if ($request->type === 'rental' && ! $flatHasOwner) {
            $rules['owner_user_id'] = 'required|exists:users,id';
        }

        $validatedData = $request->validate($rules, [
            'owner_user_id.required' => 'A flat must have an owner before it can be rented. Please assign an owner.',
        ]);

        // Remove owner_user_id from validatedData before creating the tenant
        $ownerUserId = $validatedData['owner_user_id'] ?? null;
        unset($validatedData['owner_user_id']);

        Resident::create($validatedData);

        // If owner_user_id was provided, create the owner resident
        if ($request->type === 'rental' && ! $flatHasOwner && $ownerUserId) {
            Resident::create([
                'block_id' => $validatedData['block_id'],
                'flat_id' => $validatedData['flat_id'],
                'user_id' => $ownerUserId,
                'type' => 'owner',
                'move_in_date' => $validatedData['move_in_date'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Resident created successfully.',
        ]);
    }

    // Show the form for editing the specified resource.
    public function edit(Resident $resident)
    {
        abort_if(\Gate::denies('resident_edit'), 403);
        $blocks = Block::all();
        $flats = Flat::where('block_id', $resident->block_id)->get();
        $users = User::with(['resident.flat.block'])->get();

        return view('residents.edit', compact('resident', 'blocks', 'flats', 'users'));
    }

    // Update the specified resource in storage.
    public function update(Request $request, Resident $resident)
    {
        abort_if(! \Auth::user()->can('resident_edit'), 403);
        $flatHasOwner = Resident::where('flat_id', $request->flat_id)
            ->where('type', 'owner')
            ->where(function ($q) {
                $q->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
            ->where('id', '!=', $resident->id)
            ->exists();

        // Check if flat is already occupied by another resident
        $isOccupied = Resident::where('flat_id', $request->flat_id)
            ->whereNull('move_out_date')
            ->where('id', '!=', $resident->id)
            ->exists();

        if ($isOccupied) {
            return response()->json([
                'success' => false,
                'message' => 'This flat is already occupied by an active resident. Please move them out first.',
            ], 422);
        }

        $rules = [
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'type' => 'required|string|in:owner,rental',
            'user_id' => 'required|exists:users,id',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date',
        ];

        // If it's a rental and flat doesn't have an owner, they must provide one.
        if ($request->type === 'rental' && ! $flatHasOwner) {
            $rules['owner_user_id'] = 'required|exists:users,id';
        }

        $validatedData = $request->validate($rules, [
            'owner_user_id.required' => 'A flat must have an owner before it can be rented. Please assign an owner.',
        ]);

        // Remove owner_user_id from validatedData before updating the tenant
        $ownerUserId = $validatedData['owner_user_id'] ?? null;
        unset($validatedData['owner_user_id']);

        $resident->update($validatedData);

        // If owner_user_id was provided, create the owner resident
        if ($request->type === 'rental' && ! $flatHasOwner && $ownerUserId) {
            Resident::create([
                'block_id' => $validatedData['block_id'],
                'flat_id' => $validatedData['flat_id'],
                'user_id' => $ownerUserId,
                'type' => 'owner',
                'move_in_date' => $validatedData['move_in_date'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Resident updated successfully.',
        ]);
    }

    // Remove the specified resource from storage.
    public function destroy(Resident $resident)
    {
        abort_if(! \Auth::user()->can('resident_delete'), 403);
        $resident->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resident deleted successfully.',
        ]);
    }

    // API Methods
    public function getFlatsByBlock($block_id)
    {
        abort_if(! \Auth::user()->can('resident_view'), 403);
        $flats = Flat::where('block_id', $block_id)->get();

        return response()->json($flats);
    }

    public function getFlatOwner($flat_id)
    {
        abort_if(! \Auth::user()->can('resident_view'), 403);
        $ownerResident = Resident::where('flat_id', $flat_id)->where('type', 'owner')->first();
        if ($ownerResident) {
            return response()->json(['has_owner' => true, 'user_id' => $ownerResident->user_id]);
        }

        return response()->json(['has_owner' => false]);
    }

    public function getFlatUsers($flat_id)
    {
        abort_if(! \Auth::user()->can('resident_view'), 403);
        $residents = Resident::with('user')->where('flat_id', $flat_id)->get();

        $users = $residents->map(function ($resident) {
            return [
                'id' => $resident->user->id,
                'name' => $resident->user->name,
                'email' => $resident->user->email,
                'phone' => $resident->user->phone,
                'resident_type' => $resident->type,
            ];
        });

        return response()->json($users);
    }

    // Bulk Import Methods
    public function downloadTemplate()
    {
        abort_if(! \Auth::user()->can('resident_create'), 403);
        $headers = [
            'Content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=residents_import_template.xlsx',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            // Header row matching the agreed columns
            $writer->addRow(Row::fromValues([
                'Name', 'Email', 'Phone', 'Aadhar ID', 'Block Name', 'Flat No', 'Type (owner/rental)', 'Move In Date (YYYY-MM-DD)',
            ]));

            // Example row
            $writer->addRow(Row::fromValues([
                'John Doe', 'john.doe@example.com', '9876543210', '123412341234', 'A', '101', 'owner', '2023-01-15',
            ]));

            $writer->close();
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export(Request $request)
    {
        abort_if(\Gate::denies('resident_view'), 403);
        $headers = [
            'Content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=residents_export_'.date('Ymd_His').'.xlsx',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($request) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Name', 'Email', 'Phone', 'Aadhar ID', 'Block Name', 'Flat No', 'Type', 'Move In Date', 'Move Out Date'
            ]));

            $query = Resident::with(['user', 'block', 'flat']);
            
            if ($request->filled('block')) {
                $query->whereHas('block', function ($q) use ($request) {
                    $q->where('block_name', $request->block);
                });
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            $query->chunk(200, function ($residents) use ($writer) {
                foreach ($residents as $resident) {
                    $writer->addRow(Row::fromValues([
                        $resident->user->name ?? 'N/A',
                        $resident->user->email ?? 'N/A',
                        $resident->user->phone ?? 'N/A',
                        $resident->user->aadhar_id ?? 'N/A',
                        $resident->block->block_name ?? 'N/A',
                        $resident->flat->flat_no ?? 'N/A',
                        ucfirst($resident->type),
                        $resident->move_in_date ? date('Y-m-d', strtotime($resident->move_in_date)) : '',
                        $resident->move_out_date ? date('Y-m-d', strtotime($resident->move_out_date)) : '',
                    ]));
                }
            });

            $writer->close();
        };

        return response()->stream($callback, 200, $headers);
    }

    // Handle the preview of residents from Excel file
    public function previewImport(Request $request)
    {
        abort_if(\Gate::denies('resident_create'), 403);
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('excel_file');
        
        // Store the file temporarily
        $path = $file->storeAs('temp_imports', 'residents_import_' . time() . '.' . $file->getClientOriginalExtension());

        try {
            $reader = new Reader;
            $reader->open(\Illuminate\Support\Facades\Storage::path($path));
            
            $previewRows = [];
            $headers = [];
            $rowCount = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowCount === 0) {
                        $headers = $row->toArray();
                    } else {
                        // Sometimes dates are objects in Spout, we must convert to string for JSON preview
                        $cells = $row->toArray();
                        foreach ($cells as &$cell) {
                            if ($cell instanceof \DateTime) {
                                $cell = $cell->format('Y-m-d');
                            }
                        }
                        $previewRows[] = $cells;
                    }
                    
                    $rowCount++;

                }
                break; // Only read first sheet
            }
            $reader->close();

            return response()->json([
                'success' => true,
                'file_path' => $path,
                'headers' => $headers,
                'preview_rows' => $previewRows
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Storage::delete($path);
            return response()->json([
                'success' => false,
                'message' => 'Error reading Excel file: ' . $e->getMessage()
            ], 500);
        }
    }

    // Handle the actual import process with field mapping
    public function processImport(Request $request)
    {
        abort_if(\Gate::denies('resident_create'), 403);
        $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
        ]);

        $path = $request->file_path;
        if (!\Illuminate\Support\Facades\Storage::exists($path) || !str_starts_with($path, 'temp_imports/')) {
            return response()->json(['success' => false, 'message' => 'Temporary file not found or invalid. Please try uploading again.']);
        }

        $mapping = $request->mapping;

        // Ensure required mappings are present
        $requiredFields = ['name', 'email', 'aadhar_id', 'block_name', 'flat_no', 'type'];
        foreach ($requiredFields as $field) {
            if (!isset($mapping[$field]) && $mapping[$field] !== '0' && $mapping[$field] !== 0) {
                \Illuminate\Support\Facades\Storage::delete($path);
                return response()->json(['success' => false, 'message' => "Required field '{$field}' is not mapped."]);
            }
        }

        try {
            DB::beginTransaction();

            $reader = new Reader;
            $reader->open(\Illuminate\Support\Facades\Storage::path($path));

            $isFirstRow = true;
            $successCount = 0;
            $failedRecords = [];
            $rowIndex = 1;

            $defaultPassword = Hash::make('password123');
            $blockCache = [];
            $flatCache = [];
            $activeOwnerCache = [];
            $userCache = [];

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($isFirstRow) {
                        $isFirstRow = false;
                        $rowIndex++;
                        continue; // Skip header row
                    }

                    $cells = $row->toArray();

                    // Safely grab using mapped indices
                    $name = isset($mapping['name']) && isset($cells[$mapping['name']]) ? trim($cells[$mapping['name']]) : null;
                    $email = isset($mapping['email']) && isset($cells[$mapping['email']]) ? trim($cells[$mapping['email']]) : null;
                    $phone = isset($mapping['phone']) && isset($cells[$mapping['phone']]) ? trim($cells[$mapping['phone']]) : null;
                    $aadhar_id = isset($mapping['aadhar_id']) && isset($cells[$mapping['aadhar_id']]) ? trim($cells[$mapping['aadhar_id']]) : null;
                    $block_name = isset($mapping['block_name']) && isset($cells[$mapping['block_name']]) ? trim($cells[$mapping['block_name']]) : null;
                    $flat_no = isset($mapping['flat_no']) && isset($cells[$mapping['flat_no']]) ? trim($cells[$mapping['flat_no']]) : null;
                    $type = isset($mapping['type']) && isset($cells[$mapping['type']]) ? strtolower(trim($cells[$mapping['type']])) : 'owner';
                    
                    $moveInDateRaw = isset($mapping['move_in_date']) && isset($cells[$mapping['move_in_date']]) ? $cells[$mapping['move_in_date']] : null;

                    $data = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'aadhar_id' => $aadhar_id,
                        'block_name' => $block_name,
                        'flat_no' => $flat_no,
                        'type' => $type,
                        'move_in_date' => $moveInDateRaw,
                    ];

                    // Validate Data
                    $validator = \Illuminate\Support\Facades\Validator::make($data, [
                        'name' => 'required|string|max:255',
                        'email' => 'required|email|max:255',
                        'phone' => 'nullable|string|max:20',
                        'aadhar_id' => 'required|string|max:20',
                        'block_name' => 'required|string',
                        'flat_no' => 'required|string',
                        'type' => 'required|in:owner,rental',
                    ]);

                    if ($validator->fails()) {
                        $failedRecords[] = [
                            'name' => $data['name'] ?? 'Unknown',
                            'block' => $data['block_name'] ?? 'Unknown',
                            'flat' => $data['flat_no'] ?? 'Unknown',
                            'reason' => implode(', ', $validator->errors()->all())
                        ];
                        $rowIndex++;
                        continue;
                    }

                    // Date Parsing
                    $moveInDate = $data['move_in_date'];
                    if ($moveInDate instanceof \DateTime) {
                        $moveInDate = $moveInDate->format('Y-m-d');
                    } else if (!empty($moveInDate) && strtotime($moveInDate)) {
                        $moveInDate = date('Y-m-d', strtotime($moveInDate));
                    } else {
                        $moveInDate = now()->format('Y-m-d');
                    }

                    // Find Block (with cache)
                    if (! isset($blockCache[$data['block_name']])) {
                        $blockCache[$data['block_name']] = Block::where('block_name', $data['block_name'])->first();
                    }
                    $block = $blockCache[$data['block_name']];

                    if (! $block) {
                        $failedRecords[] = [
                            'name' => $data['name'] ?? 'Unknown',
                            'block' => $data['block_name'] ?? 'Unknown',
                            'flat' => $data['flat_no'] ?? 'Unknown',
                            'reason' => "Block '{$data['block_name']}' not found."
                        ];
                        $rowIndex++;
                        continue;
                    }

                    // Find Flat (with cache)
                    $flatCacheKey = $block->id.'_'.$data['flat_no'];
                    if (! isset($flatCache[$flatCacheKey])) {
                        $flat = Flat::where('block_id', $block->id)->where('flat_no', $data['flat_no'])->first();
                        
                        if (!$flat) {
                            $failedRecords[] = [
                                'name' => $data['name'] ?? 'Unknown',
                                'block' => $data['block_name'] ?? 'Unknown',
                                'flat' => $data['flat_no'] ?? 'Unknown',
                                'reason' => "Flat '{$data['flat_no']}' not found in Block '{$data['block_name']}'."
                            ];
                            $rowIndex++;
                            continue;
                        }
                        
                        $flatCache[$flatCacheKey] = $flat;
                    }
                    $flat = $flatCache[$flatCacheKey];

                    if (! $flat) {
                        $failedRecords[] = [
                            'name' => $data['name'] ?? 'Unknown',
                            'block' => $data['block_name'] ?? 'Unknown',
                            'flat' => $data['flat_no'] ?? 'Unknown',
                            'reason' => "Flat '{$data['flat_no']}' not found and no active Flat Type exists to auto-create it."
                        ];
                        $rowIndex++;
                        continue;
                    }

                    // Enforce No Rental Without Owner Rule
                    if ($data['type'] === 'rental') {
                        if (!array_key_exists($flat->id, $activeOwnerCache)) {
                            $activeOwnerCache[$flat->id] = Resident::where('flat_id', $flat->id)
                                ->where('type', 'owner')
                                ->where(function($q) {
                                    $q->whereNull('move_out_date')->orWhere('move_out_date', '>=', now()->startOfDay());
                                })->exists();
                        }

                        if (!$activeOwnerCache[$flat->id]) {
                            $failedRecords[] = [
                                'name' => $data['name'] ?? 'Unknown',
                                'block' => $data['block_name'] ?? 'Unknown',
                                'flat' => $data['flat_no'] ?? 'Unknown',
                                'reason' => "Cannot add rental to flat '{$data['flat_no']}' because it has no active owner."
                            ];
                            $rowIndex++;
                            continue;
                        }
                    } else if ($data['type'] === 'owner') {
                        $activeOwnerCache[$flat->id] = true;
                    }

                    // Check or create User (with cache)
                    if (!isset($userCache[$data['email']])) {
                        $user = User::firstOrCreate(
                            ['email' => $data['email']],
                            [
                                'name' => $data['name'],
                                'phone' => $data['phone'],
                                'aadhar_id' => $data['aadhar_id'],
                                'password' => $defaultPassword,
                                'role' => $data['type'],
                                'status' => 'active',
                            ]
                        );
                        $userCache[$data['email']] = $user->id;
                    }
                    $userId = $userCache[$data['email']];

                    // Check for duplicate entry
                    $isDuplicate = Resident::where('user_id', $userId)
                        ->where('flat_id', $flat->id)
                        ->where('type', $data['type'])
                        ->exists();

                    if ($isDuplicate) {
                        $failedRecords[] = [
                            'name' => $data['name'] ?? 'Unknown',
                            'block' => $data['block_name'] ?? 'Unknown',
                            'flat' => $data['flat_no'] ?? 'Unknown',
                            'reason' => "Duplicate entry: Resident already registered for this flat."
                        ];
                        $rowIndex++;
                        continue;
                    }

                    // Create new resident
                    Resident::create([
                        'user_id' => $userId,
                        'flat_id' => $flat->id,
                        'type' => $data['type'],
                        'block_id' => $block->id,
                        'move_in_date' => $moveInDate,
                    ]);

                    $successCount++;
                    $rowIndex++;
                }
                break; // Only process the first sheet
            }

            $reader->close();
            DB::commit();
            \Illuminate\Support\Facades\Storage::delete($path); // Cleanup temp file

            return response()->json([
                'success' => true,
                'success_count' => $successCount,
                'failed_count' => count($failedRecords),
                'failed_records' => $failedRecords
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Storage::delete($path); // Cleanup temp file

            return response()->json(['success' => false, 'message' => 'Error processing residents import: '.$e->getMessage()]);
        }
    }
}
