<x-user-page>

    <!-- TOP CAPACITY CARDS -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-md-6 col-xl-6">
            <div class="card kpi-hero-card block-filter-card kpi-theme-indigo h-100 border-0" data-block-filter="" style="cursor: pointer;" title="Click to show all {{ strtolower(\App\Models\Setting::label('block', 'Blocks')) }}s">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="kpi-icon-pedestal">
                            <i class="fas {{ \App\Models\Setting::unitIconClass() }}"></i>
                        </div>
                        <span class="kpi-status-pill">
                            <span class="kpi-status-dot"></span> Capacity
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="kpi-label mb-1">Total {{ \App\Models\Setting::label('unit_plural', 'Flats') }} Capacity</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="kpi-number counter-animate" data-target="{{ $totalFlats }}">0</span>
                            <span class="fs-4 fw-bold text-light opacity-75">{{ \App\Models\Setting::label('unit_plural', 'Flats') }}</span>
                        </div>
                    </div>
                    <div class="kpi-glow-orb"></div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-6 col-xl-6">
            <div class="card kpi-hero-card block-filter-card kpi-theme-cyan h-100 border-0" data-block-filter="" style="cursor: pointer;" title="Click to show all {{ strtolower(\App\Models\Setting::label('block', 'Blocks')) }}s">
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
                        <div class="kpi-label mb-1">Total Occupied {{ \App\Models\Setting::label('unit_plural', 'Flats') }}</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="kpi-number counter-animate" data-target="{{ $totalOccupiedFlats }}">0</span>
                            <span class="fs-4 fw-bold text-light opacity-75">{{ \App\Models\Setting::label('unit_plural', 'Flats') }}</span>
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
                <div class="card kpi-hero-card block-filter-card {{ $theme }} h-100 border-0" data-block-filter="{{ $block->block_name }}" style="cursor: pointer;" title="Click to filter table by {{ \App\Models\Setting::label('block', 'Block') }} {{ $block->block_name }}">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="kpi-icon-pedestal">
                                <i class="fas {{ \App\Models\Setting::blockIconClass() }}"></i>
                            </div>
                            <span class="kpi-status-pill">
                                <span class="kpi-status-dot"></span> Active
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="kpi-label mb-1">{{ \App\Models\Setting::label('block', 'Block') }} {{ $block->block_name }}</div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="kpi-number">{{ $block->occupied_flats_count }}</span>
                                <span class="fs-4 fw-bold text-muted">/{{ $block->total_flats }}</span>
                                <span class="fs-6 fw-semibold text-light opacity-75 ms-1">{{ \App\Models\Setting::label('unit_plural', 'Flats') }}</span>
                            </div>
                        </div>
                        <div class="kpi-glow-orb"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ \App\Models\Setting::label('block', 'Block') }} Management</h4>

        @can('block_create')
        <button type="button" class="btn btn-primary" id="btn-add-block" data-url="{{ route('blocks.create') }}"
            data-title="Add {{ \App\Models\Setting::label('block', 'Block') }}">Add {{ \App\Models\Setting::label('block', 'Block') }}</button>
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterCards = document.querySelectorAll('.block-filter-card');
                filterCards.forEach(card => {
                    card.addEventListener('click', function() {
                        const filterValue = this.getAttribute('data-block-filter') || '';
                        
                        // Highlight active card
                        filterCards.forEach(c => c.style.boxShadow = '');
                        if (filterValue !== '') {
                            this.style.boxShadow = '0 0 0 3px rgba(99, 102, 241, 0.6)';
                        }
                        
                        // Filter DataTable if loaded
                        if (window.LaravelDataTables && window.LaravelDataTables['blocks-table']) {
                            window.LaravelDataTables['blocks-table'].search(filterValue).draw();
                        } else if ($.fn.DataTable && $('#blocks-table').length) {
                            $('#blocks-table').DataTable().search(filterValue).draw();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-user-page>
