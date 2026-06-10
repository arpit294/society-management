<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Resident;
use App\Models\PrepaidMaintenance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrepaymentController extends Controller
{
    public function index()
    {
        // Get residents who have unused prepaid maintenance
        $prepayments = PrepaidMaintenance::with(['user', 'flat'])
            ->where('status', 'unused')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('prepayments.index', compact('prepayments'));
    }

    public function create()
    {
        // Get active residents with flat info
        $residents = Resident::with(['user', 'flat.flatType', 'block'])
            ->whereNull('move_out_date')
            ->orWhere('move_out_date', '>=', now()->startOfDay())
            ->get();

        return view('prepayments.create', compact('residents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'months' => 'required|integer|min:1|max:12',
            'start_month' => 'required|string',
            'start_year' => 'required|integer',
            'payment_method' => 'required|in:cash,upi',
            'transaction_id' => 'nullable|string',
            'payment_slip' => 'required_if:payment_method,upi|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $resident = Resident::with(['user', 'flat.flatType'])->findOrFail($request->resident_id);

        if (!$resident->flat || !$resident->flat->flatType) {
            return redirect()->back()->with('error', 'Resident does not have a flat assigned with a valid flat type.');
        }

        $monthlyFee = $resident->type === 'owner' 
            ? $resident->flat->flatType->owner_maintenance_fee 
            : $resident->flat->flatType->rental_maintenance_fee;
        $numberOfMonths = $request->months;

        DB::beginTransaction();

        try {
            $paymentSlipPath = null;
            if ($request->hasFile('payment_slip')) {
                $paymentSlipPath = $request->file('payment_slip')->store('payment_slips', 'public');
            }

            $currentDate = Carbon::createFromDate($request->start_year, Carbon::parse($request->start_month)->month, 1);
            $endDate = $currentDate->copy()->addMonths($numberOfMonths - 1);

            PrepaidMaintenance::create([
                'user_id' => $resident->user_id,
                'flat_id' => $resident->flat_id,
                'month' => $currentDate->format('F'),
                'year' => $currentDate->year,
                'end_month' => $endDate->format('F'),
                'end_year' => $endDate->year,
                'months' => $numberOfMonths,
                'months_used' => 0,
                'amount_paid' => $monthlyFee * $numberOfMonths,
                'status' => 'unused',
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'payment_slip' => $paymentSlipPath,
            ]);

            DB::commit();

            return redirect()->route('maintenance-bills.index')->with('success', 'Prepayment recorded successfully for ' . $numberOfMonths . ' months.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error recording prepayment: ' . $e->getMessage());
        }
    }
}
