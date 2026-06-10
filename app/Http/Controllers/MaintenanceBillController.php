<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceBill;
use App\Models\Maintenance;
use App\Models\Resident;
use App\DataTables\MaintenanceBillsDataTable;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MaintenanceBillController extends Controller
{
    public function index(MaintenanceBillsDataTable $dataTable)
    {
        $prepayments = \App\Models\PrepaidMaintenance::with(['user', 'flat.block'])
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
            'due_date' => 'required|date',
            'status' => 'required|in:draft,published',
        ]);

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

            foreach ($activeResidents as $resident) {
                if (!$resident->flat || !$resident->flat->flatType) continue;

                $amount = $resident->flat->flatType->maintenance_fee;

                // Check for unused prepayment
                $prepayment = \App\Models\PrepaidMaintenance::where('flat_id', $resident->flat_id)
                    ->where('status', 'unused')
                    ->whereRaw('months_used < months')
                    ->first();

                $status = 'due';
                $paidAt = null;

                if ($prepayment) {
                    $status = 'paid';
                    $paidAt = now();
                }

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
        $maintenance = Maintenance::with(['maintenanceBills.user', 'maintenanceBills.flat'])->findOrFail($id);
        return $dataTable->with('id', $id)->render('maintenance_bills.show', compact('maintenance'));
    }

    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance record deleted successfully.',
        ]);
    }

    public function edit($id)
    {
        $maintenanceBill = MaintenanceBill::findOrFail($id);
        $blocks = \App\Models\Block::all();
        $flats = \App\Models\Flat::with('flatType')->where('block_id', $maintenanceBill->block_id)->get();
        $users = \App\Models\User::all();
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

        if ($request->status === 'paid' && $maintenanceBill->getRawOriginal('status') !== 'paid') {
            $data['paid_at'] = now();
            // Lock in the penalty and total amount
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
            'status' => 'required|in:paid,due,pending'
        ]);

        $maintenanceBill = MaintenanceBill::findOrFail($id);

        if ($request->status === 'paid' && $maintenanceBill->status !== 'paid') {
            // Read dynamic amounts BEFORE changing status to paid, so accessors calculate correctly
            $lockedPenalty = $maintenanceBill->penalty_amount;
            $lockedTotal = $maintenanceBill->total_amount;

            $maintenanceBill->status = 'paid';
            $maintenanceBill->paid_at = now();

            // Lock in the dynamically calculated amounts
            $maintenanceBill->penalty_amount = $lockedPenalty;
            $maintenanceBill->total_amount = $lockedTotal;
        } elseif ($request->status !== 'paid') {
            $maintenanceBill->status = $request->status;
            $maintenanceBill->paid_at = null;
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

    public function details($id)
    {
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);

        return view('maintenance_bills.details', compact('bill'));
    }

    public function downloadInvoice($id)
    {
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);
        $pdf = Pdf::loadView('maintenance_bills.invoice_pdf', compact('bill'));

        $fileName = 'invoice_'.($bill->flat->block->block_name ?? '').'-'.($bill->flat->flat_no ?? '').'_'.$bill->maintenance->month.'_'.$bill->maintenance->year.'.pdf';

        return $pdf->download($fileName);
    }
}
