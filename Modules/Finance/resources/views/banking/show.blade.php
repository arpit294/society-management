<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('finance.banking.accounts.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Accounts
            </a>
            <h2 class="h3 fw-bold text-dark mb-0">{{ $bankAccount->bank_name }} - Passbook</h2>
            <p class="text-muted small mb-0">{{ $bankAccount->account_name }} ({{ $bankAccount->account_number }})</p>
        </div>
        <div>
            <span class="text-muted small d-block text-end">Current Balance</span>
            <h3 class="fw-bold text-success mb-0">₹{{ number_format($bankAccount->current_balance, 2) }}</h3>
        </div>
    </div>

    <!-- Passbook Transactions Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference (UTR / Cheque)</th>
                            <th>Reconciliation</th>
                            <th class="text-end">Deposit (Dr)</th>
                            <th class="text-end">Withdrawal (Cr)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                            <td>{{ $tx->description }}</td>
                            <td><small class="fw-semibold">{{ $tx->reference_number ?? '-' }}</small></td>
                            <td>
                                @if($tx->is_reconciled)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-check me-1"></i> Reconciled</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Uncleared</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ $tx->type === 'deposit' ? '₹' . number_format($tx->amount, 2) : '-' }}
                            </td>
                            <td class="text-end fw-bold text-danger">
                                {{ $tx->type === 'withdrawal' ? '₹' . number_format($tx->amount, 2) : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No transactions recorded for this account yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
</x-user-page>
