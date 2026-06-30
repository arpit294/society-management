<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles {
        hasPermissionTo as protected spatieHasPermissionTo;
    }

    public const UPDATED_AT = null;

    public const ROLE_OWNER = 'owner';
    public const ROLE_RENTAL = 'rental';
    public const ROLE_SECURITY = 'security';
    public const ROLE_COMMITTEE_MEMBER = 'committee_member';
    public const ROLE_SECRETARY = 'secretary';

    public static function roleValues(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_RENTAL,
            self::ROLE_SECURITY,
            self::ROLE_COMMITTEE_MEMBER,
            self::ROLE_SECRETARY,
        ];
    }

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

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ($value === 1 || $value === true || $value === '1' || $value === 'active') ? 'active' : 'inactive',
            set: fn($value) => ($value === 1 || $value === true || $value === '1' || $value === 'active' || strtolower((string)$value) === 'active') ? 1 : 0,
        );
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
        return $this->role === self::ROLE_SECRETARY;
    }

    public function isCommitteeMember(): bool
    {
        return $this->role === self::ROLE_COMMITTEE_MEMBER;
    }

    public function isSecurity(): bool
    {
        return $this->role === self::ROLE_SECURITY;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isRental(): bool
    {
        return $this->role === self::ROLE_RENTAL;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_SECRETARY,
            self::ROLE_COMMITTEE_MEMBER,
            self::ROLE_SECURITY,
        ]);
    }

    /**
     * The "booted" method of the model.
     * Hook into Eloquent model events.
     */
    protected static function booted(): void
    {
        // Prevent automatic role syncing.
        // Your UI/seeders should assign roles explicitly through Spatie.
        // Automatic syncing from the `role` column causes unwanted pivot rows
        // (and makes it look like roles are being assigned even when they shouldn't).
        //
        // Keep this method intentionally empty.
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
