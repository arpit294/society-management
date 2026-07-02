<x-user-page>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa-solid fa-chart-line text-primary me-2"></i>Financial & Maintenance Reports</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.maintenance') }}" class="row g-3 mb-4 align-items-end" id="filterForm">
                    <input type="hidden" name="active_tab" id="activeTabInput" value="{{ request('active_tab', '#main-maintenance') }}">
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select js-auto-submit" id="reportTypeSelect">
                            <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>

                    <div class="col-md-3" id="monthContainer" style="display: {{ $reportType == 'yearly' ? 'none' : 'block' }};">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select js-auto-submit">
                            @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                <option value="{{ $month }}" {{ isset($selectedMonth) && $selectedMonth == $month ? 'selected' : '' }}>{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select js-auto-submit">
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

                <ul class="nav nav-pills nav-fill mb-4 p-2 bg-light bg-opacity-75 rounded-4 shadow-sm border" id="mainReportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-4 fw-bold py-3 fs-6 d-flex align-items-center justify-content-center gap-2" id="main-maintenance-tab" data-coreui-toggle="tab" data-coreui-target="#main-maintenance" type="button" role="tab" aria-controls="main-maintenance" aria-selected="true">
                            <i class="fa-solid fa-building-circle-check fs-5 text-primary"></i> Maintenance Collection Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-4 fw-bold py-3 fs-6 d-flex align-items-center justify-content-center gap-2" id="main-expense-tab" data-coreui-toggle="tab" data-coreui-target="#main-expense" type="button" role="tab" aria-controls="main-expense" aria-selected="false">
                            <i class="fa-solid fa-file-invoice-dollar fs-5 text-warning"></i> Society Expense Report
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-4 fw-bold py-3 fs-6 d-flex align-items-center justify-content-center gap-2" id="main-summary-tab" data-coreui-toggle="tab" data-coreui-target="#main-summary" type="button" role="tab" aria-controls="main-summary" aria-selected="false">
                            <i class="fa-solid fa-scale-balanced fs-5 text-success"></i> Revenue vs Expense Summary
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="mainReportTabsContent">
                    <div class="tab-pane fade show active" id="main-maintenance" role="tabpanel" aria-labelledby="main-maintenance-tab">
                        <div class="row mb-4">

                    <div class="col-md-4">
                        <div class="card border-0 border-start border-4 border-info shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-normal mb-2">Total Expected Amount</h5>
                                <h3 class="text-info fw-bold mb-0">{{ \App\Helpers\CurrencyHelper::formatCurrency($reportType == 'yearly' ? $yearlyExpected : $totalExpected) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 border-start border-4 border-success shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-normal mb-2">Total Paid Amount</h5>
                                <h3 class="text-success fw-bold mb-0">{{ \App\Helpers\CurrencyHelper::formatCurrency($reportType == 'yearly' ? $yearlyPaid : $totalPaid) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 border-start border-4 border-danger shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-normal mb-2">Total Pending Amount</h5>
                                <h3 class="text-danger fw-bold mb-0">{{ \App\Helpers\CurrencyHelper::formatCurrency($reportType == 'yearly' ? $yearlyPending : $totalPending) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                @if($reportType == 'monthly')
                    @php
                        $paymentMethods = $paidBills->groupBy(fn($b) => !empty($b->payment_method) ? $b->payment_method : 'other')->map->sum('total_amount');
                        if ($paymentMethods->isEmpty()) {
                            $methodLabels = ['No Payments Yet'];
                            $methodValues = [0];
                        } else {
                            $methodLabels = $paymentMethods->keys()->map(fn($m) => ucwords(str_replace('_', ' ', $m)))->values();
                            $methodValues = $paymentMethods->values()->map(fn($v) => round($v, 2))->values();
                        }

                        $blocksExpected = [];
                        $blocksPaid = [];
                        $blocksPending = [];
                        foreach ($paidBills as $bill) {
                            $bName = $bill->block->block_name ?? 'No Block';
                            $blocksPaid[$bName] = ($blocksPaid[$bName] ?? 0) + $bill->total_amount;
                            $blocksExpected[$bName] = ($blocksExpected[$bName] ?? 0) + $bill->total_amount;
                        }
                        foreach ($pendingBills as $bill) {
                            $bName = $bill->block->block_name ?? 'No Block';
                            $blocksPending[$bName] = ($blocksPending[$bName] ?? 0) + $bill->total_amount;
                            $blocksExpected[$bName] = ($blocksExpected[$bName] ?? 0) + $bill->total_amount;
                        }
                        if (empty($blocksExpected)) {
                            $blockNames = ['No Blocks'];
                            $blockPaidData = [0];
                            $blockPendingData = [0];
                        } else {
                            ksort($blocksExpected);
                            $blockNames = array_keys($blocksExpected);
                            $blockPaidData = array_map(fn($b) => round($blocksPaid[$b] ?? 0, 2), $blockNames);
                            $blockPendingData = array_map(fn($b) => round($blocksPending[$b] ?? 0, 2), $blockNames);
                        }
                    @endphp

                    <div class="row mb-4 g-4">
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                <div class="card-header bg-white fw-bold d-flex align-items-center">
                                    <i class="fa-solid fa-chart-pie text-primary me-2"></i> Collection Status Overview
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 280px;">
                                    <canvas id="monthlyCollectionChart" style="max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                <div class="card-header bg-white fw-bold d-flex align-items-center">
                                    <i class="fa-solid fa-wallet text-info me-2"></i> Payment Methods Breakdown
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 280px;">
                                    <canvas id="paymentMethodChart" style="max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0 bg-light bg-opacity-50">
                                <div class="card-header bg-white fw-bold d-flex align-items-center">
                                    <i class="fa-solid fa-building text-success me-2"></i> Block-Wise Collection vs Pending Dues
                                </div>
                                <div class="card-body">
                                    <canvas id="blockWiseChart" style="max-height: 300px; width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                <table id="paidTable" class="table table-bordered table-striped table-hover">
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
                                        @foreach($paidBills as $bill)
                                            <tr>
                                                <td>{{ $bill->user->name ?? 'N/A' }}</td>
                                                <td>{{ $bill->block->block_name ?? 'N/A' }} - {{ $bill->flat->flat_no ?? 'N/A' }}</td>
                                                <td>{{ \App\Helpers\CurrencyHelper::formatCurrency($bill->total_amount) }}</td>
                                                <td>{{ ucfirst($bill->payment_method) }}</td>
                                                <td>{{ $bill->paid_at ? $bill->paid_at->format('d M Y') : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot style="background: rgba(16, 185, 129, 0.15) !important;" class="fw-bold fs-6">
                                        <tr style="border-top: 2px solid #10b981 !important; border-bottom: 2px solid #10b981 !important;">
                                            <td colspan="2" class="py-3"><span class="badge bg-success px-3 py-2 fs-6 shadow-sm"><i class="fa-solid fa-check-circle me-1"></i> TOTAL PAID AMOUNT</span></td>
                                            <td class="py-3 fs-6 fw-bolder" style="color: #34d399 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalPaid) }}</td>
                                            <td colspan="2" class="py-3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            <div class="table-responsive">
                                <table id="pendingTable" class="table table-bordered table-striped table-hover">
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
                                        @foreach($pendingBills as $bill)
                                            <tr>
                                                <td>{{ $bill->user->name ?? 'N/A' }}</td>
                                                <td>{{ $bill->block->block_name ?? 'N/A' }} - {{ $bill->flat->flat_no ?? 'N/A' }}</td>
                                                <td>{{ \App\Helpers\CurrencyHelper::formatCurrency($bill->amount) }}</td>
                                                <td>{{ \App\Helpers\CurrencyHelper::formatCurrency($bill->penalty_amount) }}</td>
                                                <td>{{ \App\Helpers\CurrencyHelper::formatCurrency($bill->total_amount) }}</td>
                                                <td><span class="badge bg-danger">{{ ucfirst($bill->status) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot style="background: rgba(239, 68, 68, 0.15) !important;" class="fw-bold fs-6">
                                        <tr style="border-top: 2px solid #ef4444 !important; border-bottom: 2px solid #ef4444 !important;">
                                            <td colspan="2" class="py-3"><span class="badge bg-danger px-3 py-2 fs-6 shadow-sm"><i class="fa-solid fa-triangle-exclamation me-1"></i> TOTAL PENDING DUE</span></td>
                                            <td class="py-3 fs-6 fw-bolder" style="color: #38bdf8 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($pendingBills->sum('amount')) }}</td>
                                            <td class="py-3 fs-6 fw-bolder" style="color: #fbbf24 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($pendingBills->sum('penalty_amount')) }}</td>
                                            <td class="py-3 fs-6 fw-bolder" style="color: #f87171 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalPending) }}</td>
                                            <td class="py-3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    @php
                        $yearlyMonths = collect($monthlyBreakdown)->pluck('month')->values();
                        $yearlyExpectedData = collect($monthlyBreakdown)->map(fn($m) => round($m->expected, 2))->values();
                        $yearlyPaidData = collect($monthlyBreakdown)->map(fn($m) => round($m->paid, 2))->values();
                        $yearlyPendingData = collect($monthlyBreakdown)->map(fn($m) => round($m->pending, 2))->values();
                    @endphp

                    <div class="row mb-4 g-4">
                        <div class="col-lg-8">
                            <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                <div class="card-header bg-white fw-bold d-flex align-items-center">
                                    <i class="fa-solid fa-chart-column text-primary me-2"></i> Month-by-Month Collection Trend ({{ $selectedYear }})
                                </div>
                                <div class="card-body">
                                    <canvas id="yearlyTrendChart" style="max-height: 300px; width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                <div class="card-header bg-white fw-bold d-flex align-items-center">
                                    <i class="fa-solid fa-chart-pie text-success me-2"></i> Overall Collection Efficiency
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 280px;">
                                    <canvas id="yearlyEfficiencyChart" style="max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Yearly Breakdown ({{ $selectedYear }})</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="yearlyTable" class="table table-bordered table-striped table-hover mb-0">
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
                                                <td class="text-end">{{ \App\Helpers\CurrencyHelper::formatCurrency($data->expected) }}</td>
                                                <td class="text-end text-success">{{ \App\Helpers\CurrencyHelper::formatCurrency($data->paid) }}</td>
                                                <td class="text-end text-danger">{{ \App\Helpers\CurrencyHelper::formatCurrency($data->pending) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot style="background: rgba(99, 102, 241, 0.18) !important;" class="fw-bold fs-6">
                                        <tr style="border-top: 2px solid #6366f1 !important; border-bottom: 2px solid #6366f1 !important;">
                                            <td class="py-3"><span class="badge bg-primary px-3 py-2 fs-6 shadow-sm"><i class="fa-solid fa-calculator me-1"></i> GRAND TOTAL</span></td>
                                            <td class="text-end py-3 fs-6 fw-bolder" style="color: #38bdf8 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($yearlyExpected) }}</td>
                                            <td class="text-end py-3 fs-6 fw-bolder" style="color: #34d399 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($yearlyPaid) }}</td>
                                            <td class="text-end py-3 fs-6 fw-bolder" style="color: #f87171 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($yearlyPending) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
                    </div> <!-- Close main-maintenance -->

                    <!-- Tab 2: Expense Report -->
                    <div class="tab-pane fade" id="main-expense" role="tabpanel" aria-labelledby="main-expense-tab">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-0 border-start border-4 border-danger shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title fw-normal mb-2">Total Society Expenses</h5>
                                        <h3 class="text-danger fw-bold mb-0">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalExpense) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 border-start border-4 border-warning shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title fw-normal mb-2">Active Expense Categories</h5>
                                        <h3 class="text-warning fw-bold mb-0">{{ $expenseCategories->count() }} Categories</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 border-start border-4 border-info shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title fw-normal mb-2">Total Transactions Logged</h5>
                                        <h3 class="text-info fw-bold mb-0">{{ $expensesList->count() }} Entries</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($reportType == 'monthly')
                            @php
                                if ($expenseCategories->isEmpty()) {
                                    $expCatLabels = ['No Expenses'];
                                    $expCatValues = [0];
                                } else {
                                    $expCatLabels = $expenseCategories->keys()->values();
                                    $expCatValues = $expenseCategories->values()->map(fn($v) => round($v, 2))->values();
                                }
                            @endphp
                            <div class="row mb-4 g-4">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                        <div class="card-header bg-white fw-bold d-flex align-items-center">
                                            <i class="fa-solid fa-chart-pie text-warning me-2"></i> Monthly Expense by Category
                                        </div>
                                        <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 280px;">
                                            <canvas id="monthlyExpenseCategoryChart" style="max-height: 250px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                        <div class="card-header bg-white fw-bold d-flex align-items-center">
                                            <i class="fa-solid fa-chart-bar text-danger me-2"></i> Category Spending Comparison
                                        </div>
                                        <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 280px;">
                                            <canvas id="monthlyExpenseBarChart" style="max-height: 250px; width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @php
                                $yearlyExpMonths = collect($monthlyBreakdown)->pluck('month')->values();
                                $yearlyExpData = collect($monthlyBreakdown)->map(fn($m) => round($m->expense, 2))->values();
                                if ($expenseCategories->isEmpty()) {
                                    $expCatLabels = ['No Expenses'];
                                    $expCatValues = [0];
                                } else {
                                    $expCatLabels = $expenseCategories->keys()->values();
                                    $expCatValues = $expenseCategories->values()->map(fn($v) => round($v, 2))->values();
                                }
                            @endphp
                            <div class="row mb-4 g-4">
                                <div class="col-lg-8">
                                    <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                        <div class="card-header bg-white fw-bold d-flex align-items-center">
                                            <i class="fa-solid fa-chart-column text-danger me-2"></i> Month-by-Month Expense Trend ({{ $selectedYear }})
                                        </div>
                                        <div class="card-body">
                                            <canvas id="yearlyExpenseTrendChart" style="max-height: 300px; width: 100%;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card h-100 shadow-sm border-0 bg-light bg-opacity-50">
                                        <div class="card-header bg-white fw-bold d-flex align-items-center">
                                            <i class="fa-solid fa-chart-pie text-warning me-2"></i> Yearly Category Breakdown
                                        </div>
                                        <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 280px;">
                                            <canvas id="yearlyExpenseCategoryChart" style="max-height: 250px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                                <span><i class="fa-solid fa-list text-primary me-2"></i> Detailed Expense Transactions</span>
                                <span class="badge bg-secondary">{{ $expensesList->count() }} Records</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Expense Title</th>
                                                <th>Category</th>
                                                <th>Logged By</th>
                                                <th>Expense Date</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($expensesList as $index => $exp)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="fw-semibold">{{ $exp->title }}</td>
                                                    <td><span class="badge bg-info text-dark">{{ $exp->category ? $exp->category->title : 'Uncategorized' }}</span></td>
                                                    <td>{{ $exp->user ? $exp->user->name : 'N/A' }}</td>
                                                    <td>{{ $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->format('d M Y') : $exp->created_at->format('d M Y') }}</td>
                                                    <td class="text-end fw-bold text-danger">{{ \App\Helpers\CurrencyHelper::formatCurrency($exp->total_amount) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">No expenses recorded for this period.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if($expensesList->isNotEmpty())
                                            <tfoot style="background: rgba(239, 68, 68, 0.15) !important;" class="fw-bold fs-6">
                                                <tr style="border-top: 2px solid #ef4444 !important; border-bottom: 2px solid #ef4444 !important;">
                                                    <td colspan="5" class="py-3"><span class="badge bg-danger px-3 py-2 fs-6 shadow-sm"><i class="fa-solid fa-receipt me-1"></i> TOTAL EXPENSES</span></td>
                                                    <td class="text-end py-3 fs-6 fw-bolder" style="color: #f87171 !important;">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalExpense) }}</td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Close main-expense -->

                    <!-- Tab 3: Revenue vs Expense Summary -->
                    <div class="tab-pane fade" id="main-summary" role="tabpanel" aria-labelledby="main-summary-tab">
                        @php
                            $revenueTotal = $reportType == 'yearly' ? $yearlyPaid : $totalPaid;
                            $netAmount = $revenueTotal - $totalExpense;
                        @endphp
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-0 border-start border-4 border-success shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title fw-normal mb-2">Total Collected Revenue</h5>
                                        <h3 class="text-success fw-bold mb-0">{{ \App\Helpers\CurrencyHelper::formatCurrency($revenueTotal) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 border-start border-4 border-danger shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title fw-normal mb-2">Total Society Expenses</h5>
                                        <h3 class="text-danger fw-bold mb-0">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalExpense) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 border-start border-4 {{ $netAmount >= 0 ? 'border-primary' : 'border-warning' }} shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title fw-normal mb-2">Net Financial {{ $netAmount >= 0 ? 'Surplus (Profit)' : 'Deficit (Loss)' }}</h5>
                                        <h3 class="{{ $netAmount >= 0 ? 'text-primary' : 'text-warning' }} fw-bold mb-0">{{ \App\Helpers\CurrencyHelper::formatCurrency($netAmount) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card shadow-sm border-0 bg-light bg-opacity-50">
                                    <div class="card-header bg-white fw-bold d-flex align-items-center">
                                        <i class="fa-solid fa-chart-column text-primary me-2"></i> Revenue vs Expense Comparison Chart
                                    </div>
                                    <div class="card-body">
                                        <canvas id="revenueVsExpenseChart" style="max-height: 350px; width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Close main-summary -->
                </div> <!-- Close mainReportTabsContent -->
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof Chart === "undefined") return;

        @if($reportType == 'monthly')
            var ctxOverview = document.getElementById("monthlyCollectionChart");
            if (ctxOverview) {
                new Chart(ctxOverview.getContext("2d"), {
                    type: "doughnut",
                    data: {
                        labels: ["Collected Amount", "Pending Amount"],
                        datasets: [{
                            data: [{{ round($totalPaid, 2) }}, {{ round($totalPending, 2) }}],
                            backgroundColor: ["#198754", "#dc3545"],
                            borderWidth: 2,
                            borderColor: "#ffffff"
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: "bottom" }
                        }
                    }
                });
            }

            var ctxMethods = document.getElementById("paymentMethodChart");
            if (ctxMethods) {
                new Chart(ctxMethods.getContext("2d"), {
                    type: "pie",
                    data: {
                        labels: {!! json_encode($methodLabels) !!},
                        datasets: [{
                            data: {!! json_encode($methodValues) !!},
                            backgroundColor: ["#0d6efd", "#0dcaf0", "#ffc107", "#6c757d", "#6610f2", "#fd7e14"],
                            borderWidth: 2,
                            borderColor: "#ffffff"
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: "bottom" }
                        }
                    }
                });
            }

            var ctxBlock = document.getElementById("blockWiseChart");
            if (ctxBlock) {
                new Chart(ctxBlock.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: {!! json_encode($blockNames) !!},
                        datasets: [
                            {
                                label: "Collected Amount",
                                data: {!! json_encode($blockPaidData) !!},
                                backgroundColor: "#198754",
                                borderRadius: 4
                            },
                            {
                                label: "Pending Dues",
                                data: {!! json_encode($blockPendingData) !!},
                                backgroundColor: "#dc3545",
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        },
                        plugins: {
                            legend: { position: "top" }
                        }
                    }
                });
            }

            var ctxExpCat = document.getElementById("monthlyExpenseCategoryChart");
            if (ctxExpCat) {
                new Chart(ctxExpCat.getContext("2d"), {
                    type: "doughnut",
                    data: {
                        labels: {!! json_encode($expCatLabels) !!},
                        datasets: [{
                            data: {!! json_encode($expCatValues) !!},
                            backgroundColor: ["#dc3545", "#ffc107", "#0d6efd", "#0dcaf0", "#6610f2", "#fd7e14", "#20c997", "#6c757d"],
                            borderWidth: 2,
                            borderColor: "#ffffff"
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: "bottom" } }
                    }
                });
            }

            var ctxExpBar = document.getElementById("monthlyExpenseBarChart");
            if (ctxExpBar) {
                new Chart(ctxExpBar.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: {!! json_encode($expCatLabels) !!},
                        datasets: [{
                            label: "Spent Amount",
                            data: {!! json_encode($expCatValues) !!},
                            backgroundColor: "#dc3545",
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            var ctxRevExp = document.getElementById("revenueVsExpenseChart");
            if (ctxRevExp) {
                new Chart(ctxRevExp.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: ["{{ $selectedMonth }} {{ $selectedYear }}"],
                        datasets: [
                            {
                                label: "Collected Revenue",
                                data: [{{ round($totalPaid, 2) }}],
                                backgroundColor: "#198754",
                                borderRadius: 6
                            },
                            {
                                label: "Total Expenses",
                                data: [{{ round($totalExpense, 2) }}],
                                backgroundColor: "#dc3545",
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { position: "top" } }
                    }
                });
            }
        @else

            var ctxYearlyTrend = document.getElementById("yearlyTrendChart");
            if (ctxYearlyTrend) {
                new Chart(ctxYearlyTrend.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: {!! json_encode($yearlyMonths) !!},
                        datasets: [
                            {
                                label: "Expected Amount",
                                data: {!! json_encode($yearlyExpectedData) !!},
                                backgroundColor: "rgba(13, 202, 240, 0.6)",
                                borderColor: "#0dcaf0",
                                borderWidth: 1,
                                borderRadius: 4
                            },
                            {
                                label: "Collected Amount",
                                data: {!! json_encode($yearlyPaidData) !!},
                                backgroundColor: "#198754",
                                borderRadius: 4
                            },
                            {
                                label: "Pending Amount",
                                data: {!! json_encode($yearlyPendingData) !!},
                                backgroundColor: "#dc3545",
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        },
                        plugins: {
                            legend: { position: "top" }
                        }
                    }
                });
            }

            var ctxYearlyEff = document.getElementById("yearlyEfficiencyChart");
            if (ctxYearlyEff) {
                new Chart(ctxYearlyEff.getContext("2d"), {
                    type: "doughnut",
                    data: {
                        labels: ["Collected Amount", "Pending Dues"],
                        datasets: [{
                            data: [{{ round($yearlyPaid, 2) }}, {{ round($yearlyPending, 2) }}],
                            backgroundColor: ["#198754", "#dc3545"],
                            borderWidth: 2,
                            borderColor: "#ffffff"
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: "bottom" }
                        }
                    }
                });
            }

            var ctxYearlyExpTrend = document.getElementById("yearlyExpenseTrendChart");
            if (ctxYearlyExpTrend) {
                new Chart(ctxYearlyExpTrend.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: {!! json_encode($yearlyExpMonths) !!},
                        datasets: [{
                            label: "Total Expense",
                            data: {!! json_encode($yearlyExpData) !!},
                            backgroundColor: "#dc3545",
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { position: "top" } }
                    }
                });
            }

            var ctxYearlyExpCat = document.getElementById("yearlyExpenseCategoryChart");
            if (ctxYearlyExpCat) {
                new Chart(ctxYearlyExpCat.getContext("2d"), {
                    type: "doughnut",
                    data: {
                        labels: {!! json_encode($expCatLabels) !!},
                        datasets: [{
                            data: {!! json_encode($expCatValues) !!},
                            backgroundColor: ["#dc3545", "#ffc107", "#0d6efd", "#0dcaf0", "#6610f2", "#fd7e14", "#20c997", "#6c757d"],
                            borderWidth: 2,
                            borderColor: "#ffffff"
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: "bottom" } }
                    }
                });
            }

            var ctxRevExp = document.getElementById("revenueVsExpenseChart");
            if (ctxRevExp) {
                new Chart(ctxRevExp.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: {!! json_encode($yearlyMonths) !!},
                        datasets: [
                            {
                                label: "Collected Revenue",
                                data: {!! json_encode($yearlyPaidData) !!},
                                backgroundColor: "#198754",
                                borderRadius: 4
                            },
                            {
                                label: "Total Expenses",
                                data: {!! json_encode($yearlyExpData) !!},
                                backgroundColor: "#dc3545",
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { position: "top" } }
                    }
                });
            }
        @endif

        const activeTabInput = document.getElementById('activeTabInput');

        // Restore saved active tab on page load
        const savedTab = activeTabInput && activeTabInput.value ? activeTabInput.value : localStorage.getItem('smp_active_report_tab');
        if (savedTab && savedTab !== '#main-maintenance') {
            const targetButton = document.querySelector(`button[data-coreui-target="${savedTab}"]`);
            if (targetButton) {
                setTimeout(() => targetButton.click(), 50);
            }
        }

        // On tab click, save tab state and resize all charts
        document.querySelectorAll('button[data-coreui-toggle="tab"]').forEach(tab => {
            tab.addEventListener('click', function () {
                const target = this.getAttribute('data-coreui-target');
                if (activeTabInput && target) {
                    activeTabInput.value = target;
                }
                if (target && target.startsWith('#main-')) {
                    localStorage.setItem('smp_active_report_tab', target);
                }
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                    for (let id in Chart.instances) {
                        if (Chart.instances[id]) {
                            Chart.instances[id].resize();
                        }
                    }
                }, 150);
            });

            tab.addEventListener('shown.coreui.tab', function () {
                window.dispatchEvent(new Event('resize'));
                for (let id in Chart.instances) {
                    if (Chart.instances[id]) {
                        Chart.instances[id].resize();
                    }
                }
            });
        });
    });
</script>

@endpush
</x-user-page>
