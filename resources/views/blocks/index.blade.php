<x-user-page>


    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <div class="row mb-4">
        <div class="col-sm-6 col-md-4 col-xl mb-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $totalFlats }} Flats</div>
                        <div>Total Capacity</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4 col-xl mb-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">{{ $totalActualFlats }} Flats</div>
                        <div>Total Created</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        @php
            $bgColors = ['bg-primary', 'bg-info', 'bg-warning', 'bg-danger', 'bg-success'];
        @endphp
        @foreach($blocks as $block)
            <div class="col-sm-6 col-md-4 col-xl mb-3">
                <div class="card text-white {{ $bgColors[$loop->index % count($bgColors)] }} h-100">
                    <div class="card-body pb-3 d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-semibold">{{ $block->flats_count }}/{{ $block->total_flats }} Flats</div>
                            <div>Block {{ $block->block_name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Block Management</h4>

        <button type="button" class="btn btn-primary" id="btn-add-block" data-url="{{ route('blocks.create') }}"
            data-title="Add Block">Add Block</button>
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
