<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'block_name',
        'total_floor',
        'total_flats',
    ];
}
