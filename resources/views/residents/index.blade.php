<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Residents Management</h4>
        <button type="button" class="btn btn-primary" id="btn-add-resident"
            data-url="{{ route('residents.create') }}" data-title="Add New Resident">
            <i class="fa-solid fa-plus me-2"></i>Add Resident
        </button>
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
