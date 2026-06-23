<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlatType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'maintenance_fee',
        'penalty_per_day',
        'description',
        'status',
    ];
}
