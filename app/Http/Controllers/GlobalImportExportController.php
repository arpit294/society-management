<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Complain;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\CSV\Writer as CSVWriter;

class GlobalImportExportController extends Controller
{
    private function getTableConfigs()
    {
        return [
            'blocks' => [
                'label' => 'Blocks',
                'model' => Block::class,
                'headers' => ['block_name', 'total_floor', 'total_flats'],
                'labels' => ['Block Name (*)', 'Total Floors', 'Total Flats'],
                'required' => ['block_name'],
            ],
            'flats' => [
                'label' => 'Flats',
                'model' => Flat::class,
                'headers' => ['block_name', 'flat_no', 'floor_no', 'flat_type_name', 'status'],
                'labels' => ['Block Name (*)', 'Flat No (*)', 'Floor No', 'Flat Type Name', 'Status (occupied/vacant)'],
                'required' => ['block_name', 'flat_no'],
            ],
            'users' => [
                'label' => 'Staff & Users',
                'model' => User::class,
                'headers' => ['name', 'email', 'phone', 'role', 'status'],
                'labels' => ['Name (*)', 'Email Address (*)', 'Phone Number', 'Role (Admin/Manager/Accountant/Security/Resident)', 'Status (active/inactive)'],
                'required' => ['name', 'email'],
            ],
            'residents' => [
                'label' => 'Residents',
                'model' => Resident::class,
                'headers' => ['name', 'email', 'phone', 'aadhar_id', 'block_name', 'flat_no', 'type', 'move_in_date'],
                'labels' => ['Resident Name (*)', 'Email Address (*)', 'Phone Number', 'Aadhar ID (*)', 'Block Name (*)', 'Flat No (*)', 'Type (owner/rental) (*)', 'Move In Date (YYYY-MM-DD)'],
                'required' => ['name', 'email', 'aadhar_id', 'block_name', 'flat_no', 'type'],
            ],
            'complaints' => [
                'label' => 'Complaints',
                'model' => Complain::class,
                'headers' => ['subject', 'description', 'user_email', 'category', 'status'],
                'labels' => ['Subject (*)', 'Description (*)', 'User Email (*)', 'Category', 'Status (Pending/In Progress/Resolved)'],
                'required' => ['subject', 'description', 'user_email'],
            ],
            'expenses' => [
                'label' => 'Expenses',
                'model' => Expense::class,
                'headers' => ['title', 'total_amount', 'category_title', 'expense_date', 'invoice'],
                'labels' => ['Title (*)', 'Total Amount (₹) (*)', 'Category Title', 'Expense Date (YYYY-MM-DD)', 'Invoice No'],
                'required' => ['title', 'total_amount'],
            ],
            'flat_types' => [
                'label' => 'Flat Types',
                'model' => FlatType::class,
                'headers' => ['name', 'owner_maintenance_fee', 'rental_maintenance_fee', 'description', 'status'],
                'labels' => ['Type Name (*)', 'Owner Fee (₹)', 'Rental Fee (₹)', 'Description', 'Status (active/inactive)'],
                'required' => ['name'],
            ],
            'expense_categories' => [
                'label' => 'Expense Categories',
                'model' => ExpenseCategory::class,
                'headers' => ['title', 'status'],
                'labels' => ['Category Title (*)', 'Status (active/inactive)'],
                'required' => ['title'],
            ],
        ];
    }

    public function export(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403);
        $table = $request->input('table', 'blocks');
        $format = $request->input('format', 'excel');

        $configs = $this->getTableConfigs();
        if (!isset($configs[$table])) {
            abort(404, 'Selected module not found.');
        }

        $config = $configs[$table];
        $ext = $format === 'csv' ? 'csv' : 'xlsx';
        $contentType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        $headers = [
            'Content-type' => $contentType,
            'Content-Disposition' => 'attachment; filename=' . $table . '_export_' . date('Ymd_His') . '.' . $ext,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($table, $config, $format) {
            $writer = $format === 'csv' ? new CSVWriter() : new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($config['labels']));

            $modelClass = $config['model'];
            $records = $modelClass::all();

            foreach ($records as $record) {
                $rowValues = [];
                foreach ($config['headers'] as $h) {
                    $rowValues[] = $this->getRecordExportValue($table, $h, $record);
                }
                $writer->addRow(Row::fromValues($rowValues));
            }

            $writer->close();
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getRecordExportValue($table, $header, $record)
    {
        if ($table === 'flats') {
            if ($header === 'block_name') return $record->block->block_name ?? 'N/A';
            if ($header === 'flat_type_name') return $record->flatType->name ?? 'N/A';
        }
        if ($table === 'residents') {
            if ($header === 'name') return $record->user->name ?? 'N/A';
            if ($header === 'email') return $record->user->email ?? 'N/A';
            if ($header === 'phone') return $record->user->phone ?? 'N/A';
            if ($header === 'aadhar_id') return $record->user->aadhar_id ?? 'N/A';
            if ($header === 'block_name') return $record->block->block_name ?? 'N/A';
            if ($header === 'flat_no') return $record->flat->flat_no ?? 'N/A';
        }
        if ($table === 'complaints') {
            if ($header === 'user_email') return $record->user->email ?? 'N/A';
        }
        if ($table === 'expenses') {
            if ($header === 'category_title') return $record->category->title ?? 'N/A';
        }

        return $record->{$header} ?? '';
    }

    public function downloadTemplate(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403);
        $table = $request->input('table', 'blocks');
        $configs = $this->getTableConfigs();
        if (!isset($configs[$table])) {
            abort(404, 'Selected module not found.');
        }

        $config = $configs[$table];
        $headers = [
            'Content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=' . $table . '_import_template.xlsx',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($config) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($config['labels']));
            $writer->close();
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewImport(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403);
        $request->validate([
            'table' => 'required|string',
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $table = $request->table;
        $configs = $this->getTableConfigs();
        if (!isset($configs[$table])) {
            return response()->json(['success' => false, 'message' => 'Invalid module selected.']);
        }

        $file = $request->file('import_file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('temp_imports', 'global_' . $table . '_' . time() . '.' . $ext);

        try {
            $reader = $ext === 'csv' ? new CSVReader() : new Reader();
            $reader->open(Storage::path($path));

            $previewRows = [];
            $headers = [];
            $rowCount = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowCount === 0) {
                        $headers = $row->toArray();
                    } else {
                        $cells = $row->toArray();
                        foreach ($cells as &$cell) {
                            if ($cell instanceof \DateTime) {
                                $cell = $cell->format('Y-m-d');
                            }
                        }
                        $previewRows[] = $cells;
                    }
                    $rowCount++;
                    if ($rowCount > 6) break;
                }
                break;
            }
            $reader->close();

            return response()->json([
                'success' => true,
                'file_path' => $path,
                'table' => $table,
                'headers' => $headers,
                'preview_rows' => $previewRows,
                'expected_headers' => $configs[$table]['headers'],
                'expected_labels' => $configs[$table]['labels'],
            ]);
        } catch (\Exception $e) {
            Storage::delete($path);
            return response()->json([
                'success' => false,
                'message' => 'Error reading spreadsheet file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processImport(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403);
        $request->validate([
            'table' => 'required|string',
            'file_path' => 'required|string',
            'mapping' => 'required|array',
        ]);

        $table = $request->table;
        $path = $request->file_path;
        $mapping = $request->mapping;

        $configs = $this->getTableConfigs();
        if (!isset($configs[$table]) || !Storage::exists($path)) {
            return response()->json(['success' => false, 'message' => 'Invalid file or module. Please upload again.']);
        }

        $config = $configs[$table];
        foreach ($config['required'] as $reqField) {
            if (!isset($mapping[$reqField]) || $mapping[$reqField] === '') {
                Storage::delete($path);
                return response()->json(['success' => false, 'message' => "Required field '{$reqField}' is not mapped to any column."]);
            }
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        try {
            DB::beginTransaction();

            $reader = $ext === 'csv' ? new CSVReader() : new Reader();
            $reader->open(Storage::path($path));

            $rowCount = 0;
            $importedCount = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowCount === 0) {
                        $rowCount++;
                        continue;
                    }

                    $cells = $row->toArray();
                    foreach ($cells as &$cell) {
                        if ($cell instanceof \DateTime) {
                            $cell = $cell->format('Y-m-d');
                        }
                    }

                    $rowNum = $rowCount + 1;
                    $rowValues = [];
                    foreach ($mapping as $dbField => $colIndex) {
                        if ($colIndex !== '' && isset($cells[(int)$colIndex])) {
                            $rowValues[$dbField] = trim((string)$cells[(int)$colIndex]);
                        } else {
                            $rowValues[$dbField] = null;
                        }
                    }

                    $errorMsg = $this->validateRowConflicts($table, $rowValues, $rowNum);
                    if ($errorMsg) {
                        DB::rollBack();
                        Storage::delete($path);
                        return response()->json(['success' => false, 'message' => $errorMsg]);
                    }

                    $this->insertTableRecord($table, $rowValues);

                    $rowCount++;
                    $importedCount++;
                }
                break;
            }

            $reader->close();
            DB::commit();
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} records into {$config['label']}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($path);
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function validateRowConflicts($table, $data, $rowNum)
    {
        if ($table === 'blocks') {
            $name = $data['block_name'] ?? null;
            if (!$name) return "Row {$rowNum}: Block Name cannot be empty.";
            if (Block::where('block_name', $name)->exists()) {
                return "Row {$rowNum}: Block '{$name}' already exists in the system.";
            }
        }

        if ($table === 'flats') {
            $blockName = $data['block_name'] ?? null;
            $flatNo = $data['flat_no'] ?? null;
            if (!$blockName || !$flatNo) return "Row {$rowNum}: Block Name and Flat No are required.";
            $block = Block::where('block_name', $blockName)->first();
            if (!$block) return "Row {$rowNum}: Block '{$blockName}' does not exist.";
            if (Flat::where('block_id', $block->id)->where('flat_no', $flatNo)->exists()) {
                return "Row {$rowNum}: Flat '{$flatNo}' in Block '{$blockName}' already exists.";
            }
        }

        if ($table === 'users') {
            $email = $data['email'] ?? null;
            $name = $data['name'] ?? null;
            if (!$email || !$name) return "Row {$rowNum}: User Name and Email are required.";
            if (User::where('email', $email)->exists()) {
                return "Row {$rowNum}: User with email '{$email}' already exists.";
            }
        }

        if ($table === 'residents') {
            $aadhar = $data['aadhar_id'] ?? null;
            $email = $data['email'] ?? null;
            $blockName = $data['block_name'] ?? null;
            $flatNo = $data['flat_no'] ?? null;
            if (!$aadhar || !$email || !$blockName || !$flatNo) return "Row {$rowNum}: Missing required resident fields.";
            
            $block = Block::where('block_name', $blockName)->first();
            if (!$block) return "Row {$rowNum}: Block '{$blockName}' does not exist.";
            $flat = Flat::where('block_id', $block->id)->where('flat_no', $flatNo)->first();
            if (!$flat) return "Row {$rowNum}: Flat '{$flatNo}' in Block '{$blockName}' does not exist.";

            if (Resident::where('flat_id', $flat->id)->whereNull('move_out_date')->exists()) {
                return "Row {$rowNum}: Flat '{$flatNo}' is already occupied by an active resident.";
            }
            if (User::where('aadhar_id', $aadhar)->exists()) {
                return "Row {$rowNum}: Resident with Aadhar ID '{$aadhar}' already exists.";
            }
        }

        if ($table === 'flat_types') {
            $name = $data['name'] ?? null;
            if (!$name) return "Row {$rowNum}: Flat Type Name is required.";
            if (FlatType::where('name', $name)->exists()) {
                return "Row {$rowNum}: Flat Type '{$name}' already exists.";
            }
        }

        if ($table === 'expense_categories') {
            $title = $data['title'] ?? null;
            if (!$title) return "Row {$rowNum}: Category Title is required.";
            if (ExpenseCategory::where('title', $title)->exists()) {
                return "Row {$rowNum}: Expense Category '{$title}' already exists.";
            }
        }

        if ($table === 'complaints') {
            $email = $data['user_email'] ?? null;
            if (!$email || !User::where('email', $email)->exists()) {
                return "Row {$rowNum}: User email '{$email}' does not exist in the system.";
            }
        }

        if ($table === 'expenses') {
            $title = $data['title'] ?? null;
            $amount = $data['total_amount'] ?? null;
            if (!$title || !$amount) return "Row {$rowNum}: Expense Title and Total Amount are required.";
        }

        return null;
    }

    private function insertTableRecord($table, $data)
    {
        if ($table === 'blocks') {
            Block::create([
                'block_name' => $data['block_name'],
                'total_floor' => (int)($data['total_floor'] ?? 1),
                'total_flats' => (int)($data['total_flats'] ?? 0),
            ]);
        } elseif ($table === 'flats') {
            $block = Block::where('block_name', $data['block_name'])->first();
            $flatType = null;
            if (!empty($data['flat_type_name'])) {
                $flatType = FlatType::firstOrCreate(['name' => $data['flat_type_name']], ['status' => 'active']);
            }
            Flat::create([
                'block_id' => $block->id,
                'flat_no' => $data['flat_no'],
                'floor_no' => $data['floor_no'] ?? '1',
                'flat_type_id' => $flatType ? $flatType->id : null,
                'status' => $data['status'] ?? 'vacant',
            ]);
        } elseif ($table === 'users') {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'] ?? 'Resident',
                'status' => $data['status'] ?? 'active',
                'password' => Hash::make('password123'),
            ]);
        } elseif ($table === 'residents') {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'aadhar_id' => $data['aadhar_id'],
                    'role' => 'Resident',
                    'status' => 'active',
                    'password' => Hash::make('password123'),
                ]
            );
            $block = Block::where('block_name', $data['block_name'])->first();
            $flat = Flat::where('block_id', $block->id)->where('flat_no', $data['flat_no'])->first();
            $flat->update(['status' => 'occupied']);

            Resident::create([
                'user_id' => $user->id,
                'block_id' => $block->id,
                'flat_id' => $flat->id,
                'type' => strtolower($data['type'] ?? 'owner'),
                'move_in_date' => $data['move_in_date'] ?? date('Y-m-d'),
            ]);
        } elseif ($table === 'flat_types') {
            FlatType::create([
                'name' => $data['name'],
                'owner_maintenance_fee' => (float)($data['owner_maintenance_fee'] ?? 0),
                'rental_maintenance_fee' => (float)($data['rental_maintenance_fee'] ?? 0),
                'penalty_per_day' => (float)($data['penalty_per_day'] ?? 0),
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);
        } elseif ($table === 'expense_categories') {
            ExpenseCategory::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'status' => $data['status'] ?? 'active',
            ]);
        } elseif ($table === 'complaints') {
            $user = User::where('email', $data['user_email'])->first();
            Complain::create([
                'user_id' => $user->id,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'category' => $data['category'] ?? 'General',
                'status' => $data['status'] ?? 'Pending',
            ]);
        } elseif ($table === 'expenses') {
            $cat = null;
            if (!empty($data['category_title'])) {
                $cat = ExpenseCategory::firstOrCreate(['title' => $data['category_title']], ['slug' => Str::slug($data['category_title']), 'status' => 'active']);
            }
            Expense::create([
                'user_id' => Auth::id() ?? 1,
                'category_id' => $cat ? $cat->id : null,
                'title' => $data['title'],
                'total_amount' => (float)$data['total_amount'],
                'expense_date' => $data['expense_date'] ?? date('Y-m-d'),
                'invoice' => $data['invoice'] ?? null,
            ]);
        }
    }
}
