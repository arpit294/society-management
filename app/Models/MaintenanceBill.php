<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'user_id',
        'flat_id',
        'amount',
        'penalty_amount',
        'total_amount',
        'month',
        'year',
        'due_date',
        'generated_date',
        'paid_at',
        'payment_method',
        'transaction_id',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'generated_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function getStatusAttribute($value)
    {
        if ($value === 'paid') {
            return 'paid';
        }

        if ($this->due_date && \Carbon\Carbon::parse($this->due_date)->endOfDay()->isPast()) {
            return 'due';
        }

        return 'pending';
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
