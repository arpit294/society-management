<?php

namespace Modules\Finance\Models;

use App\Models\Flat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'finance_payments';

    protected $fillable = [
        'receipt_number',
        'invoice_id',
        'user_id',
        'flat_id',
        'bank_account_id',
        'payment_date',
        'amount',
        'payment_mode', // cash, cheque, bank_transfer, upi, advance_adjustment
        'transaction_reference',
        'status',
        'remarks',
        'received_by',
        'journal_entry_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class, 'flat_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
