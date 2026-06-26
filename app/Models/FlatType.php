<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlatType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_maintenance_fee',
        'rental_maintenance_fee',
        'description',
        'status',
    ];
}
