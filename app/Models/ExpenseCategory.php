<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'status',
    ];



    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
