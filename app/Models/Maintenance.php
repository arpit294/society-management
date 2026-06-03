<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'due_date',
        'total_additional_cost',
        'status',
    ];

    public function maintenanceBills()
    {
        return $this->hasMany(MaintenanceBill::class);
    }
}
