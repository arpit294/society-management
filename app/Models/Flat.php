<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flat extends Model
{
    protected $fillable = [
        'block_id',
        'unit_type',
        'flat_no',
        'floor_no',
        'flat_type_id',
        'area_sqft',
        'plot_area_sqyards',
        'electricity_meter_no',
        'water_meter_no',
        'has_commercial_license',
        'status',
    ];

    public function calculateMaintenanceFee($residentType = 'owner')
    {
        $type = $this->flatType;
        if (!$type) {
            return 0;
        }

        $baseRate = ($residentType === 'rental') ? $type->rental_maintenance_fee : $type->owner_maintenance_fee;

        if ($type->calculation_method === 'per_sqft' && $this->area_sqft > 0) {
            $amount = $this->area_sqft * $type->rate_per_sqft;
        } elseif ($type->calculation_method === 'hybrid' && $this->area_sqft > 0) {
            $amount = $baseRate + ($this->area_sqft * $type->rate_per_sqft);
        } else {
            $amount = $baseRate;
        }

        if ($this->unit_type === 'shop' || $this->unit_type === 'office' || $type->category_type === 'commercial') {
            $surcharge = ($amount * $type->commercial_surcharge_percentage) / 100;
            $amount += $surcharge;
        }

        return round($amount, 2);
    }

    public function block()
    {
        return $this->belongsTo(Block::class, 'block_id');
    }

    public function flatType()
    {
        return $this->belongsTo(FlatType::class, 'flat_type_id');
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function owner()
    {
        return $this->hasOne(Resident::class)
            ->where('type', 'owner')
            ->where(function ($query) {
                $query->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
            ->latest();
    }

    public function tenant()
    {
        return $this->hasOne(Resident::class)
            ->where('type', 'rental')
            ->where(function ($query) {
                $query->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>=', now()->startOfDay());
            })
            ->latest();
    }

    public function documents()
    {
        return $this->hasMany(FlatDocument::class);
    }
}
