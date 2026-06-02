<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    protected $fillable = [
        'subject',
        'description',
        'user_id',
        'category',
        'status',
        'resolution_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
