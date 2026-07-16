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
        $isCommercial = in_array(strtolower($this->unit_type ?? ''), ['shop', 'office', 'showroom', 'warehouse']);
        $type = $this->flatType;

        // Commercial properties: always calculate based on Sq. Feet (Carpet Area) x Global Commercial Rate from Settings
        if ($isCommercial) {
            $sqftRate = (float) \App\Models\Setting::get('commercial_rate_per_sqft', 0);
            if ($sqftRate <= 0) {
                $sqftRate = (float) \App\Models\Setting::get('maintenance_rate_per_sqft', 10);
            }
            if ($sqftRate <= 0) {
                $sqftRate = 10.00;
            }
            $amount = ((float) $this->area_sqft) * $sqftRate;
            return round($amount, 2);
        }

        // Residential properties: always use Fixed amount from selected Property Unit Category (FlatType)
        $fallbackFixed = ($residentType === 'rental')
            ? (float) \App\Models\Setting::get('default_tenant_fixed_maintenance', 0)
            : (float) \App\Models\Setting::get('default_owner_fixed_maintenance', 0);

        if (!$type) {
            return round($fallbackFixed, 2);
        }

        $baseRate = ($residentType === 'rental') ? $type->rental_maintenance_fee : $type->owner_maintenance_fee;
        if ($baseRate <= 0 && $fallbackFixed > 0) {
            $baseRate = $fallbackFixed;
        }

        return round($baseRate, 2);
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

    public function syncOccupancyStatus()
    {
        $this->load(['owner.user', 'tenant.user']);
        $hasResident = ($this->owner && $this->owner->user) || ($this->tenant && $this->tenant->user);
        $desiredStatus = $hasResident ? config('status.flats.occupied', 'occupied') : config('status.flats.empty', 'empty');
        if ($this->status !== $desiredStatus) {
            $this->update(['status' => $desiredStatus]);
        }
        return $desiredStatus;
    }

    public static function syncAllOccupancyStatus()
    {
        $flats = self::with(['owner.user', 'tenant.user'])->get();
        $occupiedCount = 0;
        $emptyCount = 0;
        foreach ($flats as $flat) {
            $hasResident = ($flat->owner && $flat->owner->user) || ($flat->tenant && $flat->tenant->user);
            $desiredStatus = $hasResident ? config('status.flats.occupied', 'occupied') : config('status.flats.empty', 'empty');
            if ($flat->status !== $desiredStatus) {
                $flat->update(['status' => $desiredStatus]);
            }
            if ($hasResident || $flat->status === config('status.flats.occupied', 'occupied')) {
                $occupiedCount++;
            } else {
                $emptyCount++;
            }
        }
        return ['occupied' => $occupiedCount, 'empty' => $emptyCount];
    }
}
