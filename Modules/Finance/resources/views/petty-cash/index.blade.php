<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Petty Cash Register</h2>
            <p class="text-muted small mb-0">Imprest petty cash balance, daily minor disbursements, and replenishments</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning text-dark btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalReplenish">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Replenish Cash
            </button>
            <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalAddExpense">
                <i class="fa-solid fa-plus me-1"></i> Record Minor Expense
            </button>
        </div>
    </div>

    <!-- Balance Status Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small">Current Petty Cash Balance</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">₹{{ number_format($pettyCashAccount?->current_balance ?? 0, 2) }}</h3>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="fa-solid fa-cash-register fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small">Max Single Expense Limit</span>
                        <h4 class="fw-bold text-secondary mb-0 mt-1">₹{{ number_format(config('finance.petty_cash.single_expense_limit', 2000), 2) }}</h4>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                        <i class="fa-solid fa-shield-halved fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Petty Cash Transactions Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold fs-6">Recent Cash Disbursements & Refills</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Voucher #</th>
                            <th>Type</th>
                            <th>Paid To / Source</th>
                            <th>Purpose / Item</th>
                            <th>Expense Head</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $e)
                        <tr>
                            <td>{{ $e->entry_date->format('d M Y') }}</td>
                            <td><span class="fw-semibold">{{ $e->voucher_no }}</span></td>
                            <td>
                                <span class="badge {{ $e->type === 'replenishment' ? 'bg-success' : 'bg-danger' }}">
                                    {{ strtoupper($e->type) }}
                                </span>
                            </td>
                            <td>{{ $e->paid_to }}</td>
                            <td>{{ $e->purpose }}</td>
                            <td>{{ $e->account ? $e->account->name : 'N/A' }}</td>
                            <td class="text-end fw-bold {{ $e->type === 'replenishment' ? 'text-success' : 'text-danger' }}">
                                {{ $e->type === 'replenishment' ? '+' : '-' }}₹{{ number_format($e->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No petty cash entries recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal: Record Minor Expense -->
<div class="modal fade" id="modalAddExpense" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddExpense" action="{{ route('finance.petty-cash.expense') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Record Petty Cash Expense</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Expense Head <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select" required>
                            @foreach($expenseAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" max="{{ config('finance.petty_cash.single_expense_limit', 2000) }}" name="amount" class="form-control" required placeholder="Max ₹2,000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paid To <span class="text-danger">*</span></label>
                        <input type="text" name="paid_to" class="form-control" required placeholder="e.g. Electrician Sharma, Stationer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Purpose / Description <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" class="form-control" required placeholder="e.g. Purchase of 4 LED tube lights">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Replenish Cash -->
<div class="modal fade" id="modalReplenish" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formReplenish" action="{{ route('finance.petty-cash.replenish') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Replenish Petty Cash from Bank</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Withdraw from Bank Account <span class="text-danger">*</span></label>
                        <select name="from_bank_account_id" class="form-select" required>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} (Avail: ₹{{ number_format($bank->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Replenishment Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold text-success" value="5000" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4">Replenish Cash</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#formAddExpense, #formReplenish').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                window.location.reload();
            },
            error: function(err) {
                alert(err.responseJSON ? err.responseJSON.message : 'Error recording transaction');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
