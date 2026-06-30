<x-user-page>
<div class="row g-4 mb-4">
    <!-- Total Expenses Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card dash-card card-complaints h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-bold">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalExpenses) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">Total Expenses</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- This Month Expenses Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card dash-card card-residents h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-bold">{{ \App\Helpers\CurrencyHelper::formatCurrency($thisMonthExpenses) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">This Month</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Invoices Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card dash-card card-flats h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-bold">{{ $totalInvoices }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">Total Invoices</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Maintenance Income Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card dash-card card-revenue h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-bold">{{ \App\Helpers\CurrencyHelper::formatCurrency($totalMaintenanceIncome) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">Maintenance Income</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-chart-line"></i>
                </div>
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
