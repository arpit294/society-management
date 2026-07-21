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
        'status',
    ];

    protected $appends = ['is_commercial'];

    private const COMMERCIAL_KEYWORDS = ['commercial', 'shop', 'office', 'showroom', 'warehouse'];

    public function getIsCommercialAttribute()
    {
        if ($this->containsCommercialKeyword($this->unit_type ?? '')) {
            return true;
        }
        if ($this->relationLoaded('flatType') && $this->flatType) {
            if ($this->containsCommercialKeyword($this->flatType->category_type ?? '')) {
                return true;
            }
            if ($this->containsCommercialKeyword($this->flatType->name ?? '')) {
                return true;
            }
        } elseif ($this->flat_type_id) {
            $ft = \App\Models\FlatType::find($this->flat_type_id);
            if ($ft && ($this->containsCommercialKeyword($ft->category_type ?? '') || $this->containsCommercialKeyword($ft->name ?? ''))) {
                return true;
            }
        }
        return false;
    }

    public function maintenanceSqftRate(): float
    {

        $sqftRate = $this->is_commercial
            ? (float) \App\Models\Setting::get('commercial_rate_per_sqft', 0)
            : (float) \App\Models\Setting::get('maintenance_rate_per_sqft', 0);

        if ($sqftRate <= 0 && $this->is_commercial) {
            $sqftRate = (float) \App\Models\Setting::get('maintenance_rate_per_sqft', 10);
        }

        return $sqftRate > 0 ? $sqftRate : 0;
    }

    public function calculateMaintenanceFee($residentType = 'owner')
    {
        $type = $this->flatType;
        $fallbackFixed = ($residentType === 'rental')
            ? (float) \App\Models\Setting::get('default_tenant_fixed_maintenance', 0)
            : (float) \App\Models\Setting::get('default_owner_fixed_maintenance', 0);

        $baseAmount = 0;
        $isCommercial = $this->is_commercial;

        if ($type) {
            $method = $type->calculation_method ?? 'fixed';
            $fixedFee = ($residentType === 'rental') ? (float) $type->rental_maintenance_fee : (float) $type->owner_maintenance_fee;
            if ($fixedFee <= 0 && $fallbackFixed > 0) {
                $fixedFee = $fallbackFixed;
            }
            $sqftRate = $this->maintenanceSqftRate();
            $area = (float) $this->area_sqft;

            if ($isCommercial || $method === 'per_sqft') {
                $baseAmount = $area * $sqftRate;
            } elseif ($method === 'hybrid') {
                $baseAmount = $fixedFee + ($area * $sqftRate);
            } else {
                // 'fixed' or others
                if ($fixedFee <= 0 && $sqftRate > 0 && $area > 0) {
                    $baseAmount = $area * $sqftRate;
                } else {
                    $baseAmount = $fixedFee;
                }
            }
        } else {
            // No FlatType assigned
            if ($isCommercial) {
                $baseAmount = ((float) $this->area_sqft) * $this->maintenanceSqftRate();
            } else {
                $baseAmount = $fallbackFixed;
            }
        }

        return round($baseAmount, 2);
    }

    private function containsCommercialKeyword(string $value): bool
    {
        $value = strtolower($value);

        foreach (self::COMMERCIAL_KEYWORDS as $keyword) {
            if (str_contains($value, $keyword)) {
                return true;
            }
        }

        return false;
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
