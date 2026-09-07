<?php

namespace Modules\Finance\Models;

use App\Models\Block;
use App\Models\Flat;
use App\Models\Setting;
use App\Models\User;
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
        'received_by',
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
        if (($this->attributes['status'] ?? null) === 'paid' || $value > 0) {
            return (float)$value;
        }

        // 1. Check if penalties are globally enabled in settings
        $applyPenalty = Setting::get('apply_penalty', '1');
        if ($applyPenalty !== '1') {
            return 0.00;
        }

        // 2. Check if the bill has crossed the allowed due days
        $dueDays = (int)Setting::get('penalty_due_days', 15);
        $dueDate = Carbon::parse($this->generated_date)->addDays($dueDays);

        // 3. If past due date, calculate the penalty based on billing cycle
        if ($dueDate->endOfDay()->isPast()) {
            $baseAmount = (float)$this->amount;
            $billingCycle = $this->maintenance->billing_cycle ?? 'monthly';
            $defaults = Setting::defaults();
            $penaltyValue = 0;

            if ($billingCycle === 'monthly' && Setting::get('penalty_monthly_enabled', '1') == '1') {
                $penaltyValue = (float)Setting::get('penalty_monthly_value', $defaults['penalty_monthly_value'] ?? 0);
            } elseif ($billingCycle === 'quarterly' && Setting::get('penalty_quarterly_enabled', '1') == '1') {
                $penaltyValue = (float)Setting::get('penalty_quarterly_value', $defaults['penalty_quarterly_value'] ?? 0);
            } elseif ($billingCycle === 'half_yearly' && Setting::get('penalty_half_yearly_enabled', '1') == '1') {
                $penaltyValue = (float)Setting::get('penalty_half_yearly_value', $defaults['penalty_half_yearly_value'] ?? 0);
            } elseif ($billingCycle === 'yearly' && Setting::get('penalty_yearly_enabled', '1') == '1') {
                $penaltyValue = (float)Setting::get('penalty_yearly_value', $defaults['penalty_yearly_value'] ?? 0);
            }

            $penaltyType = Setting::get('penalty_type', 'percentage');
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

        if ($status === 'paid' || $status === null) {
            return (float)$value;
        }

        $baseAmount = (float)($this->attributes['amount'] ?? 0);
        $penalty = $this->getPenaltyAmountAttribute($this->attributes['penalty_amount'] ?? 0);
        $discount = (float)($this->attributes['discount_amount'] ?? 0);

        return max(0, $baseAmount + $penalty - $discount);
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

    public function resident()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
