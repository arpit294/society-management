<x-user-page>


    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-md-4 col-xl">
            <div class="card dash-card card-flats h-100 shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold">{{ $totalFlats }} Flats</div>
                        <div class="text-uppercase fw-semibold small opacity-75">Total Capacity</div>
                    </div>
                    <div class="fs-1">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4 col-xl">
            <div class="card dash-card card-residents h-100 shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold">{{ $totalOccupiedFlats }} Flats</div>
                        <div class="text-uppercase fw-semibold small opacity-75">Total Occupied</div>
                    </div>
                    <div class="fs-1">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        @php
            $cardTypes = ['card-flats', 'card-residents', 'card-complaints', 'card-revenue'];
        @endphp
        @foreach($blocks as $block)
            <div class="col-sm-6 col-md-4 col-xl">
                <div class="card dash-card {{ $cardTypes[$loop->index % count($cardTypes)] }} h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-bold">{{ $block->occupied_flats_count }}/{{ $block->total_flats }} Flats</div>
                            <div class="text-uppercase fw-semibold small opacity-75">Block {{ $block->block_name }}</div>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-city"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Block Management</h4>

        @can('block_create')
        <button type="button" class="btn btn-primary" id="btn-add-block" data-url="{{ route('blocks.create') }}"
            data-title="Add Block">Add Block</button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>

    <div class="modal fade" id="block-modal" tabindex="-1" aria-labelledby="block-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="block-modal-content"></div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-user-page>
