<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    use HasFactory;

    protected $table = 'finance_payment_vouchers';

    protected $fillable = [
        'voucher_number',
        'vendor_bill_id',
        'vendor_id',
        'bank_account_id',
        'voucher_date',
        'amount',
        'payment_mode',
        'reference_no',
        'description',
        'approval_status', // draft, submitted, approved, rejected, paid
        'created_by',
        'approved_by',
        'approved_at',
        'paid_at',
        'journal_entry_id',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
