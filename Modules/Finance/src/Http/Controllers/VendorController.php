<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\Models\Vendor;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::withCount(['bills', 'vouchers'])->latest('id')->get();
        return view('finance::payables.vendors', compact('vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'service_type' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'gstin' => 'nullable|string|max:30',
            'pan_number' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        try {
            $vendor = Vendor::create($request->all());

            return response()->json([
                'success' => true,
                'message' => "Vendor {$vendor->name} registered successfully.",
                'vendor' => $vendor,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'service_type' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        $vendor->update($request->all());

        return response()->json([
            'success' => true,
            'message' => "Vendor details updated successfully.",
        ]);
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->bills()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete vendor with existing bills or payment history.',
            ], 422);
        }

        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted successfully.',
        ]);
    }
}
