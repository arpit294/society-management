<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fund extends Model
{
    use HasFactory;

    protected $table = 'finance_funds';

    protected $fillable = [
        'name',
        'type', // sinking_fund, reserve_fund, fixed_deposit, corpus_fund
        'account_id',
        'bank_account_id',
        'principal_amount',
        'current_balance',
        'interest_rate',
        'start_date',
        'maturity_date',
        'certificate_no',
        'status',
        'notes',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'start_date' => 'date',
        'maturity_date' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
