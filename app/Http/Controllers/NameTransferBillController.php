<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NameTransferBill;
use App\DataTables\NameTransferBillsDataTable;
use App\Models\Resident;
use Illuminate\Support\Facades\DB;

class NameTransferBillController extends Controller
{
    public function index(NameTransferBillsDataTable $dataTable)
    {
        return $dataTable->render('name_transfer_bills.index');
    }

    public function updateStatus(Request $request, NameTransferBill $bill)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,cancelled',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'status' => $request->status,
        ];

        if ($request->status === 'paid') {
            $updateData['paid_at'] = now();
            if ($request->payment_method) {
                $updateData['payment_method'] = $request->payment_method;
            }
        } elseif ($request->status === 'pending') {
            $updateData['paid_at'] = null;
        }

        $bill->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }

    public function destroy(NameTransferBill $bill)
    {
        $bill->delete();
        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully.',
        ]);
    }


    public function approve(NameTransferBill $bill)
    {
        if ($bill->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer is already approved.',
            ], 400);
        }

        $transferDate = $bill->transfer_date ?? $bill->created_at->format('Y-m-d');

        DB::beginTransaction();
        try {
            // 1. End current owner's residency
            $oldResident = Resident::where('flat_id', $bill->flat_id)
                ->where('user_id', $bill->old_owner_id)
                ->where('type', 'owner')
                ->orderBy('move_in_date', 'desc')
                ->first();

            if ($oldResident) {
                $oldResident->update(['move_out_date' => $transferDate]);
            }

            // 2. Create new resident
            Resident::create([
                'block_id' => $bill->flat->block_id,
                'flat_id' => $bill->flat_id,
                'user_id' => $bill->new_owner_id,
                'type' => 'owner',
                'move_in_date' => $transferDate,
            ]);

            // 3. Mark as approved
            $bill->update(['is_approved' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer approved successfully. Residents updated.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error approving transfer: ' . $e->getMessage(),
            ], 500);
        }
    }
}
