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
use Illuminate\Validation\ValidationException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Writer\XLSX\Writer as XLSXWriter;
use OpenSpout\Writer\CSV\Writer as CSVWriter;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class GlobalImportExportController extends Controller
{
    /**
     * Defines configuration for each importable/exportable table.
     *
     * @return array
     */
    private function getTableConfigs(): array
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
                'label' => \App\Models\Setting::label('unit_types', 'Flat Types'),
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

    /**
     * Handles common exceptions and returns appropriate responses.
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

        Log::error("Error in GlobalImportExportController@{$methodName}: " . $e->getMessage());

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
     * Exports data for a given table in either CSV or XLSX format.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403, 'Unauthorized access.');

        try {
            // Increase execution time and memory limit for large exports
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $table = $request->input('table', 'blocks');
            $format = strtolower((string) $request->input('format', 'excel'));

            $config = $this->getValidatedTableConfig($table);

            $ext = $format === 'csv' ? 'csv' : 'xlsx';
            $contentType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

            $headers = $this->getExportHeaders($table, $ext, $contentType);

            $callback = $this->createExportCallback($table, $config, $format);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Validates the requested table and returns its configuration.
     *
     * @param string $table
     * @return array
     */
    private function getValidatedTableConfig(string $table): array
    {
        $configs = $this->getTableConfigs();
        if (!isset($configs[$table])) {
            abort(404, 'Selected module not found.');
        }
        return $configs[$table];
    }

    /**
     * Generates HTTP headers for file download.
     *
     * @param string $table
     * @param string $ext
     * @param string $contentType
     * @return array
     */
    private function getExportHeaders(string $table, string $ext, string $contentType): array
    {
        return [
            'Content-type' => $contentType,
            'Content-Disposition' => 'attachment; filename=' . $table . '_export_' . date('Ymd_His') . '.' . $ext,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
    }

    /**
     * Creates the callback function for streaming the export file.
     *
     * @param string $table
     * @param array $config
     * @param string $format
     * @return \Closure
     */
    private function createExportCallback(string $table, array $config, string $format): \Closure
    {
        return function () use ($table, $config, $format) {
            $writer = $format === 'csv' ? new CSVWriter() : new XLSXWriter();
            $writer->openToFile('php://output');

            // Add header row
            $writer->addRow(Row::fromValues($config['labels']));

            // Fetch all records for the model
            $modelClass = $config['model'];
            $records = $modelClass::all();

            // Add data rows
            foreach ($records as $record) {
                $rowValues = [];
                foreach ($config['headers'] as $header) {
                    $rowValues[] = $this->getRecordExportValue($table, $header, $record);
                }
                $writer->addRow(Row::fromValues($rowValues));
            }

            $writer->close();
        };
    }

    /**
     * Retrieves the export value for a specific record and header.
     *
     * @param string $table
     * @param string $header
     * @param mixed $record
     * @return string|int|float
     */
    private function getRecordExportValue(string $table, string $header, $record)
    {
        // Handle specific relationships for different tables
        switch ($table) {
            case 'flats':
                if ($header === 'block_name') return $record->block->block_name ?? 'N/A';
                if ($header === 'flat_type_name') return $record->flatType->name ?? 'N/A';
                break;
            case 'residents':
                if ($header === 'name') return $record->user->name ?? 'N/A';
                if ($header === 'email') return $record->user->email ?? 'N/A';
                if ($header === 'phone') return $record->user->phone ?? 'N/A';
                if ($header === 'aadhar_id') return $record->user->aadhar_id ?? 'N/A';
                if ($header === 'block_name') return $record->block->block_name ?? 'N/A';
                if ($header === 'flat_no') return $record->flat->flat_no ?? 'N/A';
                break;
            case 'complaints':
                if ($header === 'user_email') return $record->user->email ?? 'N/A';
                break;
            case 'expenses':
                if ($header === 'category_title') return $record->category->title ?? 'N/A';
                if ($header === 'user_email') return $record->user->email ?? 'N/A';
                break;
            case 'maintenance_bills':
                if ($header === 'user_email') return $record->user->email ?? 'N/A';
                if ($header === 'block_name') return $record->flat->block->block_name ?? ($record->block->block_name ?? 'N/A');
                if ($header === 'flat_no') return $record->flat->flat_no ?? 'N/A';
                break;
            case 'name_transfer_bills':
                if ($header === 'block_name') return $record->flat->block->block_name ?? 'N/A';
                if ($header === 'flat_no') return $record->flat->flat_no ?? 'N/A';
                if ($header === 'old_owner_email') return $record->oldOwner->email ?? 'N/A';
                if ($header === 'new_owner_email') return $record->newOwner->email ?? 'N/A';
                break;
        }

        // Default to returning the direct attribute value
        return $record->{$header} ?? '';
    }

    /**
     * Downloads an Excel template for importing data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadTemplate(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403, 'Unauthorized access.');

        try {
            $table = $request->input('table', 'blocks');
            $config = $this->getValidatedTableConfig($table);

            $headers = $this->getTemplateDownloadHeaders($table);

            $callback = function () use ($config) {
                $writer = new XLSXWriter();
                $writer->openToFile('php://output');
                $writer->addRow(Row::fromValues($config['labels']));
                $writer->close();
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Generates HTTP headers for template file download.
     *
     * @param string $table
     * @return array
     */
    private function getTemplateDownloadHeaders(string $table): array
    {
        return [
            'Content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=' . $table . '_import_template.xlsx',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
    }

    /**
     * Previews the data from an uploaded import file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewImport(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403, 'Unauthorized access.');

        try {
            $maxSizeKb = (float) \App\Models\Setting::get('max_document_size', 2) * 1024;
            $request->validate([
                'table' => 'required|string',
                'import_file' => 'required|file|max:' . $maxSizeKb,
            ]);

            $table = $request->table;
            $config = $this->getValidatedTableConfig($table);

            $file = $request->file('import_file');
            $filePath = $this->storeUploadedFile($file, $table);

            list($headers, $previewRows) = $this->readImportFile($filePath, $file->getClientOriginalExtension());

            // Validate headers against config labels
            $validationErrors = $this->validateImportHeaders($headers, $config['labels']);
            if (!empty($validationErrors)) {
                return response()->json(['success' => false, 'message' => 'Header mismatch: ' . implode(', ', $validationErrors)]);
            }

            // Further validation of data rows can be added here if needed

            return response()->json([
                'success' => true,
                'message' => 'File preview generated successfully.',
                'headers' => $headers,
                'rows' => array_slice($previewRows, 0, 10), // Show first 10 rows for preview
                'total_rows' => count($previewRows),
                'temp_file_path' => $filePath, // Pass temp path for actual import
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Stores the uploaded import file temporarily.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $table
     * @return string
     */
    private function storeUploadedFile($file, string $table): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        return $file->storeAs('temp_imports', 'global_' . $table . '_' . time() . '.' . $ext);
    }

    /**
     * Reads data from the import file.
     *
     * @param string $filePath
     * @param string $extension
     * @return array
     */
    private function readImportFile(string $filePath, string $extension): array
    {
        $reader = $extension === 'csv' ? new CSVReader() : new XLSXReader();
        $reader->open(Storage::path($filePath));

        $headers = [];
        $dataRows = [];
        $rowCount = 0;
        $consecutiveEmpty = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();

                if ($rowCount === 0) {
                    $headers = $cells;
                } else {
                    $isEmptyRow = true;
                    foreach ($cells as &$cell) {
                        if ($cell instanceof \DateTime) {
                            $cell = $cell->format('Y-m-d');
                        }
                        if (trim((string)$cell) !== '') {
                            $isEmptyRow = false;
                        }
                    }

                    if ($isEmptyRow) {
                        $consecutiveEmpty++;
                        if ($consecutiveEmpty >= 5) {
                            break; // Stop if 5 consecutive empty rows are found
                        }
                    } else {
                        $consecutiveEmpty = 0;
                        $dataRows[] = $cells;
                    }
                }
                $rowCount++;
            }
        }
        $reader->close();
        return [$headers, $dataRows];
    }

    /**
     * Validates import file headers against expected labels.
     *
     * @param array $fileHeaders
     * @param array $configLabels
     * @return array
     */
    private function validateImportHeaders(array $fileHeaders, array $configLabels): array
    {
        $errors = [];
        foreach ($configLabels as $index => $label) {
            // Remove ' (*)' from required labels for comparison
            $cleanConfigLabel = str_replace(' (*)', '', $label);
            if (!isset($fileHeaders[$index]) || strtolower(trim($fileHeaders[$index])) !== strtolower(trim($cleanConfigLabel))) {
                $errors[] = "Expected '{$cleanConfigLabel}' at column " . ($index + 1) . ", found '" . ($fileHeaders[$index] ?? 'nothing') . "'.";
            }
        }
        return $errors;
    }

    /**
     * Processes the actual import of data from a temporary file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processImport(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403, 'Unauthorized access.');

        try {
            $request->validate([
                'table' => 'required|string',
                'temp_file_path' => 'required|string',
            ]);

            $table = $request->table;
            $filePath = $request->temp_file_path;
            $config = $this->getValidatedTableConfig($table);

            // Ensure the file exists and is accessible
            if (!Storage::exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'Import file not found or expired.'], 400);
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            list($headers, $dataRows) = $this->readImportFile($filePath, $extension);

            // Re-validate headers to be safe
            $validationErrors = $this->validateImportHeaders($headers, $config['labels']);
            if (!empty($validationErrors)) {
                Storage::delete($filePath);
                return response()->json(['success' => false, 'message' => 'Header mismatch during processing: ' . implode(', ', $validationErrors)]);
            }

            $importResults = $this->importDataRows($table, $config, $dataRows);

            // Clean up the temporary file
            Storage::delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully.',
                'imported_count' => $importResults['imported_count'],
                'failed_count' => $importResults['failed_count'],
                'errors' => $importResults['errors'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            // Clean up the temporary file in case of an error during processing
            if (isset($filePath) && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Imports data rows into the database.
     *
     * @param string $table
     * @param array $config
     * @param array $dataRows
     * @return array
     */
    private function importDataRows(string $table, array $config, array $dataRows): array
    {
        $importedCount = 0;
        $failedCount = 0;
        $errors = [];
        $modelClass = $config['model'];
        $headers = $config['headers'];
        $requiredFields = $config['required'];

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowIndex => $row) {
                $rowData = array_combine($headers, $row);
                $rowData = $this->prepareImportData($table, $rowData);

                // Basic validation for required fields
                $rowErrors = [];
                foreach ($requiredFields as $field) {
                    if (empty($rowData[$field])) {
                        $rowErrors[] = "Missing required field: {$field}";
                    }
                }

                if (!empty($rowErrors)) {
                    $failedCount++;
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . implode(', ', $rowErrors);
                    continue;
                }

                try {
                    // Specific handling for different tables
                    switch ($table) {
                        case 'users':
                            // Hash password before creating user
                            if (isset($rowData['password'])) {
                                $rowData['password'] = Hash::make($rowData['password']);
                            }
                            // Ensure role is valid
                            $validRoles = ['admin', 'manager', 'accountant', 'security', 'resident'];
                            if (!in_array(strtolower($rowData['role']), $validRoles)) {
                                throw new \Exception('Invalid role specified for user.');
                            }
                            $user = $modelClass::create($rowData);
                            // Assign role to user
                            $user->assignRole($rowData['role']);
                            break;
                        case 'residents':
                            // Find or create user for resident
                            $user = User::firstOrCreate(
                                ['email' => $rowData['email']],
                                [
                                    'name' => $rowData['name'],
                                    'phone' => $rowData['phone'] ?? null,
                                    'aadhar_id' => $rowData['aadhar_id'] ?? null,
                                    'password' => Hash::make(Str::random(10)), // Generate random password
                                    'status' => 'active',
                                ]
                            );
                            $user->assignRole('resident');

                            // Find block and flat
                            $block = Block::where('block_name', $rowData['block_name'])->firstOrFail();
                            $flat = Flat::where('block_id', $block->id)->where('flat_no', $rowData['flat_no'])->firstOrFail();

                            $modelClass::create([
                                'user_id' => $user->id,
                                'block_id' => $block->id,
                                'flat_id' => $flat->id,
                                'type' => $rowData['type'],
                                'move_in_date' => $rowData['move_in_date'] ?? null,
                                'move_out_date' => $rowData['move_out_date'] ?? null,
                            ]);
                            $flat->syncOccupancyStatus();
                            break;
                        case 'flats':
                            $block = Block::where('block_name', $rowData['block_name'])->firstOrFail();
                            $flatType = FlatType::where('name', $rowData['flat_type_name'])->first(); // Optional flat type
                            $modelClass::create([
                                'block_id' => $block->id,
                                'flat_no' => $rowData['flat_no'],
                                'floor_no' => $rowData['floor_no'] ?? null,
                                'flat_type_id' => $flatType->id ?? null,
                                'status' => $rowData['status'] ?? 'vacant',
                            ]);
                            break;
                        case 'complaints':
                            $user = User::where('email', $rowData['user_email'])->firstOrFail();
                            $modelClass::create([
                                'user_id' => $user->id,
                                'subject' => $rowData['subject'],
                                'description' => $rowData['description'],
                                'category' => $rowData['category'] ?? 'other',
                                'status' => $rowData['status'] ?? 'pending',
                                'resolution_notes' => $rowData['resolution_notes'] ?? null,
                            ]);
                            break;
                        case 'expenses':
                            $category = ExpenseCategory::where('title', $rowData['category_title'])->first(); // Optional category
                            $user = null;
                            if (isset($rowData['user_email'])) {
                                $user = User::where('email', $rowData['user_email'])->first();
                            }
                            $modelClass::create([
                                'title' => $rowData['title'],
                                'total_amount' => $rowData['total_amount'],
                                'category_id' => $category->id ?? null,
                                'expense_date' => $rowData['expense_date'] ?? now(),
                                'invoice' => $rowData['invoice'] ?? null,
                                'user_id' => $user->id ?? null,
                            ]);
                            break;
                        case 'maintenance_bills':
                            $user = User::where('email', $rowData['user_email'])->firstOrFail();
                            $block = Block::where('block_name', $rowData['block_name'])->firstOrFail();
                            $flat = Flat::where('block_id', $block->id)->where('flat_no', $rowData['flat_no'])->firstOrFail();
                            $modelClass::create([
                                'user_id' => $user->id,
                                'flat_id' => $flat->id,
                                'amount' => $rowData['amount'],
                                'penalty_amount' => $rowData['penalty_amount'] ?? 0,
                                'discount_amount' => $rowData['discount_amount'] ?? 0,
                                'total_amount' => $rowData['total_amount'],
                                'generated_date' => $rowData['generated_date'] ?? now(),
                                'paid_at' => $rowData['paid_at'] ?? null,
                                'payment_method' => $rowData['payment_method'] ?? null,
                                'transaction_id' => $rowData['transaction_id'] ?? null,
                                'payment_slip' => $rowData['payment_slip'] ?? null,
                                'status' => $rowData['status'] ?? 'pending',
                            ]);
                            break;
                        case 'name_transfer_bills':
                            $block = Block::where('block_name', $rowData['block_name'])->firstOrFail();
                            $flat = Flat::where('block_id', $block->id)->where('flat_no', $rowData['flat_no'])->firstOrFail();
                            $oldOwner = User::where('email', $rowData['old_owner_email'])->firstOrFail();
                            $newOwner = User::firstOrCreate(
                                ['email' => $rowData['new_owner_email']],
                                [
                                    'name' => 'New Owner',
                                    'password' => Hash::make(Str::random(10)),
                                    'status' => 'active',
                                ]
                            );
                            $newOwner->assignRole('resident');

                            $modelClass::create([
                                'flat_id' => $flat->id,
                                'old_owner_id' => $oldOwner->id,
                                'new_owner_id' => $newOwner->id,
                                'amount' => $rowData['amount'],
                                'transfer_date' => $rowData['transfer_date'] ?? now(),
                                'paid_at' => $rowData['paid_at'] ?? null,
                                'payment_method' => $rowData['payment_method'] ?? null,
                                'transaction_id' => $rowData['transaction_id'] ?? null,
                                'payment_slip' => $rowData['payment_slip'] ?? null,
                                'is_approved' => $rowData['is_approved'] ?? 0,
                                'status' => $rowData['status'] ?? 'pending',
                            ]);
                            break;
                        default:
                            $modelClass::create($rowData);
                            break;
                    }
                    $importedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    Log::warning("Import failed for row " . ($rowIndex + 2) . ": " . $e->getMessage());
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Transaction failed during import: " . $e->getMessage());
            $errors[] = "A critical error occurred during import: " . $e->getMessage();
            $failedCount = count($dataRows) - $importedCount; // All remaining failed
        }

        return [
            'imported_count' => $importedCount,
            'failed_count' => $failedCount,
            'importedCount' => $importedCount,
            'failedCount' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Prepares row data for import, handling specific transformations.
     *
     * @param string $table
     * @param array $rowData
     * @return array
     */
    private function prepareImportData(string $table, array $rowData): array
    {
        // Convert string representations to boolean/integer where necessary
        if (isset($rowData['is_approved'])) {
            $rowData['is_approved'] = filter_var($rowData['is_approved'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (is_null($rowData['is_approved'])) {
                $rowData['is_approved'] = 0; // Default to false if invalid
            }
        }

        // Map horizontal structure headers (like Villa No, Shop No, Office No, Unit No) directly into flat_no
        if (in_array($table, ['flats', 'residents', 'maintenance_bills', 'transfer_fees'])) {
            if (empty($rowData['flat_no'])) {
                foreach (['villa_no', 'shop_no', 'office_no', 'row_house_no', 'unit_no', 'property_unit_no', 'villa no', 'shop no', 'office no', 'unit no'] as $alias) {
                    if (!empty($rowData[$alias])) {
                        $rowData['flat_no'] = $rowData[$alias];
                        break;
                    }
                }
            }
            // If importing flats specifically, default floor_no to 0 if not provided (for villas/horizontal structures)
            if ($table === 'flats' && (!isset($rowData['floor_no']) || $rowData['floor_no'] === '')) {
                $rowData['floor_no'] = 0;
            }
        }

        return $rowData;
    }

    /**
     * Exports all tables into a single multi-sheet master Excel file.
     */
    public function exportMaster(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403, 'Unauthorized access.');

        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $configs = $this->getTableConfigs();
            $selectedTables = $request->input('tables');
            if (is_array($selectedTables) && !empty($selectedTables)) {
                $configs = array_intersect_key($configs, array_flip($selectedTables));
            }

            $ext = 'xlsx';
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

            $headers = [
                'Content-type' => $contentType,
                'Content-Disposition' => 'attachment; filename=society_master_backup_' . date('Ymd_His') . '.' . $ext,
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($configs) {
                $writer = new XLSXWriter();
                $writer->openToFile('php://output');
                $isFirst = true;

                foreach ($configs as $table => $config) {
                    if ($isFirst) {
                        $sheet = $writer->getCurrentSheet();
                        $isFirst = false;
                    } else {
                        $sheet = $writer->addNewSheetAndMakeItCurrent();
                    }
                    $sheetName = preg_replace('/[^\w\s-]/', '', substr($config['label'], 0, 30));
                    $sheet->setName(trim($sheetName ?: $table));

                    $writer->addRow(Row::fromValues($config['labels']));

                    $modelClass = $config['model'];
                    $records = $modelClass::all();

                    foreach ($records as $record) {
                        $rowValues = [];
                        foreach ($config['headers'] as $header) {
                            $rowValues[] = $this->getRecordExportValue($table, $header, $record);
                        }
                        $writer->addRow(Row::fromValues($rowValues));
                    }
                }

                $writer->close();
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Downloads a multi-sheet master template Excel file (headers only).
     */
    public function templateMaster(Request $request)
    {
        abort_if(Gate::denies('setting_view'), 403, 'Unauthorized access.');

        try {
            $configs = $this->getTableConfigs();
            $selectedTables = $request->input('tables');
            if (is_array($selectedTables) && !empty($selectedTables)) {
                $configs = array_intersect_key($configs, array_flip($selectedTables));
            }

            $ext = 'xlsx';
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

            $headers = [
                'Content-type' => $contentType,
                'Content-Disposition' => 'attachment; filename=society_master_template_' . date('Ymd_His') . '.' . $ext,
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($configs) {
                $writer = new XLSXWriter();
                $writer->openToFile('php://output');
                $isFirst = true;

                foreach ($configs as $table => $config) {
                    if ($isFirst) {
                        $sheet = $writer->getCurrentSheet();
                        $isFirst = false;
                    } else {
                        $sheet = $writer->addNewSheetAndMakeItCurrent();
                    }
                    $sheetName = preg_replace('/[^\w\s-]/', '', substr($config['label'], 0, 30));
                    $sheet->setName(trim($sheetName ?: $table));

                    $writer->addRow(Row::fromValues($config['labels']));
                }

                $writer->close();
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Previews sheets and sample data from an uploaded master Excel file.
     */
    public function previewMaster(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403, 'Unauthorized access.');

        try {
            $maxSizeKb = (float) \App\Models\Setting::get('max_document_size', 2) * 1024;
            $request->validate([
                'import_file' => 'required|file|max:' . $maxSizeKb,
            ]);

            $file = $request->file('import_file');
            $filePath = $this->storeUploadedFile($file, 'master');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'csv') {
                return response()->json(['success' => false, 'message' => 'Please upload an Excel (.xlsx) file for master import.'], 400);
            }

            $reader = new XLSXReader();
            $reader->open(Storage::path($filePath));

            $sheetsSummary = [];
            $configs = $this->getTableConfigs();

            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = trim($sheet->getName());
                $matchedTable = null;
                $matchedConfig = null;
                foreach ($configs as $tableKey => $cfg) {
                    $cleanLabel = preg_replace('/[^\w\s-]/', '', substr($cfg['label'], 0, 30));
                    if (strcasecmp($sheetName, trim($cleanLabel)) === 0 || strcasecmp($sheetName, $tableKey) === 0 || strcasecmp($sheetName, $cfg['label']) === 0) {
                        $matchedTable = $tableKey;
                        $matchedConfig = $cfg;
                        break;
                    }
                }

                if (!$matchedConfig) {
                    continue;
                }

                $headers = [];
                $previewRows = [];
                $rowCount = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->toArray();
                    if ($rowCount === 0) {
                        $headers = $cells;
                    } else {
                        $isEmptyRow = true;
                        foreach ($cells as &$cell) {
                            if ($cell instanceof \DateTime) {
                                $cell = $cell->format('Y-m-d');
                            }
                            if (trim((string)$cell) !== '') {
                                $isEmptyRow = false;
                            }
                        }
                        if (!$isEmptyRow) {
                            $previewRows[] = $cells;
                        }
                    }
                    $rowCount++;
                }

                $sheetsSummary[] = [
                    'table' => $matchedTable,
                    'label' => $matchedConfig['label'],
                    'headers' => $headers,
                    'preview_rows' => array_slice($previewRows, 0, 10),
                    'total_rows' => count($previewRows),
                ];
            }
            $reader->close();

            if (empty($sheetsSummary)) {
                return response()->json(['success' => false, 'message' => 'No recognized sheets found in the master Excel file.'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Master Excel file analyzed successfully.',
                'file_path' => $filePath,
                'sheets_summary' => $sheetsSummary,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return $this->handleException($e, __FUNCTION__);
        }
    }

    /**
     * Processes master import across multiple tables in dependency order.
     */
    public function processMaster(Request $request)
    {
        abort_if(Gate::denies('setting_edit'), 403, 'Unauthorized access.');

        try {
            $request->validate([
                'file_path' => 'required|string',
            ]);

            $filePath = $request->file_path;
            if (!Storage::exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'Master import file not found or expired.'], 400);
            }

            $reader = new XLSXReader();
            $reader->open(Storage::path($filePath));
            $configs = $this->getTableConfigs();

            $importOrder = [
                'blocks', 'flat_types', 'expense_categories', 'flats', 'users', 'residents',
                'maintenances', 'maintenance_bills', 'name_transfer_bills', 'complaints', 'expenses'
            ];

            $sheetDataByTable = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = trim($sheet->getName());
                $matchedTable = null;
                $matchedConfig = null;
                foreach ($configs as $tableKey => $cfg) {
                    $cleanLabel = preg_replace('/[^\w\s-]/', '', substr($cfg['label'], 0, 30));
                    if (strcasecmp($sheetName, trim($cleanLabel)) === 0 || strcasecmp($sheetName, $tableKey) === 0 || strcasecmp($sheetName, $cfg['label']) === 0) {
                        $matchedTable = $tableKey;
                        $matchedConfig = $cfg;
                        break;
                    }
                }
                if ($matchedTable) {
                    $rows = [];
                    $rowCount = 0;
                    foreach ($sheet->getRowIterator() as $row) {
                        $cells = $row->toArray();
                        if ($rowCount > 0) {
                            $isEmptyRow = true;
                            foreach ($cells as &$cell) {
                                if ($cell instanceof \DateTime) {
                                    $cell = $cell->format('Y-m-d');
                                }
                                if (trim((string)$cell) !== '') {
                                    $isEmptyRow = false;
                                }
                            }
                            if (!$isEmptyRow) {
                                $rows[] = $cells;
                            }
                        }
                        $rowCount++;
                    }
                    if (!empty($rows)) {
                        $sheetDataByTable[$matchedTable] = [
                            'config' => $matchedConfig,
                            'rows' => $rows,
                            'sheet_name' => $sheetName,
                        ];
                    }
                }
            }
            $reader->close();

            $totalSuccess = 0;
            $totalFailed = 0;
            $failedRecords = [];

            DB::beginTransaction();
            try {
                foreach ($importOrder as $tableKey) {
                    if (!isset($sheetDataByTable[$tableKey])) {
                        continue;
                    }
                    $tableInfo = $sheetDataByTable[$tableKey];
                    $res = $this->importDataRows($tableKey, $tableInfo['config'], $tableInfo['rows']);
                    $totalSuccess += $res['imported_count'];
                    $totalFailed += $res['failed_count'];
                    foreach ($res['errors'] as $idx => $errStr) {
                        $failedRecords[] = [
                            'record' => "Row " . ($idx + 2),
                            'sheet' => $tableInfo['sheet_name'],
                            'row' => $idx + 2,
                            'reason' => $errStr,
                        ];
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Master import completed.',
                'success_count' => $totalSuccess,
                'failed_count' => $totalFailed,
                'failed_records' => $failedRecords,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            if (isset($filePath) && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            return $this->handleException($e, __FUNCTION__);
        }
    }
}
