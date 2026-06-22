<?php

namespace App\Console\Commands;

use App\Models\MaintenanceBill;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateBillAmounts extends Command
{
    protected $signature = 'app:update-bill-amounts';

    protected $description = 'Daily update of penalty and discount amounts for unpaid bills based on settings';

    public function handle()
    {
        $bills = MaintenanceBill::with(['maintenance'])->where('status', '!=', 'paid')->get();
        $count = 0;

        $penaltySettings = $this->getSettingValues('penalty');
        $discountSettings = $this->getSettingValues('discount');

        $dueDays = (int) ($penaltySettings['due_days'] ?? 15);

        foreach ($bills as $bill) {
            $updated = false;

            // Calculate past/future months based on maintenance month/year vs now
            $now = Carbon::now()->startOfMonth();
            $billDate = null;
            if ($bill->maintenance && $bill->maintenance->month && $bill->maintenance->year) {
                try {
                    $billDate = Carbon::parse('1 '.$bill->maintenance->month.' '.$bill->maintenance->year)->startOfMonth();
                } catch (\Exception $e) {
                    $billDate = Carbon::parse($bill->generated_date)->startOfMonth();
                }
            } else {
                $billDate = Carbon::parse($bill->generated_date)->startOfMonth();
            }

            $pastMonthsCount = 0;
            $futureMonthsCount = 1; // Base assumes a 1-month bill

            if ($billDate->lt($now)) {
                $pastMonthsCount = $now->diffInMonths($billDate);
                // Max is 1 for a single monthly bill, but let's say it's 1
                if ($pastMonthsCount > 1) {
                    $pastMonthsCount = 1;
                }
                $futureMonthsCount = 0;
            } elseif ($billDate->gt($now)) {
                $futureMonthsCount = 1;
            } else {
                // Current month
                $pastMonthsCount = 0;
                $futureMonthsCount = 0;
            }

            $baseAmount = (float) $bill->amount;

            // Penalty Logic
            $newPenalty = 0;
            if ($penaltySettings['apply_penalty'] === '1' || $penaltySettings['apply_penalty'] === 'true') {
                $dueDate = Carbon::parse($bill->generated_date)->addDays($dueDays);

                if ($dueDate->endOfDay()->isPast()) {
                    $billingCycle = $bill->maintenance->billing_cycle ?? 'monthly';
                    $penaltyValue = 0;

                    if ($billingCycle === 'monthly' && ($penaltySettings['monthly_enabled'] ?? false)) {
                        $penaltyValue = $penaltySettings['monthly_value'] ?? 0;
                    } elseif ($billingCycle === 'quarterly' && ($penaltySettings['quarterly_enabled'] ?? false)) {
                        $penaltyValue = $penaltySettings['quarterly_value'] ?? 0;
                    } elseif ($billingCycle === 'yearly' && ($penaltySettings['yearly_enabled'] ?? false)) {
                        $penaltyValue = $penaltySettings['yearly_value'] ?? 0;
                    }

                    if ($penaltyValue > 0) {
                        if (($penaltySettings['type'] ?? 'percentage') === 'fixed') {
                            $newPenalty = (float) $penaltyValue;
                        } else {
                            $newPenalty = $baseAmount * ((float) $penaltyValue / 100);
                        }
                    }
                }
            }

            if (abs($newPenalty - $bill->penalty_amount) > 0.01) {
                $bill->penalty_amount = $newPenalty;
                $updated = true;
            }

            // Discount Logic
            $newDiscount = 0;
            $applyDiscount = $discountSettings['apply_discount'] ?? '0';
            if (($applyDiscount === '1' || $applyDiscount === 'true' || $applyDiscount === 'on') && $futureMonthsCount > 0) {
                $billingCycle = $bill->maintenance->billing_cycle ?? 'monthly';
                $discountValue = 0;

                if ($billingCycle === 'monthly' && ($discountSettings['monthly_enabled'] ?? false)) {
                    $discountValue = $discountSettings['monthly_value'] ?? 0;
                } elseif ($billingCycle === 'quarterly' && ($discountSettings['quarterly_enabled'] ?? false)) {
                    $discountValue = $discountSettings['quarterly_value'] ?? 0;
                } elseif ($billingCycle === 'yearly' && ($discountSettings['yearly_enabled'] ?? false)) {
                    $discountValue = $discountSettings['yearly_value'] ?? 0;
                }

                if ($discountValue > 0) {
                    if (($discountSettings['type'] ?? 'percentage') === 'fixed') {
                        $newDiscount = (float) $discountValue;
                    } else {
                        $newDiscount = $baseAmount * ((float) $discountValue / 100);
                    }
                }
            }

            if (abs($newDiscount - $bill->discount_amount) > 0.01) {
                $bill->discount_amount = $newDiscount;
                $updated = true;
            }

            if ($updated) {
                $bill->total_amount = $baseAmount + $bill->penalty_amount - $bill->discount_amount;
                $bill->save();
                $count++;
            }
        }

        $this->info("Successfully updated amounts for {$count} unpaid bills based on settings.");
        Log::info("UpdateBillAmounts command ran. Updated {$count} bills.");
    }

    private function getSettingValues(string $type): array
    {
        return [
            "apply_{$type}" => setting("apply_{$type}", '0'),
            'type' => setting("{$type}_type", 'percentage'),
            'due_days' => setting("{$type}_due_days", '15'),
            'yearly_enabled' => setting("{$type}_yearly_enabled", '0') === '1',
            'half_yearly_enabled' => setting("{$type}_half_yearly_enabled", '0') === '1',
            'quarterly_enabled' => setting("{$type}_quarterly_enabled", '0') === '1',
            'monthly_enabled' => setting("{$type}_monthly_enabled", '0') === '1',
            'yearly_value' => (float) setting("{$type}_yearly_value", setting("{$type}_yearly_percent", ($type === 'penalty' ? 15 : 10))),
            'half_yearly_value' => (float) setting("{$type}_half_yearly_value", setting("{$type}_half_yearly_percent", ($type === 'penalty' ? 10 : 0))),
            'quarterly_value' => (float) setting("{$type}_quarterly_value", setting("{$type}_quarterly_percent", ($type === 'penalty' ? 5 : 0))),
            'monthly_value' => (float) setting("{$type}_monthly_value", setting("{$type}_monthly_percent", ($type === 'penalty' ? 2 : 0))),
        ];
    }
}
