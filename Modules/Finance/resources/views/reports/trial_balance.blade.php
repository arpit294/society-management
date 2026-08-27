<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Reports Hub
            </a>
            <h2 class="h3 fw-bold text-dark mb-0">Trial Balance</h2>
            <p class="text-muted small mb-0">As of {{ date('d F Y', strtotime($asOfDate)) }}</p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2 align-items-center">
                <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
            </form>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Account Code</th>
                            <th>Account Title</th>
                            <th>Category</th>
                            <th class="text-end">Debit (₹)</th>
                            <th class="text-end">Credit (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['rows'] as $row)
                        <tr>
                            <td><code>{{ $row['code'] }}</code></td>
                            <td>{{ $row['name'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $row['type'] }}</span></td>
                            <td class="text-end fw-semibold">{{ $row['debit'] > 0 ? '₹' . number_format($row['debit'], 2) : '-' }}</td>
                            <td class="text-end fw-semibold">{{ $row['credit'] > 0 ? '₹' . number_format($row['credit'], 2) : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold fs-6">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end text-success">₹{{ number_format($data['total_debit'], 2) }}</td>
                            <td class="text-end text-primary">₹{{ number_format($data['total_credit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-center py-2">
                                @if($data['is_balanced'])
                                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i> Trial Balance is Perfectly Balanced</span>
                                @else
                                    <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Out of Balance by ₹{{ number_format(abs($data['total_debit'] - $data['total_credit']), 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</x-user-page>
