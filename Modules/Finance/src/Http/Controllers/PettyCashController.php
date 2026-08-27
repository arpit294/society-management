<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\PettyCashEntry;
use Modules\Finance\Services\AccountingEngineService;

class PettyCashController extends Controller
{
    public function __construct(
        protected AccountingEngineService $accountingEngine
    ) {}

    public function index()
    {
        $pettyCashAccount = BankAccount::where('account_type', 'cash')->first();
        $entries = PettyCashEntry::with(['account', 'handler'])->latest('id')->paginate(25);
        $expenseAccounts = Account::where('type', 'expense')->where('status', 'active')->get();
        $bankAccounts = BankAccount::where('account_type', '!=', 'cash')->where('status', 'active')->get();

        return view('finance::petty-cash.index', compact('pettyCashAccount', 'entries', 'expenseAccounts', 'bankAccounts'));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:finance_chart_of_accounts,id',
            'amount' => 'required|numeric|min:1|max:' . config('finance.petty_cash.single_expense_limit', 2000),
            'paid_to' => 'required|string|max:100',
            'purpose' => 'required|string|max:255',
            'entry_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
        ]);

        try {
            $pettyBank = BankAccount::where('account_type', 'cash')->firstOrFail();
            $amount = (float) $request->amount;

            if ($pettyBank->current_balance < $amount) {
                throw new Exception("Insufficient Petty Cash balance. Available: ₹{$pettyBank->current_balance}, Required: ₹{$amount}");
            }

            $attachment = null;
            if ($request->hasFile('receipt')) {
                $attachment = $request->file('receipt')->store('finance/petty_cash', 'public');
            }

            $expenseAccount = Account::findOrFail($request->account_id);

            DB::transaction(function () use ($pettyBank, $expenseAccount, $amount, $request, $attachment) {
                // Deduct cash balance
                $pettyBank->current_balance -= $amount;
                $pettyBank->save();

                $entry = PettyCashEntry::create([
                    'entry_date' => $request->entry_date,
                    'voucher_no' => 'PC-' . date('Ymd') . '-' . rand(100, 999),
                    'type' => 'expense',
                    'amount' => $amount,
                    'account_id' => $expenseAccount->id,
                    'paid_to' => $request->paid_to,
                    'purpose' => $request->purpose,
                    'receipt_attachment' => $attachment,
                    'handled_by' => auth()->id() ?? 1,
                ]);

                // Post Double Entry: Debit Expense Account, Credit Petty Cash (1050)
                $journalEntry = $this->accountingEngine->postTransaction(
                    "Petty Cash: {$request->purpose} (Paid to {$request->paid_to})",
                    [
                        [
                            'account_id' => $expenseAccount->id,
                            'debit' => $amount,
                            'credit' => 0,
                            'description' => "Petty expense: {$request->purpose}",
                        ],
                        [
                            'account_id' => $pettyBank->account_id,
                            'debit' => 0,
                            'credit' => $amount,
                            'description' => "Petty cash payout",
                        ],
                    ],
                    $entry,
                    $entry->entry_date
                );

                $entry->update(['journal_entry_id' => $journalEntry->id]);
            });

            return response()->json([
                'success' => true,
                'message' => "Petty cash expense of ₹{$amount} recorded.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function replenish(Request $request)
    {
        $request->validate([
            'from_bank_account_id' => 'required|exists:finance_bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'reference' => 'nullable|string',
        ]);

        try {
            $fromBank = BankAccount::findOrFail($request->from_bank_account_id);
            $pettyBank = BankAccount::where('account_type', 'cash')->firstOrFail();
            $amount = (float) $request->amount;

            if ($fromBank->current_balance < $amount) {
                throw new Exception("Insufficient funds in {$fromBank->bank_name}.");
            }

            DB::transaction(function () use ($fromBank, $pettyBank, $amount, $request) {
                $fromBank->current_balance -= $amount;
                $fromBank->save();

                $pettyBank->current_balance += $amount;
                $pettyBank->save();

                $entry = PettyCashEntry::create([
                    'entry_date' => now()->toDateString(),
                    'voucher_no' => 'REFILL-' . date('Ymd') . '-' . rand(100, 999),
                    'type' => 'replenishment',
                    'amount' => $amount,
                    'account_id' => $pettyBank->account_id,
                    'paid_to' => 'Society Office Cashier',
                    'purpose' => "Petty Cash Imprest Replenishment from {$fromBank->bank_name}",
                    'handled_by' => auth()->id() ?? 1,
                ]);

                // Post Double Entry: Debit Petty Cash (1050), Credit Main Bank (1020)
                $journalEntry = $this->accountingEngine->postTransaction(
                    "Petty Cash Replenishment from {$fromBank->bank_name}",
                    [
                        [
                            'account_id' => $pettyBank->account_id,
                            'debit' => $amount,
                            'credit' => 0,
                            'description' => "Cash replenishment",
                        ],
                        [
                            'account_id' => $fromBank->account_id,
                            'debit' => 0,
                            'credit' => $amount,
                            'description' => "Withdrawal for petty cash",
                        ],
                    ],
                    $entry,
                    now()->toDateString()
                );

                $entry->update(['journal_entry_id' => $journalEntry->id]);
            });

            return response()->json([
                'success' => true,
                'message' => "Petty cash replenished by ₹{$amount} from {$fromBank->bank_name}.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
