<?php

namespace App\Http\Controllers;

use App\DataTables\ResidentsDataTable;
use App\Models\Block;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(ResidentsDataTable $dataTable)
    {
        $blocks = \App\Models\Block::all();
        return $dataTable->render('residents.index', compact('blocks'));
    }

    public function create()
    {
        $blocks = Block::all();
        $users = User::with(['resident.flat.block'])->get();
        return view('residents.create', compact('blocks', 'users'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $flatHasOwner = Resident::where('flat_id', $request->flat_id)->where('type', 'owner')->exists();

        $rules = [
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'type' => 'required|string|in:owner,rental',
            'user_id' => 'required|exists:users,id',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date',
        ];

        // If it's a rental and flat doesn't have an owner, they can optionally provide one.
        if ($request->type === 'rental' && !$flatHasOwner) {
            $rules['owner_user_id'] = 'nullable|exists:users,id';
        }

        $validatedData = $request->validate($rules);

        // Remove owner_user_id from validatedData before creating the tenant
        $ownerUserId = $validatedData['owner_user_id'] ?? null;
        unset($validatedData['owner_user_id']);

        Resident::create($validatedData);

        // If owner_user_id was provided, create the owner resident
        if ($request->type === 'rental' && !$flatHasOwner && $ownerUserId) {
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

    public function edit(Resident $resident)
    {
        $blocks = Block::all();
        $flats = Flat::where('block_id', $resident->block_id)->get();
        $users = User::with(['resident.flat.block'])->get();
        return view('residents.edit', compact('resident', 'blocks', 'flats', 'users'));
    }

    public function update(Request $request, Resident $resident)
    {
        $flatHasOwner = Resident::where('flat_id', $request->flat_id)
            ->where('type', 'owner')
            ->where('id', '!=', $resident->id)
            ->exists();

        $rules = [
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'type' => 'required|string|in:owner,rental',
            'user_id' => 'required|exists:users,id',
            'move_in_date' => 'required|date',
            'move_out_date' => 'nullable|date',
        ];

        // If it's a rental and flat doesn't have an owner, they can optionally provide one.
        if ($request->type === 'rental' && !$flatHasOwner) {
            $rules['owner_user_id'] = 'nullable|exists:users,id';
        }

        $validatedData = $request->validate($rules);

        // Remove owner_user_id from validatedData before updating the tenant
        $ownerUserId = $validatedData['owner_user_id'] ?? null;
        unset($validatedData['owner_user_id']);

        $resident->update($validatedData);

        // If owner_user_id was provided, create the owner resident
        if ($request->type === 'rental' && !$flatHasOwner && $ownerUserId) {
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

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resident deleted successfully.',
        ]);
    }

    public function getFlatsByBlock($block_id)
    {
        $flats = Flat::where('block_id', $block_id)->get();
        return response()->json($flats);
    }

    public function getFlatOwner($flat_id)
    {
        $ownerResident = Resident::where('flat_id', $flat_id)->where('type', 'owner')->first();
        if ($ownerResident) {
            return response()->json(['has_owner' => true, 'user_id' => $ownerResident->user_id]);
        }
        return response()->json(['has_owner' => false]);
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Content-Disposition" => "attachment; filename=residents_import_template.xlsx",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () {
            $writer = new \OpenSpout\Writer\XLSX\Writer();
            $writer->openToFile('php://output');

            // Header row matching the agreed columns
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Name', 'Email', 'Phone', 'Aadhar ID', 'Block Name', 'Flat No', 'Type (owner/rental)', 'Move In Date (YYYY-MM-DD)'
            ]));
            
            // Example row
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'John Doe', 'john.doe@example.com', '9876543210', '123412341234', 'A', '101', 'owner', '2023-01-15'
            ]));

            $writer->close();
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('excel_file');

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($file->path());

            $isFirstRow = true;
            $successCount = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($isFirstRow) {
                        $isFirstRow = false;
                        continue; // Skip header row
                    }

                    $cells = $row->toArray();
                    
                    // Basic validation for required columns: Name, Email, Aadhar, Block, Flat, Type, Date
                    if (count($cells) < 8 || empty($cells[0]) || empty($cells[1]) || empty($cells[3]) || empty($cells[4]) || empty($cells[5])) {
                        continue;
                    }

                    $name = $cells[0];
                    $email = $cells[1];
                    $phone = $cells[2] ?? null;
                    $aadhar = $cells[3];
                    $blockName = $cells[4];
                    $flatNo = $cells[5];
                    $type = strtolower(trim($cells[6] ?? 'owner'));
                    $moveInDate = $cells[7];

                    // Check or create User
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $name,
                            'phone' => $phone,
                            'aadhar_id' => $aadhar,
                            'password' => \Illuminate\Support\Facades\Hash::make('password123'), // Default password
                            'role' => $type,
                            'status' => 'active'
                        ]
                    );

                    // Find Block
                    $block = Block::where('block_name', $blockName)->first();
                    if (!$block) {
                        continue; // Skip if block not found
                    }

                    // Find Flat
                    $flat = Flat::where('block_id', $block->id)->where('flat_no', $flatNo)->first();
                    if (!$flat) {
                        continue; // Skip if flat not found
                    }

                    // Create Resident (check if exact resident exists to avoid duplicates)
                    Resident::firstOrCreate([
                        'user_id' => $user->id,
                        'flat_id' => $flat->id,
                        'type' => $type,
                    ], [
                        'block_id' => $block->id,
                        'move_in_date' => $moveInDate ?: now(),
                    ]);

                    $successCount++;
                }
                break; // Only process the first sheet
            }

            $reader->close();
            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', "Successfully imported {$successCount} residents!");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', "Error importing residents: " . $e->getMessage());
        }
    }
}
