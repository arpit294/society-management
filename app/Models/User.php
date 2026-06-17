<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        return $this->name.' ('.($this->phone ?? 'No Phone').')';
    }
}
