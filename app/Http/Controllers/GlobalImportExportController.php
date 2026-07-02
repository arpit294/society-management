<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Models\Block;
use App\Models\Complain;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\NameTransferBill;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nette\Schema\ValidationException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\CSV\Writer as CSVWriter;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class GlobalImportExportController extends Controller
{
    private function getTableConfigs()
    {
        $currencySymbol = CurrencyHelper::getCurrencySymbol();

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
                'headers' => ['name', 'email', 'phone', 'role', 'password', 'aadhar_id', 'status'],
                'labels' => ['Name (*)', 'Email Address (*)', 'Phone Number', 'Role (Admin/Manager/Accountant/Security/Resident)', 'Password', 'Aadhar ID', 'Status (active/inactive)'],
                'required' => ['name', 'email'],
            ],
            'residents' => [
                'label' => 'Residents',
                'model' => Resident::class,
                'headers' => ['name', 'email', 'phone', 'aadhar_id', 'block_name', 'flat_no', 'type', 'move_in_date', 'move_out_date'],
                'labels' => ['Resident Name (*)', 'Email Address (*)', 'Phone Number', 'Aadhar ID (*)', 'Block Name (*)', 'Flat No (*)', 'Type (owner/rental) (*)', 'Move In Date (YYYY-MM-DD)', 'Move Out Date (YYYY-MM-DD)'],
                'required' => ['name', 'email', 'aadhar_id', 'block_name', 'flat_no', 'type'],
            ],
            'complaints' => [
                'label' => 'Complaints',
                'model' => Complain::class,
                'headers' => ['subject', 'description', 'user_email', 'category', 'status', 'resolution_notes'],
                'labels' => ['Subject (*)', 'Description (*)', 'User Email (*)', 'Category (Maintenance Issues/Security Issues/Cleanliness & Housekeeping/Common Facilities/other)', 'Status (pending/in-progress/resolved)', 'Resolution Notes'],
                'required' => ['subject', 'description', 'user_email'],
            ],
            'expenses' => [
                'label' => 'Expenses',
                'model' => Expense::class,
                'headers' => ['title', 'total_amount', 'category_title', 'expense_date', 'invoice', 'user_email'],
                'labels' => ['Title (*)', "Total Amount ({$currencySymbol}) (*)", 'Category Title', 'Expense Date (YYYY-MM-DD)', 'Invoice No', 'User Email'],
                'required' => ['title', 'total_amount'],
            ],
            'flat_types' => [
                'label' => 'Flat Types',
                'model' => FlatType::class,
                'headers' => ['name', 'owner_maintenance_fee', 'rental_maintenance_fee', 'description', 'status'],
                'labels' => ['Type Name (*)', "Owner Fee ({$currencySymbol})", "Rental Fee ({$currencySymbol})", 'Description', 'Status (active/inactive)'],
                'required' => ['name'],
            ],
            'expense_categories' => [
                'label' => 'Expense Categories',
                'model' => ExpenseCategory::class,
                'headers' => ['title', 'status'],
                'labels' => ['Category Title (*)', 'Status (active/inactive)'],
                'required' => ['title'],
            ],
            'maintenances' => [
                'label' => 'Maintenance Batches',
                'model' => Maintenance::class,
                'headers' => ['month', 'year', 'billing_cycle', 'due_date', 'total_additional_cost', 'status'],
                'labels' => ['Month (Jan, Feb...) (*)', 'Year (YYYY) (*)', 'Billing Cycle (monthly/quarterly/yearly)', 'Due Date (YYYY-MM-DD)', "Additional Cost ({$currencySymbol})", 'Status (draft/published)'],
                'required' => ['month', 'year'],
            ],
            'maintenance_bills' => [
                'label' => 'Maintenance Payments / Bills',
                'model' => MaintenanceBill::class,
                'headers' => ['user_email', 'block_name', 'flat_no', 'amount', 'penalty_amount', 'discount_amount', 'total_amount', 'generated_date', 'paid_at', 'payment_method', 'transaction_id', 'payment_slip', 'status'],
                'labels' => ['User Email (*)', 'Block Name (*)', 'Flat No (*)', "Amount ({$currencySymbol}) (*)", "Penalty Amount ({$currencySymbol})", "Discount Amount ({$currencySymbol})", "Total Amount ({$currencySymbol}) (*)", 'Generated Date (YYYY-MM-DD)', 'Paid At (YYYY-MM-DD HH:MM)', 'Payment Method', 'Transaction ID', 'Payment Slip URL', 'Status (pending/paid)'],
                'required' => ['user_email', 'block_name', 'flat_no', 'total_amount'],
            ],
            'name_transfer_bills' => [
                'label' => 'Transfer Fees',
                'model' => NameTransferBill::class,
                'headers' => ['block_name', 'flat_no', 'old_owner_email', 'new_owner_email', 'amount', 'transfer_date', 'paid_at', 'payment_method', 'transaction_id', 'payment_slip', 'is_approved', 'status'],
                'labels' => ['Block Name (*)', 'Flat No (*)', 'Old Owner Email (*)', 'New Owner Email (*)', "Transfer Fee Amount ({$currencySymbol}) (*)", 'Transfer Date (YYYY-MM-DD)', 'Paid At (YYYY-MM-DD HH:MM)', 'Payment Method', 'Transaction ID', 'Payment Slip URL', 'Is Approved (1/0)', 'Status (pending/paid)'],
                'required' => ['block_name', 'flat_no', 'old_owner_email', 'new_owner_email', 'amount'],
            ],
        ];
    }

    public function export(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403);
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $table = $request->input('table', 'blocks');
            $format = strtolower((string) $request->input('format', 'excel'));


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
                // No-op: OpenSpout handles writing/closing on its own.

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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in GlobalImportExportController@export: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred during export: ' . $e->getMessage());
        }
    }

    private function getRecordExportValue($table, $header, $record)
    {
        // Return export value; guard against missing relations

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
            if ($header === 'user_email') return $record->user->email ?? 'N/A';
        }
        if ($table === 'maintenance_bills') {
            if ($header === 'user_email') return $record->user->email ?? 'N/A';
            if ($header === 'block_name') return $record->flat->block->block_name ?? 'N/A';
            if ($header === 'flat_no') return $record->flat->flat_no ?? 'N/A';
        }
        if ($table === 'name_transfer_bills') {
            if ($header === 'block_name') return $record->flat->block->block_name ?? 'N/A';
            if ($header === 'flat_no') return $record->flat->flat_no ?? 'N/A';
            if ($header === 'old_owner_email') return $record->oldOwner->email ?? 'N/A';
            if ($header === 'new_owner_email') return $record->newOwner->email ?? 'N/A';
        }

        return $record->{$header} ?? '';
    }

    public function downloadTemplate(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403);
        try {
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in GlobalImportExportController@downloadTemplate: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred downloading template: ' . $e->getMessage());
        }
    }

    public function previewImport(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403);
        $request->validate([
            'table' => 'required|string',
            'import_file' => 'required|file|max:20480',
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
            $consecutiveEmpty = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowCount === 0) {
                        $headers = $row->toArray();
                        $rowCount++;
                    } else {
                        $cells = $row->toArray();
                        $isEmptyRow = true;
                        foreach ($cells as &$cell) {
                            if ($cell instanceof \DateTime) {
                                $cell = $cell->format('Y-m-d');
                            }
                            if (trim((string)$cell) !== '') $isEmptyRow = false;
                        }
                        if ($isEmptyRow) {
                            $consecutiveEmpty++;
                            if ($consecutiveEmpty > 20) break;
                            continue;
                        }
                        $consecutiveEmpty = 0;
                        $previewRows[] = $cells;
                        $rowCount++;
                    }
                    if (count($previewRows) >= 6 || $rowCount > 50) break;
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
        set_time_limit(0);
        ini_set('memory_limit', '-1');

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

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        try {
            DB::beginTransaction();


            $reader = $ext === 'csv' ? new CSVReader() : new Reader();
            $reader->open(Storage::path($path));

            $rowCount = 0;
            $importedCount = 0;
            $failedRows = [];
            $consecutiveEmpty = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowCount === 0) {
                        $rowCount++;
                        continue;
                    }

                    $cells = $row->toArray();
                    $isEmptyRow = true;
                    foreach ($cells as &$cell) {
                        if ($cell instanceof \DateTime) {
                            $cell = $cell->format('Y-m-d');
                        }
                        if (trim((string)$cell) !== '') $isEmptyRow = false;
                    }

                    if ($isEmptyRow) {
                        $consecutiveEmpty++;
                        if ($consecutiveEmpty > 20) break;
                        $rowCount++;
                        continue;
                    }
                    $consecutiveEmpty = 0;

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
                        $failedRows[] = [
                            'sheet' => $config['label'],
                            'row' => $rowNum,
                            'record' => $this->extractRecordIdentifier($rowValues),
                            'reason' => preg_replace('/^Row \d+:\s*/', '', $errorMsg),
                        ];
                        $rowCount++;
                        continue;
                    }

                    try {
                        $this->insertTableRecord($table, $rowValues);
                        $importedCount++;
                    } catch (\Exception $ex) {
                        $failedRows[] = [
                            'sheet' => $config['label'],
                            'row' => $rowNum,
                            'record' => $this->extractRecordIdentifier($rowValues),
                            'reason' => "Database error: " . $ex->getMessage(),
                        ];
                    }

                    $rowCount++;
                }
                break;
            }

            $reader->close();
            DB::commit();
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'success_count' => $importedCount,
                'failed_count' => count($failedRows),
                'failed_records' => $failedRows,
                'message' => "Successfully processed {$config['label']} import.",
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
            if ($block->total_flats > 0 && Flat::where('block_id', $block->id)->count() >= $block->total_flats) {
                return "Row {$rowNum}: Block '{$blockName}' already has the maximum {$block->total_flats} flats.";
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
            $existingUserWithAadhar = User::where('aadhar_id', $aadhar)->first();
            if ($existingUserWithAadhar && $existingUserWithAadhar->email !== $email) {
                return "Row {$rowNum}: Aadhar ID '{$aadhar}' is already registered to another user ({$existingUserWithAadhar->email}).";
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

        if ($table === 'maintenances') {
            $month = $data['month'] ?? null;
            $year = $data['year'] ?? null;
            if (!$month || !$year) return "Row {$rowNum}: Month and Year are required.";
            if (Maintenance::where('month', $month)->where('year', $year)->exists()) {
                return "Row {$rowNum}: Maintenance batch for '{$month} {$year}' already exists.";
            }
        }

        if ($table === 'maintenance_bills') {
            $email = $data['user_email'] ?? null;
            $blockName = $data['block_name'] ?? null;
            $flatNo = $data['flat_no'] ?? null;
            if (!$email || !$blockName || !$flatNo) return "Row {$rowNum}: User Email, Block Name, and Flat No are required.";
            $user = User::where('email', $email)->first();
            if (!$user) return "Row {$rowNum}: User email '{$email}' does not exist.";
            $block = Block::where('block_name', $blockName)->first();
            if (!$block) return "Row {$rowNum}: Block '{$blockName}' does not exist.";
            $flat = Flat::where('block_id', $block->id)->where('flat_no', $flatNo)->first();
            if (!$flat) return "Row {$rowNum}: Flat '{$flatNo}' in Block '{$blockName}' does not exist.";
        }

        if ($table === 'name_transfer_bills') {
            $blockName = $data['block_name'] ?? null;
            $flatNo = $data['flat_no'] ?? null;
            $oldEmail = $data['old_owner_email'] ?? null;
            $newEmail = $data['new_owner_email'] ?? null;
            if (!$blockName || !$flatNo || !$oldEmail || !$newEmail) return "Row {$rowNum}: Block, Flat, Old Owner Email, and New Owner Email are required.";
            $block = Block::where('block_name', $blockName)->first();
            if (!$block) return "Row {$rowNum}: Block '{$blockName}' does not exist.";
            $flat = Flat::where('block_id', $block->id)->where('flat_no', $flatNo)->first();
            if (!$flat) return "Row {$rowNum}: Flat '{$flatNo}' does not exist.";
            if (!User::where('email', $oldEmail)->exists()) return "Row {$rowNum}: Old owner email '{$oldEmail}' does not exist.";
            if (!User::where('email', $newEmail)->exists()) return "Row {$rowNum}: New owner email '{$newEmail}' does not exist.";
        }

        return null;
    }

    private function insertTableRecord($table, $data)
    {
        static $defaultPasswordHash = null;
        if ($defaultPasswordHash === null) {
            $defaultPasswordHash = Hash::make('password123');
        }

        if ($table === 'blocks') {
            Block::create([
                'block_name' => $data['block_name'],
                'total_floor' => (int)($data['total_floor'] ?? 1),
                'total_flats' => (int)($data['total_flats'] ?? 0),
            ]);
        } elseif ($table === 'flats') {
            $block = Block::where('block_name', $data['block_name'])->first();
            if ($block->total_flats > 0 && Flat::where('block_id', $block->id)->count() >= $block->total_flats) {
                throw new \Exception("Block '{$data['block_name']}' already has the maximum {$block->total_flats} flats.");
            }
            $flatType = null;
            if (!empty($data['flat_type_name'])) {
                $flatType = FlatType::firstOrCreate(['name' => $data['flat_type_name']], ['status' => 'active']);
            }
            Flat::create([
                'block_id' => $block->id,
                'flat_no' => $data['flat_no'],
                'floor_no' => !empty($data['floor_no']) ? $data['floor_no'] : '1',
                'flat_type_id' => $flatType ? $flatType->id : null,
                'status' => !empty($data['status']) ? $data['status'] : 'vacant',
            ]);
        } elseif ($table === 'users') {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => !empty($data['phone']) ? $data['phone'] : null,
                'role' => !empty($data['role']) ? $data['role'] : 'Resident',
                // Default status should always be active for new users.
                'status' => 'active',
                'password' => !empty($data['password']) ? $data['password'] : $defaultPasswordHash,
                'aadhar_id' => !empty($data['aadhar_id']) ? $data['aadhar_id'] : null,

            ]);
        } elseif ($table === 'residents') {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => !empty($data['phone']) ? $data['phone'] : null,
                    'aadhar_id' => $data['aadhar_id'],
                    'role' => 'Resident',
                    'status' => 'active',
                    'password' => $defaultPasswordHash,
                ]
            );
            $block = Block::where('block_name', $data['block_name'])->first();
            $flat = Flat::where('block_id', $block->id)->where('flat_no', $data['flat_no'])->first();
            $flat->update(['status' => 'occupied']);

            $resType = !empty($data['type']) ? strtolower(trim($data['type'])) : 'owner';
            if (!in_array($resType, ['owner', 'rental'])) $resType = 'owner';

            Resident::create([
                'user_id' => $user->id,
                'block_id' => $block->id,
                'flat_id' => $flat->id,
                'type' => $resType,
                'move_in_date' => !empty($data['move_in_date']) ? $data['move_in_date'] : date('Y-m-d'),
                'move_out_date' => !empty($data['move_out_date']) ? $data['move_out_date'] : null,
            ]);
        } elseif ($table === 'flat_types') {
            $ftStatus = !empty($data['status']) ? strtolower(trim($data['status'])) : 'active';
            if (!in_array($ftStatus, ['active', 'inactive'])) $ftStatus = 'active';

            FlatType::create([
                'name' => $data['name'],
                'owner_maintenance_fee' => (float)($data['owner_maintenance_fee'] ?? 0),
                'rental_maintenance_fee' => (float)($data['rental_maintenance_fee'] ?? 0),
                'description' => !empty($data['description']) ? $data['description'] : null,
                'status' => $ftStatus,
            ]);
        } elseif ($table === 'expense_categories') {
            $ecStatus = !empty($data['status']) ? strtolower(trim($data['status'])) : 'active';
            if (!in_array($ecStatus, ['active', 'inactive'])) $ecStatus = 'active';

            ExpenseCategory::create([
                'title' => $data['title'],
                'slug' => !empty($data['slug']) ? $data['slug'] : Str::slug($data['title']),
                'status' => $ecStatus,
            ]);
        } elseif ($table === 'complaints') {
            $user = User::where('email', $data['user_email'])->first();

            $cat = !empty($data['category']) ? trim($data['category']) : 'other';
            if (stripos($cat, 'maint') !== false) $cat = 'Maintenance Issues';
            elseif (stripos($cat, 'secu') !== false) $cat = 'Security Issues';
            elseif (stripos($cat, 'clean') !== false || stripos($cat, 'house') !== false) $cat = 'Cleanliness & Housekeeping';
            elseif (stripos($cat, 'facil') !== false || stripos($cat, 'common') !== false) $cat = 'Common Facilities';
            elseif (!in_array($cat, ['Maintenance Issues', 'Security Issues', 'Cleanliness & Housekeeping', 'Common Facilities', 'other'])) {
                $cat = 'other';
            }

            $rawStatus = !empty($data['status']) ? strtolower(trim($data['status'])) : config('status.complaints.pending');
            if ($rawStatus === 'in progress' || $rawStatus === 'in_progress' || stripos($rawStatus, 'progress') !== false) $rawStatus = config('status.complaints.in_progress');
            elseif (stripos($rawStatus, 'resolv') !== false) $rawStatus = config('status.complaints.resolved');
            else $rawStatus = config('status.complaints.pending');

            Complain::create([
                'user_id' => $user->id,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'category' => $cat,
                'status' => $rawStatus,
                'resolution_notes' => !empty($data['resolution_notes']) ? $data['resolution_notes'] : null,
            ]);
        } elseif ($table === 'expenses') {
            $cat = null;
            if (!empty($data['category_title'])) {
                $cat = ExpenseCategory::firstOrCreate(['title' => $data['category_title']], ['slug' => Str::slug($data['category_title']), 'status' => 'active']);
            }
            $expUser = !empty($data['user_email']) ? User::where('email', $data['user_email'])->first() : null;
            Expense::create([
                'user_id' => $expUser ? $expUser->id : (Auth::id() ?? 1),
                'category_id' => $cat ? $cat->id : null,
                'title' => $data['title'],
                'total_amount' => (float)$data['total_amount'],
                'expense_date' => !empty($data['expense_date']) ? $data['expense_date'] : date('Y-m-d'),
                'invoice' => !empty($data['invoice']) ? $data['invoice'] : null,
            ]);
        } elseif ($table === 'maintenances') {
            $bc = !empty($data['billing_cycle']) ? strtolower(trim($data['billing_cycle'])) : 'monthly';
            if (!in_array($bc, ['monthly', 'quarterly', 'yearly'])) $bc = 'monthly';

            $st = !empty($data['status']) ? strtolower(trim($data['status'])) : 'draft';
            if (!in_array($st, ['draft', 'published'])) $st = 'draft';

            Maintenance::create([
                'month' => $data['month'],
                'year' => $data['year'],
                'billing_cycle' => $bc,
                'due_date' => !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+15 days')),
                'total_additional_cost' => (float)($data['total_additional_cost'] ?? 0),
                'status' => $st,
            ]);
        } elseif ($table === 'maintenance_bills') {
            $user = User::where('email', $data['user_email'])->first();
            $block = Block::where('block_name', $data['block_name'])->first();
            $flat = Flat::where('block_id', $block->id)->where('flat_no', $data['flat_no'])->first();
            $maintenance = Maintenance::latest()->first();

            $st = !empty($data['status']) ? strtolower(trim($data['status'])) : 'due';
            if (!in_array($st, ['paid', 'due', 'pending'])) $st = 'due';

            MaintenanceBill::create([
                'maintenance_id' => $maintenance ? $maintenance->id : 1,
                'user_id' => $user->id,
                'block_id' => $block->id,
                'flat_id' => $flat->id,
                'amount' => (float)($data['amount'] ?? $data['total_amount']),
                'penalty_amount' => (float)($data['penalty_amount'] ?? 0),
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'total_amount' => (float)$data['total_amount'],
                'generated_date' => !empty($data['generated_date']) ? $data['generated_date'] : date('Y-m-d'),
                'paid_at' => !empty($data['paid_at']) ? $data['paid_at'] : null,
                'payment_method' => !empty($data['payment_method']) ? $data['payment_method'] : null,
                'transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : null,
                'payment_slip' => !empty($data['payment_slip']) ? $data['payment_slip'] : null,
                'status' => $st,
            ]);
        } elseif ($table === 'name_transfer_bills') {
            $block = Block::where('block_name', $data['block_name'])->first();
            $flat = Flat::where('block_id', $block->id)->where('flat_no', $data['flat_no'])->first();
            $oldOwner = User::where('email', $data['old_owner_email'])->first();
            $newOwner = User::where('email', $data['new_owner_email'])->first();

            $st = !empty($data['status']) ? strtolower(trim($data['status'])) : 'pending';
            if (!in_array($st, ['pending', 'paid', 'cancelled'])) $st = 'pending';

            NameTransferBill::create([
                'flat_id' => $flat->id,
                'old_owner_id' => $oldOwner->id,
                'new_owner_id' => $newOwner->id,
                'amount' => (float)$data['amount'],
                'transfer_date' => !empty($data['transfer_date']) ? $data['transfer_date'] : date('Y-m-d'),
                'paid_at' => !empty($data['paid_at']) ? $data['paid_at'] : null,
                'payment_method' => !empty($data['payment_method']) ? $data['payment_method'] : null,
                'transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : null,
                'payment_slip' => !empty($data['payment_slip']) ? $data['payment_slip'] : null,
                'status' => $st,
                'is_approved' => isset($data['is_approved']) && $data['is_approved'] !== '' ? (int)$data['is_approved'] : ($st === 'paid' ? 1 : 0),
            ]);
        }
    }

    public function exportMaster(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403);
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            DB::disableQueryLog();
            $selectedTables = $request->input('tables');
            if (!is_array($selectedTables) || empty($selectedTables)) {
                $selectedTables = array_keys($this->getTableConfigs());
            }

            $configs = $this->getTableConfigs();
            $dependencyOrder = ['blocks', 'flat_types', 'flats', 'users', 'residents', 'expense_categories', 'expenses', 'complaints', 'maintenances', 'maintenance_bills', 'name_transfer_bills'];

            $orderedTables = [];
            foreach ($dependencyOrder as $tbl) {
                if (in_array($tbl, $selectedTables) && isset($configs[$tbl])) {
                    $orderedTables[] = $tbl;
                }
            }

            $headers = [
                'Content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename=master_database_backup_' . date('Ymd_His') . '.xlsx',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($orderedTables, $configs) {
                $writer = new Writer();
                $writer->openToFile('php://output');

                $sheet = $writer->getCurrentSheet();
                $sheet->setName('Master Horizontal');

                $row1 = [];
                $row2 = [];

                foreach ($orderedTables as $table) {
                    $config = $configs[$table];
                    $labels = $config['labels'];
                    $colCount = count($labels);

                    $row1[] = '### MODULE: ' . strtoupper($table) . ' ###';
                    for ($i = 1; $i < $colCount; $i++) {
                        $row1[] = '';
                    }
                    $row1[] = ''; // Gap column

                    foreach ($labels as $lbl) {
                        $row2[] = $lbl;
                    }
                    $row2[] = ''; // Gap column
                }

                if (!empty($row1)) {
                    array_pop($row1);
                    array_pop($row2);
                }

                $writer->addRow(Row::fromValues($row1));
                $writer->addRow(Row::fromValues($row2));

                // Fetch records
                $tableRecords = [];
                $maxRows = 0;

                foreach ($orderedTables as $table) {
                    $modelClass = $configs[$table]['model'];
                    $recs = $modelClass::all()->values();
                    $tableRecords[$table] = $recs;
                    if ($recs->count() > $maxRows) {
                        $maxRows = $recs->count();
                    }
                }

                for ($r = 0; $r < $maxRows; $r++) {
                    $dataRow = [];
                    foreach ($orderedTables as $table) {
                        $config = $configs[$table];
                        $recs = $tableRecords[$table];
                        $record = $recs[$r] ?? null;

                        foreach ($config['headers'] as $h) {
                            if ($record) {
                                $dataRow[] = $this->getRecordExportValue($table, $h, $record);
                            } else {
                                $dataRow[] = '';
                            }
                        }
                        $dataRow[] = ''; // Gap
                    }
                    if (!empty($dataRow)) array_pop($dataRow);
                    $writer->addRow(Row::fromValues($dataRow));
                }

                $writer->close();
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in GlobalImportExportController@exportMaster: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred during master export: ' . $e->getMessage());
        }
    }

    public function templateMaster(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403);
        try {
            $selectedTables = $request->input('tables');
            if (!is_array($selectedTables) || empty($selectedTables)) {
                $selectedTables = array_keys($this->getTableConfigs());
            }

            $configs = $this->getTableConfigs();
            $dependencyOrder = ['blocks', 'flat_types', 'flats', 'users', 'residents', 'expense_categories', 'expenses', 'complaints', 'maintenances', 'maintenance_bills', 'name_transfer_bills'];

            $orderedTables = [];
            foreach ($dependencyOrder as $tbl) {
                if (in_array($tbl, $selectedTables) && isset($configs[$tbl])) {
                    $orderedTables[] = $tbl;
                }
            }

            $headers = [
                'Content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename=master_database_template.xlsx',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($orderedTables, $configs) {
                $writer = new Writer();
                $writer->openToFile('php://output');

                $sheet = $writer->getCurrentSheet();
                $sheet->setName('Master Template');

                $row1 = [];
                $row2 = [];

                foreach ($orderedTables as $table) {
                    $config = $configs[$table];
                    $labels = $config['labels'];
                    $colCount = count($labels);

                    $row1[] = '### MODULE: ' . strtoupper($table) . ' ###';
                    for ($i = 1; $i < $colCount; $i++) {
                        $row1[] = '';
                    }
                    $row1[] = ''; // Gap column

                    foreach ($labels as $lbl) {
                        $row2[] = $lbl;
                    }
                    $row2[] = ''; // Gap column
                }

                if (!empty($row1)) {
                    array_pop($row1);
                    array_pop($row2);
                }

                $writer->addRow(Row::fromValues($row1));
                $writer->addRow(Row::fromValues($row2));

                // Sample row
                $sampleRow = [];
                $hasSample = false;
                foreach ($orderedTables as $table) {
                    $config = $configs[$table];
                    if (isset($config['sample']) && is_array($config['sample'])) {
                        foreach ($config['sample'] as $sVal) $sampleRow[] = $sVal;
                        $hasSample = true;
                    } else {
                        for ($i = 0; $i < count($config['labels']); $i++) $sampleRow[] = '';
                    }
                    $sampleRow[] = '';
                }
                if (!empty($sampleRow)) array_pop($sampleRow);
                if ($hasSample) {
                    $writer->addRow(Row::fromValues($sampleRow));
                }

                $writer->close();
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in GlobalImportExportController@templateMaster: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred downloading master template: ' . $e->getMessage());
        }
    }

    public function previewMaster(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        DB::disableQueryLog();
        $request->validate([
            'import_file' => 'required|file|max:20480',
        ]);

        $configs = $this->getTableConfigs();
        $file = $request->file('import_file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('temp_imports', 'master_' . time() . '.' . $ext);

        try {
            $reader = $ext === 'csv' ? new CSVReader() : new Reader();
            $reader->open(Storage::path($path));

            $allRows = [];
            $consecutiveEmpty = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->toArray();
                    $isEmptyRow = true;
                    foreach ($cells as &$c) {
                        if ($c instanceof \DateTime) {
                            $c = $c->format('Y-m-d');
                        }
                        if (trim((string)$c) !== '') $isEmptyRow = false;
                    }
                    if ($isEmptyRow) {
                        $consecutiveEmpty++;
                        if ($consecutiveEmpty > 20 && count($allRows) >= 2) break;
                    } else {
                        $consecutiveEmpty = 0;
                    }
                    $allRows[] = $cells;
                }
                break; // First sheet
            }
            $reader->close();

            while (!empty($allRows) && count($allRows) > 2) {
                $lastRow = end($allRows);
                $isEmpty = true;
                foreach ($lastRow as $c) {
                    if (trim((string)$c) !== '') {
                        $isEmpty = false;
                        break;
                    }
                }
                if ($isEmpty) array_pop($allRows);
                else break;
            }

            if (count($allRows) < 2) {
                return response()->json(['success' => false, 'message' => 'Spreadsheet is empty or missing headers.'], 400);
            }

            $row1 = $allRows[0];
            $row2 = $allRows[1];
            $colMap = [];

            foreach ($row1 as $colIdx => $cellVal) {
                $val = trim((string)$cellVal);
                if ($val === '') continue;
                $tblName = preg_match('/MODULE:\s*([A-Z_\s]+)/i', $val, $m) ? $m[1] : $val;
                $tblName = strtolower(str_replace(' ', '_', trim($tblName)));
                if (isset($configs[$tblName])) {
                    $colMap[$tblName] = [
                        'start' => $colIdx,
                        'end' => $colIdx + count($configs[$tblName]['headers']) - 1
                    ];
                }
            }

            $sheetsSummary = [];
            foreach ($configs as $tblName => $config) {
                if (!isset($colMap[$tblName])) continue;
                $range = $colMap[$tblName];

                $headers = array_slice($row2, $range['start'], $range['end'] - $range['start'] + 1);
                $previewRows = [];
                $validCount = 0;

                for ($r = 2; $r < count($allRows); $r++) {
                    $cells = array_slice($allRows[$r], $range['start'], $range['end'] - $range['start'] + 1);
                    $isEmpty = true;
                    foreach ($cells as $v) {
                        if (trim((string)$v) !== '') {
                            $isEmpty = false;
                            break;
                        }
                    }
                    if ($isEmpty) continue;

                    $validCount++;
                    $previewRows[] = $cells;
                }

                $sheetsSummary[] = [
                    'table' => $tblName,
                    'label' => $config['label'],
                    'record_count' => $validCount,
                    'headers' => $headers,
                    'preview_rows' => $previewRows,
                ];
            }

            return response()->json([
                'success' => true,
                'file_path' => $path,
                'sheets_summary' => $sheetsSummary,
            ]);
        } catch (\Exception $e) {
            Storage::delete($path);
            return response()->json([
                'success' => false,
                'message' => 'Error reading spreadsheet file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processMaster(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        DB::disableQueryLog();
        $request->validate([
            'file_path' => 'required|string',
        ]);

        $path = $request->file_path;
        if (!Storage::exists($path)) {
            return response()->json(['success' => false, 'message' => 'Uploaded file expired or not found. Please upload again.']);
        }

        $configs = $this->getTableConfigs();
        $dependencyOrder = ['blocks', 'flat_types', 'flats', 'users', 'residents', 'expense_categories', 'expenses', 'complaints', 'maintenances', 'maintenance_bills', 'name_transfer_bills'];

        try {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $reader = strtolower($ext) === 'csv' ? new CSVReader() : new Reader();
            $reader->open(Storage::path($path));

            $allRows = [];
            $consecutiveEmpty = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->toArray();
                    $isEmptyRow = true;
                    foreach ($cells as &$c) {
                        if ($c instanceof \DateTime) {
                            $c = $c->format('Y-m-d');
                        }
                        if (trim((string)$c) !== '') $isEmptyRow = false;
                    }
                    if ($isEmptyRow) {
                        $consecutiveEmpty++;
                        if ($consecutiveEmpty > 20 && count($allRows) >= 2) break;
                    } else {
                        $consecutiveEmpty = 0;
                    }
                    $allRows[] = $cells;
                }
                break;
            }
            $reader->close();

            while (

            !empty($allRows) && count($allRows) > 2) {
                $lastRow = end($allRows);
                $isEmpty = true;
                foreach ($lastRow as $c) {
                    if (trim((string)$c) !== '') {
                        $isEmpty = false;
                        break;
                    }
                }
                if ($isEmpty) array_pop($allRows);
                else break;
            }

            if (count($allRows) < 2) {
                return response()->json(['success' => false, 'message' => 'Spreadsheet is empty or missing headers.']);
            }

            $row1 = $allRows[0];
            $colMap = [];

            foreach ($row1 as $colIdx => $cellVal) {
                $val = trim((string)$cellVal);
                if ($val === '') continue;
                $tblName = preg_match('/MODULE:\s*([A-Z_\s]+)/i', $val, $m) ? $m[1] : $val;
                $tblName = strtolower(str_replace(' ', '_', trim($tblName)));
                if (isset($configs[$tblName])) {
                    $colMap[$tblName] = [
                        'start' => $colIdx,
                        'end' => $colIdx + count($configs[$tblName]['headers']) - 1
                    ];
                }
            }

            DB::beginTransaction();
            $importedCount = 0;
            $failedRows = [];

            foreach ($dependencyOrder as $table) {
                if (!isset($colMap[$table])) continue;
                $config = $configs[$table];
                $expectedHeaders = $config['headers'];
                $range = $colMap[$table];

                for ($r = 2; $r < count($allRows); $r++) {
                    $rowNum = $r + 1; // 1-indexed Excel row number
                    $rawCells = array_slice($allRows[$r], $range['start'], $range['end'] - $range['start'] + 1);

                    $isEmpty = true;
                    foreach ($rawCells as $v) {
                        if (trim((string)$v) !== '') {
                            $isEmpty = false;
                            break;
                        }
                    }
                    if ($isEmpty) continue;

                    $rowValues = [];
                    foreach ($expectedHeaders as $colIdx => $dbField) {
                        $rowValues[$dbField] = isset($rawCells[$colIdx]) ? trim((string)$rawCells[$colIdx]) : null;
                    }

                    $recId = $this->extractRecordIdentifier($rowValues);
                    $missingReq = false;
                    foreach ($config['required'] as $reqField) {
                        if (!isset($rowValues[$reqField]) || $rowValues[$reqField] === '') {
                            $failedRows[] = [
                                'sheet' => $config['label'],
                                'row' => $rowNum,
                                'record' => $recId,
                                'reason' => "Required field '{$reqField}' is missing.",
                            ];
                            $missingReq = true;
                            break;
                        }
                    }
                    if ($missingReq) continue;

                    $conflictError = $this->validateRowConflicts($table, $rowValues, $rowNum);
                    if ($conflictError) {
                        $failedRows[] = [
                            'sheet' => $config['label'],
                            'row' => $rowNum,
                            'record' => $recId,
                            'reason' => preg_replace('/^Row \d+:\s*/', '', $conflictError),
                        ];
                        continue;
                    }

                    try {
                        $this->insertTableRecord($table, $rowValues);
                        $importedCount++;
                    } catch (\Exception $ex) {
                        $failedRows[] = [
                            'sheet' => $config['label'],
                            'row' => $rowNum,
                            'record' => $recId,
                            'reason' => "Database error: " . $ex->getMessage(),
                        ];
                    }
                }
            }

            DB::commit();
            Storage::delete($path);

            $failedCount = count($failedRows);

            return response()->json([
                'success' => $failedCount === 0,
                'success_count' => $importedCount,
                'failed_count' => $failedCount,
                'failed_records' => $failedRows,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($path);
            return response()->json([
                'success' => false,
                'message' => 'Master import processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function extractRecordIdentifier($rowValues)
    {
        if (isset($rowValues['block_name']) && isset($rowValues['flat_no'])) {
            return $rowValues['block_name'] . ' - ' . $rowValues['flat_no'];
        }
        return $rowValues['name'] ?? $rowValues['title'] ?? $rowValues['block_name'] ?? $rowValues['flat_no'] ?? $rowValues['category_name'] ?? $rowValues['email'] ?? $rowValues['phone'] ?? $rowValues['user_email'] ?? '-';
    }
}
