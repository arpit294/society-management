<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $table = 'finance_chart_of_accounts';

    protected $fillable = [
        'code',
        'name',
        'type', // asset, liability, equity, income, expense
        'parent_id',
        'is_system',
        'opening_balance',
        'current_balance',
        'description',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_system' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalItems(): HasMany
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }

    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class, 'account_id');
    }

    /**
     * Determine normal balance direction: Assets & Expenses are Debit (true); Liabilities, Equity, Incomes are Credit (false).
     */
    public function isNormalDebit(): bool
    {
        return in_array($this->type, ['asset', 'expense']);
    }
}
