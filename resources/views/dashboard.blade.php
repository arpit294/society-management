<x-layout>

    <x-sidebar />
    <div class="wrapper d-flex flex-column min-vh-100">
        <x-header />
        <div class="body flex-grow-1">

            <div class="container-lg px-4">
                
                <!-- TOP CARDS ROW -->
                <div class="row g-4 mb-4">
                    <!-- Flats Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-hero-card kpi-theme-indigo h-100 border-0">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <!-- Top Row: Icon Pedestal & Live Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="kpi-icon-pedestal">
                                        <i class="fas {{ \App\Models\Setting::unitIconClass() }}"></i>
                                    </div>
                                    <span class="kpi-status-pill">
                                        <span class="kpi-status-dot"></span> Active
                                    </span>
                                </div>
                                <!-- Bottom Row: Label & Value -->
                                <div class="mt-2">
                                    <div class="kpi-label mb-1">Total {{ \App\Models\Setting::label('unit_plural', 'Flats') }}</div>
                                    <div class="kpi-number counter-animate" data-target="{{ $totalFlats }}">0</div>
                                </div>
                                <!-- Decorative Glow Orb -->
                                <div class="kpi-glow-orb"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Residents Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-hero-card kpi-theme-cyan h-100 border-0">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <!-- Top Row: Icon Pedestal & Live Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="kpi-icon-pedestal">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <span class="kpi-status-pill">
                                        <span class="kpi-status-dot"></span> Active
                                    </span>
                                </div>
                                <!-- Bottom Row: Label & Value -->
                                <div class="mt-2">
                                    <div class="kpi-label mb-1">Total {{ \App\Models\Setting::label('resident', 'Resident') }}s</div>
                                    <div class="kpi-number counter-animate" data-target="{{ $totalResidents }}">0</div>
                                </div>
                                <!-- Decorative Glow Orb -->
                                <div class="kpi-glow-orb"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Complaints Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-hero-card kpi-theme-rose h-100 border-0">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <!-- Top Row: Icon Pedestal & Live Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="kpi-icon-pedestal">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <span class="kpi-status-pill">
                                        <span class="kpi-status-dot"></span> Monitored
                                    </span>
                                </div>
                                <!-- Bottom Row: Label & Value -->
                                <div class="mt-2">
                                    <div class="kpi-label mb-1">{{ __('Total Complaints') }}</div>
                                    <div class="kpi-number counter-animate" data-target="{{ $totalComplaints }}">0</div>
                                </div>
                                <!-- Decorative Glow Orb -->
                                <div class="kpi-glow-orb"></div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($isFinanceActive))
                    <!-- Available Fund Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-hero-card kpi-theme-emerald h-100 border-0">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <!-- Top Row: Icon Pedestal & Live Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="kpi-icon-pedestal">
                                        <i class="fas {{ \App\Helpers\CurrencyHelper::getCurrencyIconClass() }}"></i>
                                    </div>
                                    <span class="kpi-status-pill">
                                        <span class="kpi-status-dot"></span> Secure
                                    </span>
                                </div>
                                <!-- Bottom Row: Label & Value -->
                                <div class="mt-2">
                                    <div class="kpi-label mb-1">{{ __('Available Fund') }}</div>
                                    <div class="kpi-number">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}<span class="counter-animate" data-target="{{ $totalAvailableFund }}">0</span></div>
                                </div>
                                <!-- Decorative Glow Orb -->
                                <div class="kpi-glow-orb"></div>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- PRO Teaser: Available Fund Card -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-hero-card kpi-theme-emerald h-100 border-0 js-premium-feature" data-feature="Society Financial Accounting & Fund Balance" style="cursor: pointer;">
                            <div class="card-body p-4 d-flex flex-column justify-content-between position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="kpi-icon-pedestal">
                                        <i class="fas fa-wallet text-warning"></i>
                                    </div>
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fa-solid fa-lock me-1"></i>PRO</span>
                                </div>
                                <div class="mt-2">
                                    <div class="kpi-label mb-1">{{ __('Financial Balance') }}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-4 fw-bold text-body opacity-75">••••••••</span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 small">Unlock</span>
                                    </div>
                                </div>
                                <div class="kpi-glow-orb"></div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- BLOCKS SUMMARY SECTION -->
                @if(isset($blocks) && $blocks->count() > 0)
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card glass-card shadow-sm border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas {{ \App\Models\Setting::blockIconClass() }} text-primary me-2"></i> {{ \App\Models\Setting::label('block', 'Block') }} Occupancy Details</h5>
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $blocks->count() }} {{ \App\Models\Setting::label('block', 'Blocks') }}</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 bg-transparent">
                                        <thead class="bg-transparent border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                                            <tr>
                                                <th class="py-2 text-muted small fw-bold">{{ strtoupper(\App\Models\Setting::label('block', 'BLOCK')) }} NAME</th>
                                                <th class="py-2 text-muted small fw-bold text-center">OCCUPIED {{ strtoupper(\App\Models\Setting::label('unit_plural', 'FLATS')) }}</th>
                                                <th class="py-2 text-muted small fw-bold text-center">TOTAL {{ strtoupper(\App\Models\Setting::label('unit_plural', 'FLATS')) }}</th>
                                                <th class="py-2 text-muted small fw-bold text-end">OCCUPANCY RATE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($blocks as $block)
                                                @php
                                                    $occupancyRate = $block->total_flats > 0 ? round(($block->occupied_flats_count / $block->total_flats) * 100) : 0;
                                                    $progressColor = $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger');
                                                @endphp
                                                <tr class="border-bottom" style="border-color: rgba(255, 255, 255, 0.05) !important;">
                                                    <td class="py-3 fw-semibold text-body">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="avatar avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                                <i class="fas {{ \App\Models\Setting::blockIconClass() }}"></i>
                                                            </div>
                                                            <div>
                                                                {{ \App\Models\Setting::label('block', 'Block') }} {{ $block->block_name }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 text-center fw-bold text-primary">{{ $block->occupied_flats_count }}</td>
                                                    <td class="py-3 text-center fw-bold text-muted">{{ $block->total_flats }}</td>
                                                    <td class="py-3 text-end">
                                                        <div class="d-flex flex-column align-items-end">
                                                            <span class="fw-bold mb-1">{{ $occupancyRate }}%</span>
                                                            <div class="progress" style="height: 6px; width: 100px; background-color: rgba(255,255,255,0.1);">
                                                                <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" style="width: {{ $occupancyRate }}%;" aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($isFinanceActive))
                <!-- CHARTS ROW 1 -->
                <div class="row g-4 mb-4">
                    <!-- Main Chart: Revenue vs Expenses -->
                    <div class="col-lg-12">
                        <div class="card glass-card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h5 class="card-title mb-0 fw-bold">Revenue vs Expenses ({{ date('Y') }})</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="mainChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <!-- PRO Financial Analytics Teaser Row -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card glass-card shadow-sm border-0 p-4 js-premium-feature" data-feature="Society Revenue & Expense Financial Analytics" style="cursor: pointer; background: linear-gradient(135deg, rgba(30, 41, 59, 0.03) 0%, rgba(245, 158, 11, 0.05) 100%);">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-3 flex-shrink-0">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h5 class="fw-bold mb-0 text-body">Financial Analytics & Cashflow Charts</h5>
                                            <span class="badge bg-warning text-dark fw-bold">PRO FEATURE</span>
                                        </div>
                                        <p class="text-muted small mb-0">Track monthly maintenance collections vs society expenditures, cash balances, and budget allocations with the Finance Module.</p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-warning px-4 py-2 fw-semibold text-dark rounded-pill flex-shrink-0 shadow-sm js-premium-feature-btn" data-feature="Financial Accounting & Analytics">
                                    <i class="fa-solid fa-crown me-1"></i> Unlock Analytics
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- CHARTS ROW 2 -->
                <div class="row g-4 mb-4">
                    @if(!empty($isFinanceActive))
                    <!-- Expense Breakdown Chart -->
                    <div class="col-lg-6">
                        <div class="card glass-card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h5 class="card-title mb-0 fw-bold">Expense Breakdown ({{ date('Y') }})</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                                <canvas id="expenseBreakdownChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Occupancy Rates Chart -->
                    <div class="{{ !empty($isFinanceActive) ? 'col-lg-6' : 'col-12' }}">
                        <div class="card glass-card h-100 shadow-sm border-0">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h5 class="card-title mb-0 fw-bold">Occupancy Rates</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                                <canvas id="occupancyChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($isFinanceActive))
                <!-- LIVE CASHFLOW & NET-WORTH LEDGER ROW -->
                <div class="row g-4 mb-4">
                    <!-- Left Col: This Month's Cashflow Pulse -->
                    <div class="col-xl-5 col-lg-12">
                        <div class="card glass-card shadow-sm border-0 p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-scale-balanced text-{{ $cashflowColor }}"></i>
                                            Monthly Cashflow Pulse
                                        </h5>
                                        <p class="text-muted mb-0 small">Real-time financial reconciliation for {{ date('F Y') }}</p>
                                    </div>
                                    <span class="badge bg-{{ $cashflowColor }} bg-opacity-10 text-{{ $cashflowColor }} border border-{{ $cashflowColor }} border-opacity-25 rounded-pill px-3 py-2 small fw-bold">
                                        {{ $cashflowStatus }}
                                    </span>
                                </div>

                                <!-- Net Cashflow Big Display -->
                                <div class="text-center py-4 mb-4 rounded-4 border" style="background: rgba(255, 255, 255, 0.02); border-color: rgba(255, 255, 255, 0.08) !important;">
                                    <span class="text-muted small text-uppercase fw-bold d-block mb-1" style="letter-spacing: 1px;">Net Monthly Cashflow</span>
                                    <h2 class="display-5 fw-bolder mb-2 text-{{ $cashflowColor }}">
                                        {{ $thisMonthNet < 0 ? '-' : '+' }} {{ \App\Helpers\CurrencyHelper::formatCurrency(abs($thisMonthNet)) }}
                                    </h2>
                                    <span class="badge bg-{{ $cashflowColor }} bg-opacity-10 text-{{ $cashflowColor }} px-3 py-1 rounded-pill small">
                                        <i class="fa-solid {{ $thisMonthNet >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} me-1"></i>
                                        {{ $thisMonthNet >= 0 ? 'Positive Operating Surplus' : 'Operating Deficit This Month' }}
                                    </span>
                                </div>

                                <!-- Revenue vs Expense Breakdown -->
                                <div class="d-flex flex-column gap-3 mb-3">
                                    <!-- This Month Inflow -->
                                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2) !important;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-md rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                                <i class="fa-solid fa-arrow-down-long"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-body" style="font-size: 0.9rem;">Total Inflow (Revenue)</h6>
                                                <span class="text-muted small" style="font-size: 0.75rem;">Maintenance, Transfer & Penalty Fees</span>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-success fs-6">+ {{ \App\Helpers\CurrencyHelper::formatCurrency($thisMonthRevenue) }}</span>
                                    </div>

                                    <!-- This Month Penalty Income -->
                                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2) !important;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-md rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-body" style="font-size: 0.9rem;">Penalty Income (Late Fees)</h6>
                                                <span class="text-muted small" style="font-size: 0.75rem;">Collected from overdue maintenance bills</span>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-warning fs-6" title="Included in Total Inflow">Included: {{ \App\Helpers\CurrencyHelper::formatCurrency($thisMonthPenalty ?? 0) }}</span>
                                    </div>

                                    <!-- This Month Transfer Income -->
                                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: rgba(59, 130, 246, 0.05); border-color: rgba(59, 130, 246, 0.2) !important;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-md rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                                <i class="fa-solid fa-right-left"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-body" style="font-size: 0.9rem;">Transfer Fee Income</h6>
                                                <span class="text-muted small" style="font-size: 0.75rem;">Collected from flat ownership transfers</span>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-info fs-6" title="Included in Total Inflow">Included: {{ \App\Helpers\CurrencyHelper::formatCurrency($thisMonthTransfer ?? 0) }}</span>
                                    </div>

                                    <!-- This Month Outflow -->
                                    <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2) !important;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-md rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                                <i class="fa-solid fa-arrow-up-long"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-body" style="font-size: 0.9rem;">Total Outflow (Expenses)</h6>
                                                <span class="text-muted small" style="font-size: 0.75rem;">Repairs, Utility & AMC Bills</span>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-danger fs-6">- {{ \App\Helpers\CurrencyHelper::formatCurrency($thisMonthExpense) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Society Reserve Fund Pill -->
                            <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 mt-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-vault text-primary fs-5 flex-shrink-0"></i>
                                    <span class="small fw-semibold text-body">Net Society Reserve Fund:</span>
                                </div>
                                <span class="fw-bolder text-primary fs-6">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalAvailableFund) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Col: Combined Financial Ledger Stream -->
                    <div class="col-xl-7 col-lg-12">
                        <div class="card glass-card shadow-sm border-0 p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-receipt text-primary"></i>
                                            Live Financial Ledger
                                        </h5>
                                        <p class="text-muted mb-0 small">Chronological stream merging incoming receipts & outgoing expenditures</p>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 small fw-semibold">
                                        <i class="fa-solid fa-stream me-1"></i> Real-Time Stream
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 bg-transparent">
                                        <thead class="bg-transparent border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                                            <tr>
                                                <th class="py-2 text-muted small fw-bold">TRANSACTION DETAILS</th>
                                                <th class="py-2 text-muted small fw-bold">CATEGORY</th>
                                                <th class="py-2 text-muted small fw-bold text-end">AMOUNT</th>
                                                <th class="py-2 text-muted small fw-bold text-end">TIME</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($ledgerTransactions as $tx)
                                                <tr class="border-bottom" style="border-color: rgba(255, 255, 255, 0.05) !important;">
                                                    <td class="py-3">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="avatar avatar-sm rounded-circle {{ $tx->type === 'income' ? ($tx->category === 'Penalty Fee' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success') : 'bg-danger bg-opacity-10 text-danger' }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                                <i class="fa-solid {{ $tx->type === 'income' ? ($tx->category === 'Penalty Fee' ? 'fa-circle-exclamation' : 'fa-arrow-down-long') : 'fa-arrow-up-long' }}"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-semibold text-body" style="font-size: 0.9rem;">{{ $tx->title }}</h6>
                                                                <span class="text-muted small" style="font-size: 0.75rem;">
                                                                    <i class="fa-solid {{ $tx->type === 'income' ? ($tx->category === 'Penalty Fee' ? 'fa-circle-plus text-warning' : 'fa-circle-plus text-success') : 'fa-circle-minus text-danger' }} me-1"></i>
                                                                    {{ ucfirst($tx->type) }} Record
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="badge {{ $tx->type === 'income' ? ($tx->category === 'Penalty Fee' ? 'bg-warning bg-opacity-10 text-warning border border-warning' : 'bg-success bg-opacity-10 text-success border border-success') : 'bg-secondary bg-opacity-10 text-secondary border border-secondary' }} border-opacity-25 px-2 py-1 small fw-medium">
                                                            {{ $tx->category }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-end">
                                                        <span class="fw-bold {{ $tx->type === 'income' ? ($tx->category === 'Penalty Fee' ? 'text-warning' : 'text-success') : 'text-danger' }}">
                                                            {{ $tx->type === 'income' ? '+' : '-' }} {{ \App\Helpers\CurrencyHelper::formatCurrency($tx->amount) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-end">
                                                        <span class="text-muted small fw-medium" style="font-size: 0.8rem;">{{ $tx->time }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-5">
                                                        <div class="py-3">
                                                            <div class="avatar avatar-xl rounded-circle bg-secondary bg-opacity-10 text-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                                                                <i class="fa-solid fa-receipt"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-1 text-body">No Financial Transactions Recorded</h6>
                                                            <p class="text-muted small mb-0">Incoming fee receipts and outgoing expenses will appear here automatically.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-3 mt-3 border-top" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                                <span class="text-muted small"><i class="fa-solid fa-lock text-primary me-1"></i> All transactions are cryptographically audited & reconciled</span>
                                <div class="d-flex gap-3">
                                    @if(\Illuminate\Support\Facades\Route::has('maintenance-bills.index'))
                                    <a href="{{ route('maintenance-bills.index') }}" class="btn btn-sm btn-link text-success fw-semibold text-decoration-none p-0">
                                        Inflow <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                    @endif
                                    @if(\Illuminate\Support\Facades\Route::has('expenses.index'))
                                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-link text-danger fw-semibold text-decoration-none p-0">
                                        Outflow <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
        <x-footer />
    </div>

    <!-- Pass Chart Data to script.js -->
    <div id="dashboard-chart-data" style="display:none" 
         data-months='{{ json_encode($months) }}' 
         data-revenue='{{ json_encode($chartDataRevenue) }}' 
         data-expenses='{{ json_encode($chartDataExpenses) }}'
         data-status='{{ json_encode($billStatusData) }}'
         data-occupancy='{{ json_encode($occupancyData) }}'
         data-expense-labels='{{ json_encode($expenseBreakdownLabels) }}'
         data-expense-data='{{ json_encode($expenseBreakdownData) }}'
         data-currency="{{ \App\Helpers\CurrencyHelper::getCurrencyCode() }}"
         data-currency-symbol="{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}">
    </div>
</x-layout>
