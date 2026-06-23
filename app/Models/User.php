<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles {
        hasPermissionTo as protected spatieHasPermissionTo;
    }

    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'aadhar_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    public function complains()
    {
        return $this->hasMany(Complain::class);
    }

    public function resident()
    {
        return $this->hasOne(Resident::class)
            ->orderByRaw('move_out_date IS NOT NULL') // Nulls first (active)
            ->latest('move_in_date');
    }

    public function getResidentDetailsAttribute()
    {
        return $this->name . ' (' . ($this->phone ?? 'No Phone') . ')';
    }

    /**
     * Helper Methods to Check Roles in an Easy and Readable Way.
     * Use these instead of raw string comparisons throughout the codebase.
     */

    public function isSecretary(): bool
    {
        return $this->role === UserRole::SECRETARY->value;
    }

    public function isCommitteeMember(): bool
    {
        return $this->role === UserRole::COMMITTEE_MEMBER->value;
    }

    public function isSecurity(): bool
    {
        return $this->role === UserRole::SECURITY->value;
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::OWNER->value;
    }

    public function isRental(): bool
    {
        return $this->role === UserRole::RENTAL->value;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            UserRole::SECRETARY->value,
            UserRole::COMMITTEE_MEMBER->value,
            UserRole::SECURITY->value,
        ]);
    }

    /**
     * The "booted" method of the model.
     * Hook into Eloquent model events.
     */
    protected static function booted(): void
    {
        // Automatically sync the string 'role' column with Spatie's permission tables.
        // This ensures the local column and permission system are always in lockstep.
        static::saved(function (User $user): void {
            if ($user->role && Role::where('name', $user->role)->where('guard_name', 'web')->exists()) {
                $user->syncRoles([$user->role]);
            } else {
                $user->syncRoles([]);
            }
        });
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        if ($this->role === 'Admin') {
            return true;
        }

        return $this->spatieHasPermissionTo($permission, $guardName);
    }
}
