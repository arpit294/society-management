<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flat extends Model
{
    protected $fillable = [
        'block_id',
        'flat_no',
        'floor_no',
        'flat_type',
        'maintenance_amount',
        'status',
    ];

    public function block()
    {
        return $this->belongsTo(Block::class, 'block_id');
    }
}
