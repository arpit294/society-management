<?php

namespace App\Http\Controllers;

use App\DataTables\MaintenanceBillsDataTable;
use App\DataTables\MaintenanceDetailsDataTable;
use App\Models\FlatType;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;
use App\Models\Resident;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaintenanceBillController extends Controller
{
    /**
     * Display a listing of maintenance bills.
     *
     * @return mixed
     */
    public function index(MaintenanceBillsDataTable $dataTable)
    {
        return $dataTable->render('maintenance_bills.index');
    }

    /**
     * Show the form for creating new maintenance bills.
     *
     * @return View
     */
    public function create()
    {
        return view('maintenance_bills.create');
    }

    /**
     * Store new maintenance bills in storage.
     *
     * @return JsonResponse
     */
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
                ->where(function ($query) {
                    $query->whereNull('move_out_date')
                        ->orWhere('move_out_date', '>=', now()->startOfDay());
                })->get();

            foreach ($activeResidents as $resident) {
                if (! $resident->flat || ! $resident->flat->flatType) {
                    continue;
                }

                $amount = $resident->flat->flatType->maintenance_fee;

                MaintenanceBill::create([
                    'maintenance_id' => $maintenance->id,
                    'user_id' => $resident->user_id,
                    'flat_id' => $resident->flat_id,
                    'block_id' => $resident->block_id,
                    'amount' => $amount,
                    'penalty_amount' => 0,
                    'total_amount' => $amount,
                    'generated_date' => now(),
                    'status' => 'due',
                ]);
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
                'message' => 'Error generating maintenance: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the specified maintenance record and its bills.
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(MaintenanceDetailsDataTable $dataTable, $id)
    {
        $maintenance = Maintenance::with(['maintenanceBills.user', 'maintenanceBills.flat'])->findOrFail($id);
        $flatTypes = FlatType::all();

        return $dataTable->with('id', $id)->render('maintenance_bills.show', compact('maintenance', 'flatTypes'));
    }

    /**
     * Remove the specified maintenance record from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance record deleted successfully.',
        ]);
    }

    /**
     * Update the status of a specific maintenance bill.
     *
     * @param  int  $id
     * @return JsonResponse|RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:paid,due,pending',
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
                'totalAmountExpected' => number_format($totalAmountExpected, 2),
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    /**
     * Show the details of a specific maintenance bill.
     *
     * @param  int  $id
     * @return View
     */
    public function details($id)
    {
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);

        return view('maintenance_bills.details', compact('bill'));
    }

    /**
     * Download the invoice PDF for a specific maintenance bill.
     *
     * @param  int  $id
     * @return Response
     */
    public function downloadInvoice($id)
    {
        $bill = MaintenanceBill::with(['user', 'flat.block', 'flat.flatType', 'maintenance'])->findOrFail($id);
        $pdf = Pdf::loadView('maintenance_bills.invoice_pdf', compact('bill'));

        $fileName = 'invoice_'.($bill->flat->block->block_name ?? '').'-'.($bill->flat->flat_no ?? '').'_'.$bill->maintenance->month.'_'.$bill->maintenance->year.'.pdf';

        return $pdf->download($fileName);
    }
}
