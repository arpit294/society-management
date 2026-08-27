<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashEntry extends Model
{
    use HasFactory;

    protected $table = 'finance_petty_cash_entries';

    protected $fillable = [
        'entry_date',
        'voucher_no',
        'type', // expense, replenishment
        'amount',
        'account_id',
        'paid_to',
        'purpose',
        'receipt_attachment',
        'handled_by',
        'journal_entry_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
