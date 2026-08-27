<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'finance_bank_accounts';

    protected $fillable = [
        'account_id',
        'bank_name',
        'account_name',
        'account_number',
        'ifsc_code',
        'branch',
        'account_type',
        'opening_balance',
        'current_balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bank_account_id');
    }
}
