<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\BankTransaction;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::where('account_type', '!=', 'cash')->where('status', 'active')->get();
        $selectedBankId = $request->input('bank_account_id', $bankAccounts->first()?->id);
        $selectedBank = BankAccount::find($selectedBankId);

        $unreconciledTransactions = collect();
        $reconciledTransactions = collect();

        if ($selectedBank) {
            $unreconciledTransactions = $selectedBank->transactions()
                ->where('is_reconciled', false)
                ->orderBy('transaction_date')
                ->get();

            $reconciledTransactions = $selectedBank->transactions()
                ->where('is_reconciled', true)
                ->latest('transaction_date')
                ->limit(20)
                ->get();
        }

        return view('finance::banking.brs', compact('bankAccounts', 'selectedBank', 'unreconciledTransactions', 'reconciledTransactions'));
    }

    public function reconcile(Request $request, BankTransaction $transaction)
    {
        $transaction->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction marked as reconciled with bank statement.',
        ]);
    }
}
