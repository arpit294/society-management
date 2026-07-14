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
        'status',
    ];

    protected $appends = ['is_commercial'];

    public function getIsCommercialAttribute()
    {
        if (in_array(strtolower($this->unit_type ?? ''), ['shop', 'office', 'commercial', 'showroom'])) {
            return true;
        }
        if ($this->relationLoaded('flatType') && $this->flatType) {
            if (in_array(strtolower($this->flatType->category_type ?? ''), ['commercial', 'shop', 'office'])) {
                return true;
            }
            if (in_array(strtolower($this->flatType->name ?? ''), ['shop', 'office', 'commercial', 'showroom'])) {
                return true;
            }
        } elseif ($this->flat_type_id) {
            $ft = \App\Models\FlatType::find($this->flat_type_id);
            if ($ft && (in_array(strtolower($ft->category_type ?? ''), ['commercial', 'shop', 'office']) || in_array(strtolower($ft->name ?? ''), ['shop', 'office', 'commercial', 'showroom']))) {
                return true;
            }
        }
        return false;
    }

    public function calculateMaintenanceFee($residentType = 'owner')
    {
        $globalMethod = \App\Models\Setting::get('maintenance_billing_method', 'fixed');
        $globalSqftRate = (float) \App\Models\Setting::get('maintenance_rate_per_sqft', 0);

        $fallbackFixed = ($residentType === 'rental')
            ? (float) \App\Models\Setting::get('default_tenant_fixed_maintenance', 0)
            : (float) \App\Models\Setting::get('default_owner_fixed_maintenance', 0);

        $type = $this->flatType;
        if (!$type) {
            if ($globalMethod === 'per_sqft' && $this->area_sqft > 0 && $globalSqftRate > 0) {
                return round($this->area_sqft * $globalSqftRate, 2);
            }
            return round($fallbackFixed, 2);
        }

        $baseRate = ($residentType === 'rental') ? $type->rental_maintenance_fee : $type->owner_maintenance_fee;
        if ($baseRate <= 0 && $fallbackFixed > 0) {
            $baseRate = $fallbackFixed;
        }

        if ($globalMethod === 'per_sqft' || $type->calculation_method === 'per_sqft') {
            $sqftRate = ($type->rate_per_sqft > 0) ? $type->rate_per_sqft : $globalSqftRate;
            if ($this->area_sqft > 0 && $sqftRate > 0) {
                $amount = $this->area_sqft * $sqftRate;
            } else {
                $amount = $baseRate;
            }
        } elseif ($type->calculation_method === 'hybrid') {
            $sqftRate = ($type->rate_per_sqft > 0) ? $type->rate_per_sqft : $globalSqftRate;
            if ($this->area_sqft > 0 && $sqftRate > 0) {
                $amount = $baseRate + ($this->area_sqft * $sqftRate);
            } else {
                $amount = $baseRate;
            }
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
