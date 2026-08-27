<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use HasFactory;

    protected $table = 'finance_bank_transactions';

    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'type', // deposit, withdrawal
        'amount',
        'reference_number',
        'description',
        'is_reconciled',
        'reconciled_at',
        'journal_entry_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
