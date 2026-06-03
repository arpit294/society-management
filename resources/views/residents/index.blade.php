<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Residents Management</h4>
        <button type="button" class="btn btn-primary" id="btn-add-resident"
            data-url="{{ route('residents.create') }}" data-title="Add New Resident">
            <i class="fa-solid fa-plus me-2"></i>Add Resident
        </button>
    </div>

    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="residents-filter-block">Filter by Block</label>
                <select id="residents-filter-block" class="form-select" style="max-width: 320px;">
                    <option value="">All Blocks</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->block_name }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-col d-none" id="residents-filter-reset-col" style="min-width: 200px;">
                <button type="button" id="residents-filter-reset" class="btn btn-outline-secondary w-100">
                    Reset filters
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0 p-lg-3">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    <!-- Resident Modal -->
    <div class="modal fade" id="resident-modal" tabindex="-1" aria-labelledby="residentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="resident-modal-content">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
