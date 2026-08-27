<?php

namespace Modules\Finance\Models;

use App\Models\Flat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'finance_invoices';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'flat_id',
        'invoice_date',
        'due_date',
        'bill_month',
        'bill_year',
        'invoice_type', // maintenance, name_transfer, noc, amenity_booking, penalty, custom
        'subtotal',
        'late_fee',
        'discount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'status', // unpaid, partially_paid, paid, overdue, cancelled
        'notes',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'bill_year' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class, 'flat_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
