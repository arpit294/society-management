<x-user-page>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Maintenance Bills</h4>
                <button type="button" class="btn btn-primary" id="btn-add-maintenance-bill"
                    data-url="{{ route('maintenance-bills.create') }}" data-title="Generate Bill">
                    <i class="fa-solid fa-plus me-1"></i> Generate Bill
                </button>
            </div>
            <div class="card-body">
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
