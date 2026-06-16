<x-user-page>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Maintenance Report</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.maintenance') }}" class="row g-3 mb-4 align-items-end" id="filterForm">
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select" id="reportTypeSelect" onchange="this.form.submit()">
                            <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>

                    <div class="col-md-3" id="monthContainer" style="display: {{ $reportType == 'yearly' ? 'none' : 'block' }};">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select" onchange="this.form.submit()">
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                <option value="{{ $month }}" {{ isset($selectedMonth) && $selectedMonth == $month ? 'selected' : '' }}>{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            @php 
                                $currentYear = date('Y');
                                $years = $availableDates->pluck('year')->push($currentYear)->unique()->sortDesc();
                            @endphp
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        @if(request()->has('month') || request()->has('year') || request()->has('report_type'))
                            <a href="{{ route('reports.maintenance') }}" class="btn btn-outline-secondary w-100">Reset</a>
                        @endif
                        <button type="submit" formaction="{{ route('reports.maintenance.export') }}" class="btn btn-success text-white w-100">
                            <svg class="icon me-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width: 1rem; height: 1rem; fill: currentColor;">
                                <path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
                            </svg>
                            Export
                        </button>
                    </div>
                </form>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card dash-card card-flats h-100 shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fs-4 fw-bold">₹{{ number_format($reportType == 'yearly' ? $yearlyExpected : $totalExpected, 2) }}</div>
                                    <div class="text-uppercase fw-semibold small opacity-75">Total Expected</div>
                                </div>
                                <div class="fs-1 text-primary">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dash-card card-revenue h-100 shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fs-4 fw-bold">₹{{ number_format($reportType == 'yearly' ? $yearlyPaid : $totalPaid, 2) }}</div>
                                    <div class="text-uppercase fw-semibold small opacity-75">Total Paid</div>
                                </div>
                                <div class="fs-1 text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dash-card card-complaints h-100 shadow-sm border-0">
                            <div class="card-body d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fs-4 fw-bold">₹{{ number_format($reportType == 'yearly' ? $yearlyPending : $totalPending, 2) }}</div>
                                    <div class="text-uppercase fw-semibold small opacity-75">Total Pending</div>
                                </div>
                                <div class="fs-1 text-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($reportType == 'monthly')
                    <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="paid-tab" data-coreui-toggle="tab" data-coreui-target="#paid" type="button" role="tab" aria-controls="paid" aria-selected="true">Paid Residents ({{ $paidBills->count() }})</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-coreui-toggle="tab" data-coreui-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="false">Pending Maintenance ({{ $pendingBills->count() }})</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="reportTabsContent">
                        <div class="tab-pane fade show active" id="paid" role="tabpanel" aria-labelledby="paid-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Resident</th>
                                            <th>Block - Flat</th>
                                            <th>Paid Amount</th>
                                            <th>Payment Method</th>
                                            <th>Paid Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paidBills as $bill)
                                            <tr>
                                                <td>{{ $bill->user->name ?? 'N/A' }}</td>
                                                <td>{{ $bill->block->block_name ?? 'N/A' }} - {{ $bill->flat->flat_no ?? 'N/A' }}</td>
                                                <td>{{ number_format($bill->total_amount, 2) }}</td>
                                                <td>{{ ucfirst($bill->payment_method) }}</td>
                                                <td>{{ $bill->paid_at ? $bill->paid_at->format('d M Y') : 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No paid bills found for this month.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Resident</th>
                                            <th>Block - Flat</th>
                                            <th>Base Amount</th>
                                            <th>Penalty Amount</th>
                                            <th>Total Due</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingBills as $bill)
                                            <tr>
                                                <td>{{ $bill->user->name ?? 'N/A' }}</td>
                                                <td>{{ $bill->block->block_name ?? 'N/A' }} - {{ $bill->flat->flat_no ?? 'N/A' }}</td>
                                                <td>{{ number_format($bill->amount, 2) }}</td>
                                                <td>{{ number_format($bill->penalty_amount, 2) }}</td>
                                                <td>{{ number_format($bill->total_amount, 2) }}</td>
                                                <td><span class="badge bg-danger">{{ ucfirst($bill->status) }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No pending bills found for this month.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Yearly Breakdown ({{ $selectedYear }})</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Month</th>
                                            <th class="text-end">Expected Amount</th>
                                            <th class="text-end text-success">Paid Amount</th>
                                            <th class="text-end text-danger">Pending Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($monthlyBreakdown as $data)
                                            <tr>
                                                <td><strong>{{ $data->month }}</strong></td>
                                                <td class="text-end">{{ number_format($data->expected, 2) }}</td>
                                                <td class="text-end text-success">{{ number_format($data->paid, 2) }}</td>
                                                <td class="text-end text-danger">{{ number_format($data->pending, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light font-weight-bold">
                                        <tr>
                                            <td><strong>Total</strong></td>
                                            <td class="text-end"><strong>{{ number_format($yearlyExpected, 2) }}</strong></td>
                                            <td class="text-end text-success"><strong>{{ number_format($yearlyPaid, 2) }}</strong></td>
                                            <td class="text-end text-danger"><strong>{{ number_format($yearlyPending, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('reportTypeSelect').addEventListener('change', function() {
        if(this.value === 'yearly') {
            document.getElementById('monthContainer').style.display = 'none';
        } else {
            document.getElementById('monthContainer').style.display = 'block';
        }
    });
</script>
</x-user-page>
