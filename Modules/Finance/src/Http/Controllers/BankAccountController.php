<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::with('account')->latest('id')->get();
        $assetAccounts = Account::where('type', 'asset')->where('status', 'active')->get();

        return view('finance::banking.accounts', compact('bankAccounts', 'assetAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50|unique:finance_bank_accounts,account_number',
            'ifsc_code' => 'nullable|string|max:20',
            'branch' => 'nullable|string|max:100',
            'account_type' => 'required|in:savings,current,escrow,cash',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        try {
            // Find or create dedicated sub-account head in Chart of Accounts
            $parentAsset = Account::where('code', '1000')->firstOrFail();
            $code = '102' . (BankAccount::count() + 1);

            $accountHead = Account::create([
                'code' => $code,
                'name' => "Bank - {$request->bank_name} ({$request->account_number})",
                'type' => 'asset',
                'parent_id' => $parentAsset->id,
                'is_system' => false,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $request->opening_balance,
                'status' => 'active',
            ]);

            $bankAccount = BankAccount::create([
                'account_id' => $accountHead->id,
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'ifsc_code' => $request->ifsc_code,
                'branch' => $request->branch,
                'account_type' => $request->account_type,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $request->opening_balance,
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bank Account {$bankAccount->bank_name} created successfully.",
                'bank_account' => $bankAccount,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(BankAccount $bankAccount)
    {
        $transactions = $bankAccount->transactions()->latest('transaction_date')->paginate(30);
        return view('finance::banking.show', compact('bankAccount', 'transactions'));
    }
}
