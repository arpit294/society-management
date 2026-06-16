<x-user-page>


    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Flat Management</h4>

        <button type="button" class="btn btn-primary" id="btn-add-flat" data-url="{{ route('flats.create') }}"
            data-title="Add Flat">Add Flat</button>
    </div>

    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-start">
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="flats-filter-block">Filter by Block</label>
                <select id="flats-filter-block" class="form-select" style="max-width: 320px;">
                    <option value="">All Blocks</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->block_name }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="flats-filter-type">Filter by Flat Type</label>
                <select id="flats-filter-type" class="form-select" style="max-width: 320px;">
                    <option value="">All Flat Types</option>
                    <option value="1BHK">1BHK</option>
                    <option value="2BHK">2BHK</option>
                    <option value="3BHK">3BHK</option>
                </select>
            </div>
            <div class="filter-col" style="min-width: 220px;">
                <label class="form-label mb-1" for="flats-filter-status">Filter by Status</label>
                <select id="flats-filter-status" class="form-select" style="max-width: 320px;">
                    <option value="">All Status</option>
                    <option value="Empty">Empty</option>
                    <option value="Occupied">Occupied</option>
                </select>
            </div>
            <div class="filter-col d-none" id="flats-filter-reset-col" style="min-width: 200px;">
                <button type="button" id="flats-filter-reset" class="btn btn-outline-secondary w-100">
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

    <div class="modal fade" id="flat-modal" tabindex="-1" aria-labelledby="flat-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="flat-modal-content"></div>
        </div>
    </div>

    <div class="modal fade" id="flat-history-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="flat-history-modal-content"></div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
        <script>
            $(document).ready(function() {
                $(document).on('click', '.btn-history-flat', function() {
                    let url = $(this).data('url');
                    $('#flat-history-modal-content').html('<div class="p-5 text-center"><div class="spinner-border text-primary"></div><div class="mt-2 text-muted">Loading history...</div></div>');
                    $('#flat-history-modal').modal('show');
                    
                    $.get(url, function(data) {
                        $('#flat-history-modal-content').html(data);
                    }).fail(function() {
                        $('#flat-history-modal-content').html('<div class="p-4 text-center text-danger">Failed to load history. Please try again.</div>');
                    });
                });
            });
        </script>
    @endpush
</x-user-page>
