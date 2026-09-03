<?php

namespace Modules\Finance\Models;

use App\Models\Flat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NameTransferBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_id',
        'old_owner_id',
        'new_owner_id',
        'amount',
        'transfer_date',
        'status',
        'paid_at',
        'payment_method',
        'transaction_id',
        'payment_slip',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function oldOwner()
    {
        return $this->belongsTo(User::class, 'old_owner_id');
    }

    public function newOwner()
    {
        return $this->belongsTo(User::class, 'new_owner_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
