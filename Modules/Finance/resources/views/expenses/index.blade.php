<x-user-page>
<div class="row g-4 mb-4">
    <!-- Total Expenses Card -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-hero-card kpi-theme-rose h-100 border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon-pedestal">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <span class="kpi-status-pill">
                        <span class="kpi-status-dot"></span> Expenses
                    </span>
                </div>
                <div class="mt-2">
                    <div class="kpi-label mb-1">Total Expenses</div>
                    <div class="kpi-number" style="font-size: 1.85rem;">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalExpenses) }}</div>
                </div>
                <div class="kpi-glow-orb"></div>
            </div>
        </div>
    </div>
    
    <!-- This Month Expenses Card -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-hero-card kpi-theme-cyan h-100 border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon-pedestal">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="kpi-status-pill">
                        <span class="kpi-status-dot"></span> Monthly
                    </span>
                </div>
                <div class="mt-2">
                    <div class="kpi-label mb-1">This Month</div>
                    <div class="kpi-number" style="font-size: 1.85rem;">{{ \App\Helpers\CurrencyHelper::formatCurrency($thisMonthExpenses) }}</div>
                </div>
                <div class="kpi-glow-orb"></div>
            </div>
        </div>
    </div>
    
    <!-- Total Invoices Card -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-hero-card kpi-theme-indigo h-100 border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon-pedestal">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span class="kpi-status-pill">
                        <span class="kpi-status-dot"></span> Recorded
                    </span>
                </div>
                <div class="mt-2">
                    <div class="kpi-label mb-1">Total Invoices</div>
                    <div class="kpi-number counter-animate" data-target="{{ $totalInvoices }}">0</div>
                </div>
                <div class="kpi-glow-orb"></div>
            </div>
        </div>
    </div>
    
    <!-- Maintenance Income Card -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-hero-card kpi-theme-emerald h-100 border-0">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="kpi-icon-pedestal">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="kpi-status-pill">
                        <span class="kpi-status-dot"></span> Income
                    </span>
                </div>
                <div class="mt-2">
                    <div class="kpi-label mb-1">Maintenance Income</div>
                    <div class="kpi-number" style="font-size: 1.85rem;">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalMaintenanceIncome) }}</div>
                </div>
                <div class="kpi-glow-orb"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Expenses</h4>
                @can('expense_create')
                <button type="button" class="btn btn-primary" id="btn-add-expense"
                    data-url="{{ route('expenses.create') }}" data-title="Add Expense">
                    <i class="fa-solid fa-plus me-1"></i> Add Expense
                </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
                        <div class="filter-col" style="min-width: 220px;">
                            <label class="form-label mb-1" for="expenses-filter-category">Filter by Category</label>
                            <select id="expenses-filter-category" class="form-select select2-filter" style="max-width: 320px;">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->title }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 220px;">
                            <label class="form-label mb-1" for="expenses-filter-user">Filter by User</label>
                            <select id="expenses-filter-user" class="form-select select2-filter" style="max-width: 320px;">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 200px;">
                            <label class="form-label mb-1" for="expenses-filter-month">Filter by Month</label>
                            <input type="month" id="expenses-filter-month" class="form-control" style="max-width: 320px;">
                        </div>
                        <div class="filter-col d-none" id="expenses-filter-reset-col" style="min-width: 200px;">
                            <button type="button" id="expenses-filter-reset" class="btn btn-outline-secondary w-100">
                                Reset filter
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover w-100', 'id' => 'expenses-table']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expense Modal -->
<div class="modal fade" id="expense-modal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="expense-modal-content">
            <!-- Modal Content will be loaded via AJAX -->
        </div>
    </div>
</div>

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
</x-user-page>
