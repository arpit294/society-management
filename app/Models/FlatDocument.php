<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlatDocument extends Model
{
    protected $fillable = [
        'flat_id',
        'resident_type',
        'uploaded_by',
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
