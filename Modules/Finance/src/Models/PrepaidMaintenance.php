<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepaidMaintenance extends Model
{
    protected $fillable = [
        'user_id',
        'flat_id',
        'month',
        'year',
        'end_month',
        'end_year',
        'months',
        'months_used',
        'amount_paid',
        'status',
        'maintenance_bill_id',
        'payment_method',
        'transaction_id',
        'payment_slip',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function maintenanceBill()
    {
        return $this->belongsTo(MaintenanceBill::class);
    }
}
