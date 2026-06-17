<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlatDocument extends Model
{
    protected $fillable = [
        'flat_id',
        'user_id',
        'resident_type',
        'uploaded_by',
        'documents',
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
