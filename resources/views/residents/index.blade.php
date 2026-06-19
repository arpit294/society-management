<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Residents Management</h4>
        @can('resident_create')
        <div>
            <button type="button" class="btn btn-outline-success me-2" data-coreui-toggle="modal" data-coreui-target="#import-resident-modal">
                <i class="fa-solid fa-file-import me-2"></i>Import Records
            </button>
            <button type="button" class="btn btn-primary" id="btn-add-resident"
                data-url="{{ route('residents.create') }}" data-title="Add New Resident">
                <i class="fa-solid fa-plus me-2"></i>Add Resident
            </button>
        </div>
        @endcan
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

    <!-- Import Resident Modal -->
    <div class="modal fade" id="import-resident-modal" tabindex="-1" aria-labelledby="importResidentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importResidentModalLabel">Import Residents</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('residents.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p>Upload an Excel (.xlsx) file to bulk import residents.</p>
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Select File</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                        </div>
                        <p class="small text-muted mb-0">Need the correct format? <a href="{{ route('residents.import.template') }}" class="text-decoration-none"><i class="fa-solid fa-download me-1"></i>Download Template</a></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-file-import me-2"></i>Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
