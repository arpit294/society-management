<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Reports Hub
            </a>
            <h2 class="h3 fw-bold text-dark mb-0">Income & Expenditure Statement</h2>
            <p class="text-muted small mb-0">Period: {{ date('d M Y', strtotime($startDate)) }} to {{ date('d M Y', strtotime($endDate)) }}</p>
        </div>
        <div>
            <form method="GET" class="d-flex gap-2 align-items-center">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
            </form>
        </div>
    </div>

    <!-- Summary Banner -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-success-subtle text-success">
                <span class="small fw-semibold">Total Society Revenues</span>
                <h3 class="fw-bold mb-0 mt-1">₹{{ number_format($data['total_income'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-danger-subtle text-danger">
                <span class="small fw-semibold">Total Operating Expenses</span>
                <h3 class="fw-bold mb-0 mt-1">₹{{ number_format($data['total_expense'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 {{ $data['net_surplus_deficit'] >= 0 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }}">
                <span class="small fw-semibold">Net {{ $data['net_surplus_deficit'] >= 0 ? 'Surplus' : 'Deficit' }}</span>
                <h3 class="fw-bold mb-0 mt-1">₹{{ number_format($data['net_surplus_deficit'], 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Income & Expense Dual Tables -->
    <div class="row g-4">
        <!-- Incomes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-success fs-6">
                        <i class="fa-solid fa-arrow-trend-up me-1"></i> Society Revenues / Incomes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Revenue Head</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['incomes'] as $inc)
                            <tr>
                                <td><code>{{ $inc['code'] }}</code></td>
                                <td>{{ $inc['name'] }}</td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($inc['amount'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">No income recorded for this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Total Income:</td>
                                <td class="text-end text-success fs-6">₹{{ number_format($data['total_income'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expenses -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-danger fs-6">
                        <i class="fa-solid fa-arrow-trend-down me-1"></i> Operational Expenses
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Expense Category</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['expenses'] as $exp)
                            <tr>
                                <td><code>{{ $exp['code'] }}</code></td>
                                <td>{{ $exp['name'] }}</td>
                                <td class="text-end fw-bold text-danger">₹{{ number_format($exp['amount'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">No expenses recorded for this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Total Expenses:</td>
                                <td class="text-end text-danger fs-6">₹{{ number_format($data['total_expense'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</x-user-page>
