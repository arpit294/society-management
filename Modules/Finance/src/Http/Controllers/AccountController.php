<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\AccountingEngineService;

class AccountController extends Controller
{
    public function __construct(
        protected AccountingEngineService $accountingEngine
    ) {}

    public function index()
    {
        $accounts = Account::with(['parent', 'children'])->orderBy('code')->get();
        $accountsByType = $accounts->groupBy('type');
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();

        return view('finance::accounts.index', compact('accounts', 'accountsByType', 'fiscalYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:finance_chart_of_accounts,code',
            'name' => 'required|string|max:150',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:finance_chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        try {
            $opening = (float) ($request->opening_balance ?? 0);
            $account = Account::create([
                'code' => $request->code,
                'name' => $request->name,
                'type' => $request->type,
                'parent_id' => $request->parent_id,
                'is_system' => false,
                'opening_balance' => $opening,
                'current_balance' => $opening,
                'description' => $request->description,
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Account {$account->code} - {$account->name} created.",
                'account' => $account,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function journalEntries()
    {
        $entries = JournalEntry::with(['items.account', 'creator'])->latest('entry_date')->paginate(30);
        $accounts = Account::where('status', 'active')->orderBy('code')->get();

        return view('finance::accounts.journals', compact('entries', 'accounts'));
    }

    public function storeManualJournal(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:finance_chart_of_accounts,id',
            'items.*.debit' => 'nullable|numeric|min:0',
            'items.*.credit' => 'nullable|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            $entry = $this->accountingEngine->postTransaction(
                $request->description,
                $request->items,
                null,
                $request->entry_date
            );

            return response()->json([
                'success' => true,
                'message' => "Journal Entry {$entry->entry_number} posted successfully.",
                'entry' => $entry,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
