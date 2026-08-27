<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Chart of Accounts (COA)</h2>
            <p class="text-muted small mb-0">General Ledger account hierarchy: Assets, Liabilities, Equity, Incomes, and Expenses</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.accounts.journals') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa-solid fa-book-journal-whills me-1"></i> Journal Entries
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalAddAccount">
                <i class="fa-solid fa-plus me-1"></i> Add Account Head
            </button>
        </div>
    </div>

    <!-- Accounts Hierarchy Tabs -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <ul class="nav nav-pills card-header-pills" id="coaTabs" role="tablist">
                @foreach(['asset' => 'Assets (1000s)', 'liability' => 'Liabilities (2000s)', 'equity' => 'Equity & Funds (3000s)', 'income' => 'Incomes (4000s)', 'expense' => 'Expenses (5000s)'] as $typeKey => $typeLabel)
                <li class="nav-item">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $typeKey }}-tab" data-coreui-toggle="pill" data-coreui-target="#{{ $typeKey }}" type="button">
                        {{ $typeLabel }}
                    </button>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="coaTabsContent">
                @foreach(['asset', 'liability', 'equity', 'income', 'expense'] as $typeKey)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $typeKey }}" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Parent Account</th>
                                    <th>Type</th>
                                    <th class="text-end">Current Balance (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountsByType->get($typeKey, collect()) as $acc)
                                <tr class="{{ empty($acc->parent_id) ? 'fw-bold table-light' : '' }}">
                                    <td><code>{{ $acc->code }}</code></td>
                                    <td>{{ $acc->name }} @if($acc->is_system) <span class="badge bg-secondary-subtle text-secondary small ms-1">System</span> @endif</td>
                                    <td>{{ $acc->parent ? $acc->parent->name : '-' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($acc->type) }}</span></td>
                                    <td class="text-end fw-bold {{ $acc->current_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        ₹{{ number_format($acc->current_balance, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No accounts under this category.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Account Head -->
<div class="modal fade" id="modalAddAccount" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddAccount" action="{{ route('finance.accounts.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Add Chart of Accounts Head</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Account Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required placeholder="e.g. 5080, 4080">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Garden Landscaping, Solar Power Maintenance">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Account Classification <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity / Fund</option>
                                <option value="income">Income</option>
                                <option value="expense" selected>Expense</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Parent Category Head</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Top Level)</option>
                                @foreach($accounts->whereNull('parent_id') as $p)
                                    <option value="{{ $p->id }}">{{ $p->code }} - {{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Opening Balance (₹)</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="0.00">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#formAddAccount').on('submit', function(e) {
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
                alert(err.responseJSON ? err.responseJSON.message : 'Error adding account');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
