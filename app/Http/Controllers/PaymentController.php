<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Resident;
use App\Models\PrepaidMaintenance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class PaymentController extends Controller
{
    public function index()
    {
        // Get residents who have unused prepaid maintenance
        $prepayments = PrepaidMaintenance::with(['user', 'flat'])
            ->where('status', 'unused')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        return view('payments.index', compact('prepayments'));
    }

    public function create()
    {
        // Get active residents with flat info
        $residents = Resident::with(['user', 'flat.flatType', 'block'])
            ->whereNull('move_out_date')
            ->orWhere('move_out_date', '>=', now()->startOfDay())
            ->get();

        $residentFees = [];
        foreach ($residents as $resident) {
            if ($resident->flat && $resident->flat->flatType) {
                $residentFees[$resident->id] = $resident->type === 'owner'
                    ? $resident->flat->flatType->owner_maintenance_fee
                    : $resident->flat->flatType->rental_maintenance_fee;
            } else {
                $residentFees[$resident->id] = 0;
            }
        }

        $discountSettings = [
            'apply_discount' => setting('apply_discount', '1'),
            'type' => setting('discount_type', 'percentage'),
            'yearly_value' => (float)setting('discount_yearly_value', setting('discount_yearly_percent', 10)),
            'half_yearly_value' => (float)setting('discount_half_yearly_value', setting('discount_half_yearly_percent', 0)),
            'quarterly_value' => (float)setting('discount_quarterly_value', setting('discount_quarterly_percent', 5)),
        ];

        $penaltySettings = [
            'apply_penalty' => setting('apply_penalty', '1'),
            'type' => setting('penalty_type', 'percentage'),
            'yearly_value' => (float)setting('penalty_yearly_value', setting('penalty_yearly_percent', 15)),
            'half_yearly_value' => (float)setting('penalty_half_yearly_value', setting('penalty_half_yearly_percent', 10)),
            'quarterly_value' => (float)setting('penalty_quarterly_value', setting('penalty_quarterly_percent', 5)),
        ];
        return view('payments.create', compact('residents', 'residentFees', 'discountSettings', 'penaltySettings'));
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
            'payment_slip' => 'required_if:payment_method,upi|image|mimes:jpeg,png,jpg|max:2048',
            'discount_amount' => 'nullable|numeric|min:0',
            'penalty_amount' => 'nullable|numeric|min:0'
        ]);

        $resident = Resident::with(['user', 'flat.flatType'])->findOrFail($request->resident_id);
        // Ensure resident has a flat with a valid flat type to determine maintenance fee
        if (!$resident->flat || !$resident->flat->flatType) {
            return redirect()->back()->with('error', 'Resident does not have a flat assigned with a valid flat type.');
        }
        // Determine monthly fee based on resident type and flat type
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

            $baseAmount = $monthlyFee * $numberOfMonths;
            $discountValue = 0;
            $applyDiscount = setting('apply_discount', '1');

            // ---------------------------------------------------------
            // COMPLEX LOGIC: Split Months (Past vs Future)
            // ---------------------------------------------------------
            $now = Carbon::now()->startOfMonth();
            $pastMonthsCount = 0;
            
            // Calculate how many months are before the current month
            $startAbs = $currentDate->year * 12 + $currentDate->month;
            $nowAbs = $now->year * 12 + $now->month;
            
            $pastMonthsCount = $nowAbs - $startAbs;
            if ($pastMonthsCount < 0) $pastMonthsCount = 0;
            if ($pastMonthsCount > $numberOfMonths) $pastMonthsCount = $numberOfMonths;
            
            $futureMonthsCount = $numberOfMonths - $pastMonthsCount;
            
            $arrearsAmount = $pastMonthsCount * $monthlyFee;
            $advanceAmount = $futureMonthsCount * $monthlyFee;

            // Penalty Calculation
            $totalPenaltyAmount = 0;
            if ($request->has('penalty_amount') && $request->filled('penalty_amount')) {
                $totalPenaltyAmount = (float)$request->penalty_amount;
            } else {
                if (setting('apply_penalty', '1') === '1' && $pastMonthsCount > 0) {
                    $penaltyValue = 0;
                    if ($pastMonthsCount >= 12) {
                        $penaltyValue = (float)setting('penalty_yearly_value', setting('penalty_yearly_percent', 15));
                    } elseif ($pastMonthsCount >= 6) {
                        $penaltyValue = (float)setting('penalty_half_yearly_value', setting('penalty_half_yearly_percent', 10));
                    } elseif ($pastMonthsCount >= 3) {
                        $penaltyValue = (float)setting('penalty_quarterly_value', setting('penalty_quarterly_percent', 5));
                    }
                    
                    if ($penaltyValue > 0) {
                        if (setting('penalty_type', 'percentage') === 'fixed') {
                            $totalPenaltyAmount = $penaltyValue;
                        } else {
                            $totalPenaltyAmount = $arrearsAmount * ($penaltyValue / 100);
                        }
                    }
                }
            }

            // Discount Calculation
            $totalDiscountAmount = 0;
            if ($request->has('discount_amount') && $request->filled('discount_amount')) {
                $totalDiscountAmount = (float)$request->discount_amount;
            } else {
                if (setting('apply_discount', '1') === '1' && $futureMonthsCount > 0) {
                    $discountValue = 0;
                    if ($futureMonthsCount >= 12) {
                        $discountValue = (float)setting('discount_yearly_value', setting('discount_yearly_percent', 10));
                    } elseif ($futureMonthsCount >= 6) {
                        $discountValue = (float)setting('discount_half_yearly_value', setting('discount_half_yearly_percent', 0));
                    } elseif ($futureMonthsCount >= 3) {
                        $discountValue = (float)setting('discount_quarterly_value', setting('discount_quarterly_percent', 5));
                    }
                    
                    if ($discountValue > 0) {
                        if (setting('discount_type', 'percentage') === 'fixed') {
                            $totalDiscountAmount = $discountValue;
                        } else {
                            $totalDiscountAmount = $advanceAmount * ($discountValue / 100);
                        }
                    }
                }
            }

            // Distribute amounts per month
            $discountPerMonth = $numberOfMonths > 0 ? ($totalDiscountAmount / $numberOfMonths) : 0;
            $penaltyPerMonth = $numberOfMonths > 0 ? ($totalPenaltyAmount / $numberOfMonths) : 0;
            
            $amountPerMonth = $monthlyFee + $penaltyPerMonth - $discountPerMonth;
            if ($amountPerMonth < 0) {
                $amountPerMonth = 0;
                // Optionally cap discount so it mathematically makes sense
                $discountPerMonth = $monthlyFee + $penaltyPerMonth;
            }

            // Generate a unique batch ID for this payment transaction
            $batchId = uniqid('pay_');

            // Generate a MaintenanceBill for each month
            for ($i = 0; $i < $numberOfMonths; $i++) {
                $loopDate = $currentDate->copy()->addMonths($i);
                $monthStr = $loopDate->format('F');
                $yearInt = $loopDate->year;

                // Find or create the Maintenance batch for this month
                $maintenance = \App\Models\Maintenance::firstOrCreate(
                    ['month' => $monthStr, 'year' => $yearInt],
                    [
                        'billing_cycle' => 'monthly',
                        'due_date' => $loopDate->copy()->endOfMonth()->format('Y-m-d'),
                        'total_additional_cost' => 0,
                        'status' => 'published'
                    ]
                );

                // Create or update the bill for this resident
                \App\Models\MaintenanceBill::updateOrCreate(
                    [
                        'maintenance_id' => $maintenance->id,
                        'flat_id' => $resident->flat_id,
                    ],
                    [
                        'batch_id' => $batchId,
                        'user_id' => $resident->user_id,
                        'block_id' => $resident->block_id,
                        'amount' => $monthlyFee,
                        'discount_amount' => $discountPerMonth,
                        'penalty_amount' => $penaltyPerMonth,
                        'total_amount' => $amountPerMonth,
                        'generated_date' => now(),
                        'paid_at' => now(),
                        'payment_method' => $request->payment_method,
                        'transaction_id' => $request->transaction_id,
                        'payment_slip' => $paymentSlipPath,
                        'status' => 'paid',
                    ]
                );
            }

            DB::commit();

            return redirect()->route('maintenance-bills.index')->with('success', 'Payment recorded successfully for ' . $numberOfMonths . ' months.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }
}
