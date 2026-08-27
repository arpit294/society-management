<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use HasFactory;

    protected $table = 'finance_fiscal_years';

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
        'closed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'fiscal_year_id');
    }

    public static function current(): ?self
    {
        return self::where('is_active', true)->first()
            ?? self::whereDate('start_date', '<=', now())
                   ->whereDate('end_date', '>=', now())
                   ->first();
    }
}
