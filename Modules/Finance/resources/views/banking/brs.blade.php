<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Bank Reconciliation Statement (BRS)</h2>
            <p class="text-muted small mb-0">Reconcile society cashbook entries against actual bank statements</p>
        </div>
        <div>
            <form method="GET" action="{{ route('finance.banking.reconciliation.index') }}" class="d-flex gap-2">
                <select name="bank_account_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($bankAccounts as $bank)
                        <option value="{{ $bank->id }}" {{ $selectedBank && $selectedBank->id == $bank->id ? 'selected' : '' }}>
                            {{ $bank->bank_name }} ({{ $bank->account_number }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if($selectedBank)
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted small">Book Balance (General Ledger)</span>
                <h3 class="fw-bold text-primary mb-0 mt-1">₹{{ number_format($selectedBank->current_balance, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <span class="text-muted small">Pending / Uncleared Items</span>
                <h3 class="fw-bold text-warning mb-0 mt-1">{{ count($unreconciledTransactions) }} Items</h3>
            </div>
        </div>
    </div>

    <!-- Unreconciled Items Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold fs-6 text-warning">
                <i class="fa-solid fa-clock me-1"></i> Uncleared Transactions for Matching
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Reference (Cheque / UTR)</th>
                            <th class="text-end">Amount (₹)</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unreconciledTransactions as $utx)
                        <tr>
                            <td>{{ $utx->transaction_date->format('d M Y') }}</td>
                            <td><span class="badge {{ $utx->type === 'deposit' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($utx->type) }}</span></td>
                            <td>{{ $utx->description }}</td>
                            <td>{{ $utx->reference_number ?? '-' }}</td>
                            <td class="text-end fw-bold {{ $utx->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($utx->amount, 2) }}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-success btn-reconcile-tx" data-url="{{ route('finance.banking.reconciliation.reconcile', $utx->id) }}">
                                    <i class="fa-solid fa-check me-1"></i> Mark Cleared
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">All transactions for this bank account are reconciled!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $(document).on('click', '.btn-reconcile-tx', function() {
        var url = $(this).data('url');
        $.ajax({
            url: url,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                alert(res.message);
                window.location.reload();
            },
            error: function(err) {
                alert('Error reconciling transaction');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
