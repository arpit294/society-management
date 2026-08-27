<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Reports Hub
            </a>
            <h2 class="h3 fw-bold text-dark mb-0">Member Statement of Account (Passbook)</h2>
            <p class="text-muted small mb-0">Individual unit financial ledger and complete transaction history</p>
        </div>
    </div>

    <!-- Unit Selector Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('finance.reports.member-passbook') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Select Property Unit <span class="text-danger">*</span></label>
                    <select name="flat_id" class="form-select" required onchange="this.form.submit()">
                        <option value="">-- Choose Flat / Unit --</option>
                        @foreach($flats as $f)
                            @php $res = $f->residents->first(); @endphp
                            <option value="{{ $f->id }}" {{ $flatId == $f->id ? 'selected' : '' }}>
                                {{ $f->block ? 'Block ' . $f->block->block_name . ' - ' : '' }}{{ $f->flat_no }} ({{ $res?->user?->name ?? 'No Resident' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Go</button>
                </div>
            </form>
        </div>
    </div>

    @if($passbook)
    <!-- Passbook Ledger Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0 fw-bold">
                    Statement for {{ $passbook['flat']->block ? 'Block ' . $passbook['flat']->block->block_name . ' - ' : '' }}{{ $passbook['flat']->flat_no }}
                </h5>
                <small class="text-muted">Resident: {{ $passbook['flat']->residents->first()?->user?->name ?? 'N/A' }}</small>
            </div>
            <div class="text-end">
                <span class="small text-muted d-block">Closing Net Dues / Balance:</span>
                <span class="fs-5 fw-bold {{ $passbook['closing_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $passbook['closing_balance'] > 0 ? 'Due: ₹' . number_format($passbook['closing_balance'], 2) : 'Clear (₹0.00)' }}
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Entry Type</th>
                            <th>Reference #</th>
                            <th>Description</th>
                            <th class="text-end">Invoiced / Debit (₹)</th>
                            <th class="text-end">Paid / Credit (₹)</th>
                            <th class="text-end">Running Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($passbook['entries'] as $e)
                        <tr>
                            <td>{{ date('d M Y', strtotime($e['date'])) }}</td>
                            <td>
                                <span class="badge {{ $e['type'] === 'Invoice' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                    {{ $e['type'] }}
                                </span>
                            </td>
                            <td><strong class="text-dark">{{ $e['reference'] }}</strong></td>
                            <td>{{ $e['description'] }}</td>
                            <td class="text-end fw-semibold text-danger">
                                {{ $e['debit'] > 0 ? '₹' . number_format($e['debit'], 2) : '-' }}
                            </td>
                            <td class="text-end fw-semibold text-success">
                                {{ $e['credit'] > 0 ? '₹' . number_format($e['credit'], 2) : '-' }}
                            </td>
                            <td class="text-end fw-bold {{ $e['running_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                ₹{{ number_format($e['running_balance'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No transactions found for this unit.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
</x-user-page>
