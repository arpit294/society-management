<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Reports Hub
            </a>
            <h2 class="h3 fw-bold text-dark mb-0">Maintenance Dues Aging & Defaulters</h2>
            <p class="text-muted small mb-0">Arrears aging brackets and resident collection follow-up tracker</p>
        </div>
    </div>

    <!-- Aging Summary Buckets -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-warning border-4">
                <span class="text-muted small fw-semibold">0 - 30 Days Arrears</span>
                <h4 class="fw-bold text-warning mb-0 mt-1">₹{{ number_format($data['summary']['0_30'], 2) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-warning border-4">
                <span class="text-muted small fw-semibold">31 - 60 Days Arrears</span>
                <h4 class="fw-bold text-warning mb-0 mt-1">₹{{ number_format($data['summary']['31_60'], 2) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-danger border-4">
                <span class="text-muted small fw-semibold">61 - 90 Days Arrears</span>
                <h4 class="fw-bold text-danger mb-0 mt-1">₹{{ number_format($data['summary']['61_90'], 2) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-danger border-4">
                <span class="text-muted small fw-semibold">90+ Days (Severe Overdue)</span>
                <h4 class="fw-bold text-danger mb-0 mt-1">₹{{ number_format($data['summary']['90_plus'], 2) }}</h4>
            </div>
        </div>
    </div>

    <!-- Defaulters Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-danger fs-6">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Overdue Members & Defaulter List
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Property Unit</th>
                            <th>Resident Name</th>
                            <th>Contact Phone</th>
                            <th>Due Date</th>
                            <th>Overdue Days</th>
                            <th>Aging Bracket</th>
                            <th class="text-end">Balance Due (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['defaulters'] as $def)
                        <tr>
                            <td>
                                <a href="{{ route('finance.invoices.show', $def['invoice_id']) }}" class="fw-semibold text-primary">
                                    {{ $def['invoice_number'] }}
                                </a>
                            </td>
                            <td><strong class="text-dark">{{ $def['flat_no'] }}</strong></td>
                            <td>{{ $def['resident_name'] }}</td>
                            <td>{{ $def['resident_phone'] }}</td>
                            <td>{{ $def['due_date'] }}</td>
                            <td>
                                <span class="badge {{ $def['days_overdue'] > 60 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $def['days_overdue'] }} Days
                                </span>
                            </td>
                            <td>{{ $def['aging_bucket'] }}</td>
                            <td class="text-end fw-bold text-danger">₹{{ number_format($def['balance_due'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No overdue balances! All members are up-to-date with payments.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold fs-6">
                            <td colspan="7" class="text-end">Total Outstanding Dues:</td>
                            <td class="text-end text-danger">₹{{ number_format($data['summary']['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</x-user-page>
