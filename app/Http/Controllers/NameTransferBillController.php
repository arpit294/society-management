<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NameTransferBill;
use App\DataTables\NameTransferBillsDataTable;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class NameTransferBillController extends Controller
{
    public function index(NameTransferBillsDataTable $dataTable)
    {
        abort_if(! \Auth::user()->can('name_transfer_bill_view'), 403);
        try {
            return $dataTable->render('name_transfer_bills.index');
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in NameTransferBillController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, NameTransferBill $bill)
    {
        abort_if(! \Auth::user()->can('name_transfer_bill_view'), 403);
        try {
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
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in NameTransferBillController@updateStatus: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(NameTransferBill $bill)
    {
        abort_if(! \Auth::user()->can('name_transfer_bill_delete'), 403);
        try {
            $bill->delete();
            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully.',
            ]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in NameTransferBillController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function approve(NameTransferBill $bill)
    {
        abort_if(! \Auth::user()->can('name_transfer_bill_view'), 403);
        if (! \Auth::user()->canApproveNameTransfer()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only Admin, Secretary, and Committee Members can approve name transfers.',
            ], 403);
        }
        try {
            if ($bill->is_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer is already approved.',
                ], 400);
            }

            if ($bill->status !== config('status.name_transfer_bills.paid', 'paid')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve transfer: Payment has not been received yet.',
                ], 400);
            }

            $transferDate = $bill->transfer_date ?? $bill->created_at->format('Y-m-d');

            DB::beginTransaction();
            try {
                // 1. End current owner's residency by completely removing all existing residents for this flat
                Resident::where('flat_id', $bill->flat_id)->delete();

                // Sync flat occupancy status
                if ($bill->flat) {
                    $bill->flat->syncOccupancyStatus();
                }

                // Sync user statuses for both old and new owners
                Resident::syncUserStatus($bill->old_owner_id);
                if ($bill->new_owner_id) {
                    Resident::syncUserStatus($bill->new_owner_id);
                }

                // 3. Mark as approved
                $bill->update([
                    'is_approved' => true,
                    'approved_by' => \Auth::id(),
                ]);

                if ($bill->new_owner_id) {
                    $user = User::find($bill->new_owner_id);
                    if ($user) {
                        $user->touch();
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Transfer approved successfully. Residents updated.',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in NameTransferBillController@approve: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error approving transfer: ' . $e->getMessage(),
            ], 500);
        }
    }
}
