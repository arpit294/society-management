<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">General Ledger & Journal Entries</h2>
            <p class="text-muted small mb-0">Audit trail of all double-entry transaction debits and credits across the society</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm px-3" data-coreui-toggle="modal" data-coreui-target="#modalAddJournal">
            <i class="fa-solid fa-plus me-1"></i> Manual Journal Entry
        </button>
    </div>

    <!-- Journal Entries Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Entry #</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Lines / Accounts Involved</th>
                            <th class="text-end">Debit (₹)</th>
                            <th class="text-end">Credit (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $j)
                        <tr>
                            <td><strong class="text-primary">{{ $j->entry_number }}</strong></td>
                            <td>{{ $j->entry_date->format('d M Y') }}</td>
                            <td>{{ $j->description }}</td>
                            <td>
                                <ul class="list-unstyled small mb-0">
                                    @foreach($j->items as $item)
                                    <li>
                                        {{ $item->account?->name }} 
                                        @if($item->debit > 0) <span class="text-success fw-semibold">(Dr: ₹{{ number_format($item->debit, 2) }})</span> @endif
                                        @if($item->credit > 0) <span class="text-danger fw-semibold">(Cr: ₹{{ number_format($item->credit, 2) }})</span> @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($j->total_debit, 2) }}</td>
                            <td class="text-end fw-bold text-danger">₹{{ number_format($j->total_credit, 2) }}</td>
                            <td><span class="badge bg-success">{{ ucfirst($j->status) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No journal entries found.</td>
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

<!-- Modal: Manual Journal Entry -->
<div class="modal fade" id="modalAddJournal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddJournal" action="{{ route('finance.accounts.journals.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Post Manual Journal Entry</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Entry Date <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Journal Narration / Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" required placeholder="e.g. Year-end Depreciation, FD Interest Accrual">
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Journal Lines (Debits must equal Credits)</h6>

                    <!-- Line 1 (Debit) -->
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-md-6">
                            <select name="items[0][account_id]" class="form-select" required>
                                <option value="">-- Select Debit Account --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="items[0][debit]" class="form-control" placeholder="Debit (₹)" required>
                            <input type="hidden" name="items[0][credit]" value="0">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="items[0][description]" class="form-control" placeholder="Debit line note">
                        </div>
                    </div>

                    <!-- Line 2 (Credit) -->
                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-md-6">
                            <select name="items[1][account_id]" class="form-select" required>
                                <option value="">-- Select Credit Account --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="items[1][debit]" value="0">
                            <input type="number" step="0.01" name="items[1][credit]" class="form-control" placeholder="Credit (₹)" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="items[1][description]" class="form-control" placeholder="Credit line note">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Post Journal Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#formAddJournal').on('submit', function(e) {
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
                alert(err.responseJSON ? err.responseJSON.message : 'Error posting journal entry');
            }
        });
    });
});
</script>
@endpush
</x-user-page>
