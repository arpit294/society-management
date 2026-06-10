<x-user-page>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Payments</h4>
                <div>
                    <a href="{{ route('prepayments.create') }}" class="btn btn-secondary me-2">
                        <i class="fa-solid fa-plus me-1"></i> Add Prepayment
                    </a>
                    <button type="button" class="btn btn-primary" id="btn-add-maintenance-bill"
                        data-url="{{ route('maintenance-bills.create') }}" data-title="Generate Bill">
                        <i class="fa-solid fa-plus me-1"></i> Generate Bill
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-4" id="paymentsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bills-tab" data-coreui-toggle="tab" data-coreui-target="#bills" type="button" role="tab" aria-controls="bills" aria-selected="true">Due Bills</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prepayments-tab" data-coreui-toggle="tab" data-coreui-target="#prepayments" type="button" role="tab" aria-controls="prepayments" aria-selected="false">Prepayments (Unused)</button>
                    </li>
                </ul>
                <div class="tab-content" id="paymentsTabContent">
                    <div class="tab-pane fade show active" id="bills" role="tabpanel" aria-labelledby="bills-tab">
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
                                <div class="filter-col" style="min-width: 220px;">
                                    <label class="form-label mb-1" for="maintenance-bills-filter-status">Filter by Status</label>
                                    <select id="maintenance-bills-filter-status" class="form-select" style="max-width: 320px;">
                                        <option value="">All Status</option>
                                        <option value="published">Published</option>
                                        <option value="draft">Draft</option>
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
                    
                    <div class="tab-pane fade" id="prepayments" role="tabpanel" aria-labelledby="prepayments-tab">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>Resident</th>
                                        <th>Flat</th>
                                        <th>Period</th>
                                        <th>Usage</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($prepayments as $prepayment)
                                        <tr>
                                            <td>{{ $prepayment->user->name ?? 'N/A' }}</td>
                                            <td>{{ $prepayment->flat->block->block_name ?? '' }} - {{ $prepayment->flat->flat_no ?? '' }}</td>
                                            <td>
                                                {{ $prepayment->month }} {{ $prepayment->year }}
                                                @if($prepayment->months > 1 && $prepayment->end_month)
                                                    <br><small class="text-muted">to {{ $prepayment->end_month }} {{ $prepayment->end_year }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $prepayment->months_used }} / {{ $prepayment->months }} used</td>
                                            <td>{{ number_format($prepayment->amount_paid, 2) }}</td>
                                            <td><span class="badge bg-success">{{ $prepayment->status }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No unused prepayments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
