<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\BankAccount;
use Modules\Finance\Models\Fund;

class FundController extends Controller
{
    public function index()
    {
        $funds = Fund::with(['account', 'bankAccount'])->latest('id')->get();
        $equityAccounts = Account::where('type', 'equity')->where('status', 'active')->get();
        $bankAccounts = BankAccount::where('status', 'active')->get();

        return view('finance::funds.index', compact('funds', 'equityAccounts', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:sinking_fund,reserve_fund,fixed_deposit,corpus_fund',
            'account_id' => 'required|exists:finance_chart_of_accounts,id',
            'bank_account_id' => 'nullable|exists:finance_bank_accounts,id',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'maturity_date' => 'nullable|date',
            'certificate_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $fund = Fund::create([
                'name' => $request->name,
                'type' => $request->type,
                'account_id' => $request->account_id,
                'bank_account_id' => $request->bank_account_id,
                'principal_amount' => $request->principal_amount,
                'current_balance' => $request->principal_amount,
                'interest_rate' => $request->interest_rate,
                'start_date' => $request->start_date,
                'maturity_date' => $request->maturity_date,
                'certificate_no' => $request->certificate_no,
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Fund/FD {$fund->name} created successfully.",
                'fund' => $fund,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
