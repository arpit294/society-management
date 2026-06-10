<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceBill;
use App\Models\Maintenance;
use App\Models\Resident;
use App\DataTables\MaintenanceBillsDataTable;
use App\Models\Block;
use App\Models\Flat;
use App\Models\PrepaidMaintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MaintenanceBillController extends Controller
{
    public function index(MaintenanceBillsDataTable $dataTable)
    {
        $prepayments = PrepaidMaintenance::with(['user', 'flat.block'])
            ->where('status', 'unused')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return $dataTable->render('maintenance_bills.index', compact('prepayments'));
    }

    public function create()
    {
        return view('maintenance_bills.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'month' => 'required|string|max:50',
            'year' => 'required|integer|min:2000',
            'status' => 'required|in:draft,published',
        ]);
        // Calculate due date based on configurable penalty due days (default to 15 days if not set)
        $dueDays = (int)setting('penalty_due_days', 15);
        // Store the due date in the maintenance table, but it can also be calculated dynamically in the accessor if you prefer
        $validatedData['due_date'] = now()->addDays($dueDays)->format('Y-m-d');

        DB::beginTransaction();

        try {
            // Create Master Maintenance
            $maintenance = Maintenance::create($validatedData);

            // Fetch all active residents
            $activeResidents = Resident::with('flat.flatType')
                ->where(function($query) {
                    $query->whereNull('move_out_date')
                          ->orWhere('move_out_date', '>=', now()->startOfDay());
                })->get();
            // Loop through each resident and create individual maintenance bills
            foreach ($activeResidents as $resident) {
                if (!$resident->flat || !$resident->flat->flatType) continue;

                // Determine the maintenance amount based on resident type and flat type
                $amount = $resident->type === 'owner'
                    ? $resident->flat->flatType->owner_maintenance_fee
                    : $resident->flat->flatType->rental_maintenance_fee;

                // Check for unused prepayment
                $prepayment = PrepaidMaintenance::where('flat_id', $resident->flat_id)
                    ->where('status', 'unused')
                    ->whereRaw('months_used < months')
                    ->first();

                $status = 'due';
                $paidAt = null;

                if ($prepayment) {
                    $status = 'paid';
                    $paidAt = now();
                }

                // Create the maintenance bill for the resident
                $bill = MaintenanceBill::create([
                    'maintenance_id' => $maintenance->id,
                    'user_id' => $resident->user_id,
                    'flat_id' => $resident->flat_id,
                    'block_id' => $resident->block_id,
                    'amount' => $amount,
                    'penalty_amount' => 0,
                    'total_amount' => $amount,
                    'generated_date' => now(),
                    'status' => $status,
                    'paid_at' => $paidAt,
                ]);

                // If a prepayment exists, update it to reflect the usage of one month of prepayment
                if ($prepayment) {
                    $monthsUsed = $prepayment->months_used + 1;
                    $prepayment->update([
                        'months_used' => $monthsUsed,
                        'status' => ($monthsUsed >= $prepayment->months) ? 'used' : 'unused',
                        'maintenance_bill_id' => $bill->id // Tracks the latest bill id for reference
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance records generated successfully.',
            ]);
        // Catch any exceptions that occur during the process and roll back the transaction
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating maintenance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(\App\DataTables\MaintenanceDetailsDataTable $dataTable, $id)
    {
        // Show maintenance details along with all associated bills
        $maintenance = Maintenance::with(['maintenanceBills.user', 'maintenanceBills.flat'])->findOrFail($id);
        return $dataTable->with('id', $id)->render('maintenance_bills.show', compact('maintenance'));
    }

    public function destroy($id)
    {
        // Delete the entire maintenance record along with all associated bills
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance record deleted successfully.',
        ]);
    }

    public function edit($id)
    {
        // Show form to edit individual maintenance bill
        $maintenanceBill = MaintenanceBill::findOrFail($id);
        $blocks = Block::all();
        $flats = Flat::with('flatType')->where('block_id', $maintenanceBill->block_id)->get();
        $users = User::all();
        return view('maintenance_bills.edit', compact('maintenanceBill', 'blocks', 'flats', 'users'));
    }

    public function update(Request $request, $id)
    {
        $maintenanceBill = MaintenanceBill::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'block_id' => 'required|exists:blocks,id',
            'flat_id' => 'required|exists:flats,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:paid,due,pending',
        ]);

        $data = $request->only([
            'user_id',
            'block_id',
            'flat_id',
            'amount',
            'status',
        ]);
        // If status is being updated to paid, set the paid_at timestamp and lock in penalty and total amounts
        if ($request->status === 'paid' && $maintenanceBill->getRawOriginal('status') !== 'paid') {
            $data['paid_at'] = now();

            // ---------------------------------------------------------
            // COMPLEX LOGIC: Locking in Dynamic Amounts
            // Because penalties are calculated dynamically based on today's date,
            // we must explicitly freeze the penalty and total amount in the DB
            // the moment it is paid. Otherwise, if someone views the paid bill
            // 6 months later, it would calculate new penalties on the paid bill.
            // ---------------------------------------------------------
            $data['penalty_amount'] = $maintenanceBill->penalty_amount;
            $data['total_amount'] = $request->amount + $maintenanceBill->penalty_amount;
        } elseif ($request->status !== 'paid') {
            $data['paid_at'] = null;
            $data['penalty_amount'] = 0;
            $data['total_amount'] = $request->amount;
        }

        $maintenanceBill->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance bill updated successfully.',
        ]);
    }

    public function destroyIndividual($id)
    {
        $bill = MaintenanceBill::findOrFail($id);
        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance bill deleted successfully.',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:paid,due,pending',
            'payment_method' => 'required_if:status,paid|in:cash,upi',
            'transaction_id' => 'nullable|string',
            'payment_slip' => 'required_if:payment_method,upi|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $maintenanceBill = MaintenanceBill::findOrFail($id);
         // If status is being updated to paid, set the paid_at timestamp and lock in penalty and total amounts
        if ($request->status === 'paid' && $maintenanceBill->status !== 'paid') {
            // Read dynamic amounts BEFORE changing status to paid, so accessors calculate correctly
            $lockedPenalty = $maintenanceBill->penalty_amount;
            $lockedTotal = $maintenanceBill->total_amount;

            $maintenanceBill->status = 'paid';
            $maintenanceBill->paid_at = now();
            $maintenanceBill->payment_method = $request->payment_method;
            $maintenanceBill->transaction_id = $request->transaction_id;

            if ($request->hasFile('payment_slip')) {
                $maintenanceBill->payment_slip = $request->file('payment_slip')->store('payment_slips', 'public');
            }

            // Lock in the dynamically calculated amounts
            $maintenanceBill->penalty_amount = $lockedPenalty;
            $maintenanceBill->total_amount = $lockedTotal;
        } elseif ($request->status !== 'paid') {
            $maintenanceBill->status = $request->status;
            $maintenanceBill->paid_at = null;
            $maintenanceBill->payment_method = null;
            $maintenanceBill->transaction_id = null;
            $maintenanceBill->payment_slip = null;
        }

        $maintenanceBill->save();

        if ($request->ajax() || $request->expectsJson()) {
            $maintenance = Maintenance::with('maintenanceBills')->findOrFail($maintenanceBill->maintenance_id);
            $paidCount = $maintenance->maintenanceBills->where('status', 'paid')->count();
            $totalCount = $maintenance->maintenanceBills->count();
            $totalAmountExpected = $maintenance->maintenanceBills->sum('total_amount');

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'paidCount' => $paidCount,
                'totalCount' => $totalCount,
                'totalAmountExpected' => number_format($totalAmountExpected, 2)
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    // Additional method to fetch resident info based on user ID, useful for dynamic form updates when creating/editing bills
    public function details($id)
    {
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);

        return view('maintenance_bills.details', compact('bill'));
    }

    // Method to download invoice as PDF
    public function downloadInvoice($id)
    {
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);
        $pdf = Pdf::loadView('maintenance_bills.invoice_pdf', compact('bill'));

        $fileName = 'invoice_'.($bill->flat->block->block_name ?? '').'-'.($bill->flat->flat_no ?? '').'_'.$bill->maintenance->month.'_'.$bill->maintenance->year.'.pdf';

        return $pdf->download($fileName);
    }


    // API endpoint to fetch resident info based on user ID, used for dynamic form updates when creating/editing bills
    public function getResidentInfo($userId)
    {
        $resident = Resident::with('flat.flatType')
            ->where('user_id', $userId)
            ->where(function($query) {
                $query->whereNull('move_out_date')
                      ->orWhere('move_out_date', '>=', now()->startOfDay());
            })->first();
    // This method is used to dynamically fetch the maintenance amount and related info when a user is selected in the bill creation/edit form
        if ($resident && $resident->flat && $resident->flat->flatType) {
            $amount = $resident->type === 'owner'
                ? $resident->flat->flatType->owner_maintenance_fee
                : $resident->flat->flatType->rental_maintenance_fee;

            return response()->json([
                'success' => true,
                'block_id' => $resident->block_id,
                'flat_id' => $resident->flat_id,
                'amount' => $amount
            ]);
        }
        return response()->json(['success' => false]);
    }
}
