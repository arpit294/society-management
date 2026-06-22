<x-user-page>

<!-- Summary Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Collected -->
    <div class="col-md-4">
        <div class="card dash-card card-revenue h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-3 fw-bold">₹{{ number_format($totalCollected, 2) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">Total Collected</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Cash Collected -->
    <div class="col-md-4">
        <div class="card dash-card card-flats h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-3 fw-bold">₹{{ number_format($cashCollected, 2) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">Cash Collections</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- UPI Collected -->
    <div class="col-md-4">
        <div class="card dash-card card-residents h-100 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-3 fw-bold">₹{{ number_format($upiCollected, 2) }}</div>
                    <div class="text-uppercase fw-semibold small opacity-75">UPI Collections</div>
                </div>
                <div class="fs-1">
                    <i class="fas fa-qrcode"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                <h5 class="card-title mb-0 fw-bold">Collection Trends ({{ date('Y') }})</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentsChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for chart -->
<div id="payments-chart-data" 
     data-months="{{ json_encode($months) }}" 
     data-revenue="{{ json_encode($chartDataRevenue) }}"
     class="d-none"></div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Payments</h4>
                <div>
                    @can('maintenance_bill_create')
                    <button type="button" data-url="{{ route('maintenance-bills.create') }}" id="btn-record-payment" class="btn btn-primary me-2">
                        <i class="fa-solid fa-plus me-1"></i> Record Payment
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">


                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
                        <div class="filter-col" style="min-width: 220px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-block">Filter by Block</label>
                            <select id="maintenance-bills-filter-block" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Blocks</option>
                                @foreach($blocks as $block)
                                    <option value="{{ $block->block_name }}">{{ $block->block_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 280px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-resident">Filter by Resident</label>
                            <select id="maintenance-bills-filter-resident" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Residents</option>
                                @foreach($residents as $resident)
                                    <option value="{{ $resident->user->name ?? '' }}">
                                        {{ $resident->user->name ?? 'Unknown' }} ({{ $resident->flat->block->block_name ?? '' }} - {{ $resident->flat->flat_no ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 150px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-year">Filter by Year</label>
                            <select id="maintenance-bills-filter-year" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-col" style="min-width: 220px;">
                            <label class="form-label mb-1" for="maintenance-bills-filter-method">Filter by Method</label>
                            <select id="maintenance-bills-filter-method" class="form-select select2-filter" style="width: 100%;">
                                <option value="">All Methods</option>
                                <option value="cash">CASH</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>
                        <div class="filter-col d-none" id="maintenance-bills-filter-reset-col" style="min-width: 200px;">
                            <button type="button" id="maintenance-bills-filter-reset" class="btn btn-outline-secondary w-100">
                                Reset filter
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover w-100', 'id' => 'maintenance-bills-table']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Bill Modal -->
<div class="modal fade" id="maintenance-bill-modal" tabindex="-1" aria-labelledby="maintenanceBillModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="maintenance-bill-modal-content">
            <!-- Modal Content will be loaded via AJAX -->
        </div>
    </div>
</div>

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
</x-user-page>
