<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'flat_id',
        'user_id',
        'type',
        'occupant_category',
        'business_name',
        'contact_person',
        'move_in_date',
        'move_out_date',
    ];

    protected function casts(): array
    {
        return [
            'move_in_date' => 'date',
            'move_out_date' => 'date',
        ];
    }



    public function getContactPersonAttribute($value)
    {
        return !empty($value) ? $value : ($this->user ? $this->user->name : null);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsOwnerAttribute()
    {
        return $this->type === 'owner';
    }

    public function getIsTenantAttribute()
    {
        return $this->type === 'rental';
    }

    protected static function booted(): void
    {
        static::saved(function ($resident) {
            if ($resident->isDirty('user_id') && $resident->getOriginal('user_id')) {
                self::syncUserStatus($resident->getOriginal('user_id'));
            }
            self::syncUserStatus($resident->user_id);
        });

        static::deleted(function ($resident) {
            self::syncUserStatus($resident->user_id);
        });
    }

    public static function syncUserStatus($userId): void
    {
        if (! $userId) {
            return;
        }

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return;
        }

        // Do not deactivate admin or non-resident staff accounts by residency rule
        if (in_array(strtolower((string) $user->role), ['admin'])) {
            return;
        }

        // Check if user currently has at least one active residency
        $hasActiveResidency = self::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>', now()->startOfDay());
            })
            ->exists();

        // Check if they STILL own any flat (they are the latest owner of it)
        $stillOwnsAFlat = self::where('user_id', $userId)
            ->where('type', 'owner')
            ->whereNotExists(function ($query) {
                $query->select('id')
                      ->from('residents as r2')
                      ->whereColumn('r2.flat_id', 'residents.flat_id')
                      ->where('r2.type', 'owner')
                      ->whereColumn('r2.id', '>', 'residents.id');
            })
            ->exists();

        if ($hasActiveResidency || $stillOwnsAFlat) {
            // If they are active resident or still own a flat, ensure status is active
            if ($user->status !== 'active') {
                $user->update(['status' => 1]);
            }
        } else {
            // If they have past residency history and no active residency/ownership, mark them inactive
            $hasPastResidency = self::where('user_id', $userId)->exists();
            if ($hasPastResidency && $user->status !== 'inactive') {
                $user->update(['status' => 0]);
            }
        }
    }
}
