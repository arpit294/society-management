<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'user_id',
        'flat_id',
        'amount',
        'penalty_amount',
        'total_amount',
        'generated_date',
        'paid_at',
        'payment_method',
        'transaction_id',
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

    public function getStatusAttribute($value)
    {
        if ($value === 'paid') {
            return 'paid';
        }

        if ($this->maintenance && $this->maintenance->due_date && Carbon::parse($this->maintenance->due_date)->endOfDay()->isPast()) {
            return 'due';
        }

        return 'pending';
    }

    public function getPenaltyAmountAttribute($value)
    {
        if ($this->attributes['status'] === 'paid' || $value > 0) {
            return (float) $value;
        }

        if ($this->maintenance && $this->maintenance->due_date && Carbon::parse($this->maintenance->due_date)->endOfDay()->isPast()) {
            if ($this->flat && $this->flat->flatType) {
                return (float) $this->flat->flatType->penalty_per_day;
            }
        }

        return 0.00;
    }

    public function getTotalAmountAttribute($value)
    {
        if ($this->attributes['status'] === 'paid') {
            return (float) $value;
        }

        $baseAmount = (float) $this->amount;
        $penalty = $this->getPenaltyAmountAttribute($this->attributes['penalty_amount']);

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
