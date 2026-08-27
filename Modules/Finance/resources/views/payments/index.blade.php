<x-user-page>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Payment Receipts & Collections</h2>
            <p class="text-muted small mb-0">Record inward payments, print official money receipts, and track bank deposits</p>
        </div>
        <div>
            <a href="{{ route('finance.invoices.index') }}" class="btn btn-primary btn-sm px-3">
                <i class="fa-solid fa-file-invoice-dollar me-1"></i> Collect on Invoices
            </a>
        </div>
    </div>

    <!-- DataTable Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                {!! $dataTable->table(['class' => 'table table-hover align-middle w-100']) !!}
            </div>
        </div>
    </div>
</div>

@push('scripts')
{!! $dataTable->scripts() !!}
@endpush
</x-user-page>
