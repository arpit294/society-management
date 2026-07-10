<x-user-page>


    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @endif

    <!-- TOP CAPACITY CARDS -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-md-6 col-xl-6">
            <div class="card kpi-hero-card kpi-theme-indigo h-100 border-0">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="kpi-icon-pedestal">
                            <i class="fas fa-building"></i>
                        </div>
                        <span class="kpi-status-pill">
                            <span class="kpi-status-dot"></span> Capacity
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="kpi-label mb-1">Total Capacity</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="kpi-number counter-animate" data-target="{{ $totalFlats }}">0</span>
                            <span class="fs-4 fw-bold text-light opacity-75">Flats</span>
                        </div>
                    </div>
                    <div class="kpi-glow-orb"></div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-6 col-xl-6">
            <div class="card kpi-hero-card kpi-theme-cyan h-100 border-0">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="kpi-icon-pedestal">
                            <i class="fas fa-key"></i>
                        </div>
                        <span class="kpi-status-pill">
                            <span class="kpi-status-dot"></span> Occupied
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="kpi-label mb-1">Total Occupied</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="kpi-number counter-animate" data-target="{{ $totalOccupiedFlats }}">0</span>
                            <span class="fs-4 fw-bold text-light opacity-75">Flats</span>
                        </div>
                    </div>
                    <div class="kpi-glow-orb"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- INDIVIDUAL BLOCK CARDS -->
    <div class="row g-4 mb-4">
        @php
            $themes = ['kpi-theme-indigo', 'kpi-theme-cyan', 'kpi-theme-rose', 'kpi-theme-emerald'];
        @endphp
        @foreach($blocks as $block)
            @php
                $theme = $themes[$loop->index % count($themes)];
            @endphp
            <div class="col-sm-6 col-md-4 col-xl-4">
                <div class="card kpi-hero-card {{ $theme }} h-100 border-0">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="kpi-icon-pedestal">
                                <i class="fas fa-city"></i>
                            </div>
                            <span class="kpi-status-pill">
                                <span class="kpi-status-dot"></span> Active
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="kpi-label mb-1">Block {{ $block->block_name }}</div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="kpi-number">{{ $block->occupied_flats_count }}</span>
                                <span class="fs-4 fw-bold text-muted">/{{ $block->total_flats }}</span>
                                <span class="fs-6 fw-semibold text-light opacity-75 ms-1">Flats</span>
                            </div>
                        </div>
                        <div class="kpi-glow-orb"></div>
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
