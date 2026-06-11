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
        $totalCollected = MaintenanceBill::where('status', 'paid')->sum('total_amount');
        $cashCollected = MaintenanceBill::where('status', 'paid')->where('payment_method', 'CASH')->sum('total_amount');
        $upiCollected = MaintenanceBill::where('status', 'paid')->where('payment_method', 'UPI')->sum('total_amount');
        
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $monthlyRevenueDB = MaintenanceBill::where('maintenance_bills.status', 'paid')
            ->join('maintenances', 'maintenance_bills.maintenance_id', '=', 'maintenances.id')
            ->where('maintenances.year', date('Y'))
            ->selectRaw('maintenances.month, sum(maintenance_bills.total_amount) as total')
            ->groupBy('maintenances.month')
            ->pluck('total', 'month')
            ->toArray();
            
        $chartDataRevenue = [];
        foreach ($months as $m) {
            $chartDataRevenue[] = $monthlyRevenueDB[$m] ?? 0;
        }

        return $dataTable->render('maintenance_bills.index', compact(
            'totalCollected',
            'cashCollected',
            'upiCollected',
            'months',
            'chartDataRevenue'
        ));
    }



    public function show(\App\DataTables\MaintenanceDetailsDataTable $dataTable, $id)
    {
        // Show maintenance details along with all associated bills
        $maintenance = Maintenance::with(['maintenanceBills.user', 'maintenanceBills.flat'])->findOrFail($id);
        return $dataTable->with('id', $id)->render('maintenance_bills.show', compact('maintenance'));
    }

    public function destroy($id)
    {
        $bills = MaintenanceBill::where('batch_id', $id)->get();
        if ($bills->isEmpty()) {
            $bills = MaintenanceBill::where('id', $id)->get();
        }

        foreach ($bills as $bill) {
            $bill->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.',
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
