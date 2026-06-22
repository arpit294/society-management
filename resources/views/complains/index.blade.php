<x-user-page>


    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Complaints Management</h4>
        @can('complain_create')
        <button type="button" class="btn btn-primary" id="btn-add-complain" data-url="{{ route('complains.create') }}"
            data-title="Add Complaint">Add Complaint</button>
        @endcan
    </div>

    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="complains-filter-category">Filter by Category</label>
                <select id="complains-filter-category" class="form-select" style="max-width: 320px;">
                    <option value="">All Categories</option>
                    @foreach (['Maintenance Issues', 'Security Issues', 'Cleanliness & Housekeeping', 'Common Facilities', 'other'] as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-col d-none" id="complains-filter-reset-col" style="min-width: 200px;">
                <button type="button" id="complains-filter-reset" class="btn btn-outline-secondary w-100">
                    Reset filters
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>

    <div class="modal fade" id="complain-modal" tabindex="-1" aria-labelledby="complain-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="complain-modal-content"></div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
