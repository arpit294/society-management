<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $status
 * @property float $penalty_amount
 * @property float $total_amount
 * @property float $discount_amount
 * @property Carbon|null $paid_at
 * @property string|null $payment_method
 * @property string|null $transaction_id
 * @property string|null $payment_slip
 */
class MaintenanceBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'batch_id',
        'user_id',
        'flat_id',
        'amount',
        'penalty_amount',
        'total_amount',
        'generated_date',
        'paid_at',
        'payment_method',
        'transaction_id',
        'payment_slip',
        'status',
        'block_id',
        'dynamic_penalty_amount',
        'manual_penalty_amount',
        'discount_amount',
    ];

    protected $casts = [
        'generated_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // Accessor to determine the current status of the bill based on payment and due date
    public function getStatusAttribute($value)
    {
        // If the bill is marked as paid, return 'paid'
        if ($value === 'paid') {
            return 'paid';
        }
        // If the bill is not paid and the due date has passed, mark it as 'due'
        if ($this->maintenance && $this->maintenance->due_date && \Carbon\Carbon::parse($this->maintenance->due_date)->endOfDay()->isPast()) {
            return 'due';
        }

        return 'pending';
    }

    public function getPenaltyAmountAttribute($value)
    {
        if ($this->attributes['status'] === 'paid' || $value > 0) {
            return (float)$value;
        }

        // ---------------------------------------------------------
        // COMPLEX LOGIC: Dynamic Penalty Calculation
        // This attribute calculates the penalty amount dynamically
        // on-the-fly rather than storing it statically in the DB
        // until the bill is actually paid.
        // ---------------------------------------------------------

        // 1. Check if penalties are globally enabled in settings
        $applyPenalty = setting('apply_penalty', '1');
        if ($applyPenalty !== '1') {
            return 0.00;
        }

        // 2. Check if the bill has crossed the allowed due days
        $dueDays = (int)setting('penalty_due_days', 15);
        $dueDate = Carbon::parse($this->generated_date)->addDays($dueDays);

        // 3. If past due date, calculate the penalty based on billing cycle
        if ($dueDate->endOfDay()->isPast()) {
            $baseAmount = (float)$this->amount;
            $billingCycle = $this->maintenance->billing_cycle ?? 'monthly';
            $penaltyValue = 0;

            // Fetch the appropriate penalty value based on billing cycle and global settings
            if ($billingCycle === 'monthly' && setting('penalty_monthly_enabled', '1') == '1') {
                $penaltyValue = (float)setting('penalty_monthly_value', setting('penalty_monthly_percent', 5));
            } elseif ($billingCycle === 'quarterly' && setting('penalty_quarterly_enabled', '1') == '1') {
                $penaltyValue = (float)setting('penalty_quarterly_value', setting('penalty_quarterly_percent', 10));
            } elseif ($billingCycle === 'yearly' && setting('penalty_yearly_enabled', '1') == '1') {
                $penaltyValue = (float)setting('penalty_yearly_value', setting('penalty_yearly_percent', 15));
            }

            // 4. Apply either as a fixed dollar amount or as a percentage multiplier
            $penaltyType = setting('penalty_type', 'percentage');
            if ($penaltyType === 'fixed') {
                return $penaltyValue;
            }

            return $baseAmount * ($penaltyValue / 100);
        }

        return 0.00;
    }

    public function getTotalAmountAttribute($value)
    {
        $status = $this->attributes['status'] ?? null;

        // If status is paid OR if status is not loaded (e.g. grouped queries),
        // trust the raw value provided by the database.
        if ($status === 'paid' || $status === null) {
            return (float)$value;
        }

        $baseAmount = (float)($this->attributes['amount'] ?? 0);
        $penalty = $this->getPenaltyAmountAttribute($this->attributes['penalty_amount'] ?? 0);
        return $baseAmount + $penalty;
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }
}
