<x-user-page>
<div class="row mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">${{ number_format($totalExpenses, 2) }}</div>
                    <div>Total Expenses</div>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:40px;"></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">${{ number_format($thisMonthExpenses, 2) }}</div>
                    <div>This Month</div>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:40px;"></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card text-white bg-warning">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $totalInvoices }}</div>
                    <div>Total Invoices</div>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height:40px;"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Expenses</h4>
                <button type="button" class="btn btn-primary" id="btn-add-expense"
                    data-url="{{ route('expenses.create') }}" data-title="Add Expense">
                    <i class="fa-solid fa-plus me-1"></i> Add Expense
                </button>
            </div>
            <div class="card-body">
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
