<x-user-page>
    <style>
        .export-btn-pulse:hover {
            animation: pulse-animation 1s infinite;
        }
        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0 rgba(57, 158, 209, 0.5); }
            70% { box-shadow: 0 0 0 8px rgba(57, 158, 209, 0); }
            100% { box-shadow: 0 0 0 0 rgba(57, 158, 209, 0); }
        }
        .border-dashed {
            border-style: dashed !important;
        }
        .bg-gradient {
            background-image: linear-gradient(135deg, var(--cui-primary) 0%, #2a8bf2 100%);
        }
        /* Resident Card Styles */
        .resident-card {
            background: var(--cui-card-bg, rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            border: 1px solid var(--cui-border-color, rgba(255, 255, 255, 0.5));
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            color: var(--cui-body-color);
        }
        .resident-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: var(--cui-primary);
        }
        .card-banner {
            height: 60px;
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        .card-banner.bg-owner { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-banner.bg-rental { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .resident-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--cui-card-bg, #fff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--cui-primary);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-top: -30px;
            border: 3px solid var(--cui-card-bg, #fff);
        }
        /* Grid Pagination Styling */
        #grid-pagination .dataTables_paginate {
            display: flex;
            justify-content: flex-end;
            margin: 0;
        }
        #grid-pagination .pagination {
            margin-bottom: 0;
        }
        #grid-pagination .paginate_button {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            margin-left: 0.25rem;
            color: var(--cui-body-color);
            background-color: var(--cui-card-bg, #fff);
            border: 1px solid var(--cui-border-color, #dee2e6);
            border-radius: 0.25rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        #grid-pagination .paginate_button:hover {
            background-color: var(--cui-tertiary-bg, #e9ecef);
            border-color: var(--cui-border-color, #dee2e6);
            color: var(--cui-primary);
        }
        #grid-pagination .paginate_button.current {
            z-index: 3;
            color: #fff;
            background-color: var(--cui-primary);
            border-color: var(--cui-primary);
        }
        #grid-pagination .paginate_button.disabled {
            color: var(--cui-secondary-color, #6c757d);
            pointer-events: none;
            background-color: var(--cui-card-bg, #fff);
            border-color: var(--cui-border-color, #dee2e6);
        }
        #grid-pagination span {
            display: inline-flex;
        }
        #grid-pagination-container {
            background-color: var(--cui-card-bg, #fff);
            border: 1px solid var(--cui-border-color, transparent);
        }
    </style>
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="mb-0">Residents Management</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- View Toggle -->
            <div class="btn-group shadow-sm me-2" role="group">
                <input type="radio" class="btn-check" name="view_mode" id="view_list" value="list" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="view_list"><i class="fa-solid fa-list"></i></label>
                
                <input type="radio" class="btn-check" name="view_mode" id="view_grid" value="grid" autocomplete="off">
                <label class="btn btn-outline-primary" for="view_grid"><i class="fa-solid fa-grip"></i></label>
            </div>
            
            @can('resident_view')
            <a href="{{ route('residents.export') }}" class="btn btn-outline-info export-btn-pulse" id="export-residents-btn">
                <i class="fa-solid fa-file-export me-2"></i>Export Records
            </a>
            @endcan
            
            @can('resident_create')
            <button type="button" class="btn btn-outline-success" data-coreui-toggle="modal" data-coreui-target="#import-resident-modal">
                <i class="fa-solid fa-file-import me-2"></i>Import Records
            </button>
            <button type="button" class="btn btn-primary bg-gradient shadow-sm border-0" id="btn-add-resident"
                data-url="{{ route('residents.create') }}" data-title="Add New Resident">
                <i class="fa-solid fa-plus me-2"></i>Add Resident
            </button>
            @endcan
        </div>
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

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" id="table-container">
        <div class="card-body p-0 p-lg-3">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    <!-- Grid View Container -->
    <div id="grid-container" class="d-none">
        <div class="row g-4" id="resident-grid-content">
            <!-- Cards will be dynamically injected here via JS -->
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4 p-3 rounded-4 shadow-sm" id="grid-pagination-container">
            <div id="grid-info" class="text-muted small"></div>
            <div id="grid-pagination"></div>
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
                <form action="{{ route('residents.import') }}" method="POST" enctype="multipart/form-data" id="import-resident-form">
                    @csrf
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="mb-0">Upload an Excel (.xlsx) file to bulk import.</p>
                            <a href="{{ route('residents.import.template') }}" class="btn btn-sm btn-light rounded-pill text-primary fw-semibold border shadow-sm">
                                <i class="fa-solid fa-download me-1"></i>Download Template
                            </a>
                        </div>
                        <div class="mb-3">
                            <div class="drag-drop-zone border border-2 border-dashed rounded p-4 text-center position-relative" id="drag-drop-zone" style="background-color: #f8f9fa; cursor: pointer; transition: all 0.3s ease;">
                                <input type="file" class="position-absolute w-100 h-100 top-0 start-0 opacity-0" id="excel_file" name="excel_file" accept=".xlsx, .xls" required style="cursor: pointer;">
                                <i class="fa-solid fa-file-excel text-success mb-2" style="font-size: 3rem;"></i>
                                <h6 class="mb-1 text-dark fw-bold" id="drag-drop-text">Drag & Drop your Excel file here</h6>
                                <p class="text-muted small mb-0" id="drag-drop-subtext">or click to browse</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary bg-gradient border-0" id="import-submit-btn">
                            <i class="fa-solid fa-file-import me-2"></i>Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
        <script type="module">
            document.addEventListener('DOMContentLoaded', function() {
                const blockFilter = document.getElementById('residents-filter-block');
                const exportBtn = document.getElementById('export-residents-btn');

                if (blockFilter && exportBtn) {
                    blockFilter.addEventListener('change', updateExportUrl);

                    function updateExportUrl() {
                        const url = new URL(exportBtn.href.split('?')[0]);
                        if (blockFilter.value) {
                            url.searchParams.set('block', blockFilter.value);
                        }
                        exportBtn.href = url.toString();
                    }
                }

                // Drag and drop zone UI
                const excelFile = document.getElementById('excel_file');
                const dragDropZone = document.getElementById('drag-drop-zone');
                const dragDropText = document.getElementById('drag-drop-text');
                const dragDropSubtext = document.getElementById('drag-drop-subtext');

                if (excelFile && dragDropZone) {
                    excelFile.addEventListener('change', function() {
                        if (this.files && this.files.length > 0) {
                            dragDropText.innerHTML = `<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> ${this.files[0].name}</span>`;
                            dragDropSubtext.innerHTML = 'Click to change file';
                            dragDropZone.style.backgroundColor = 'rgba(25, 135, 84, 0.05)';
                            dragDropZone.style.borderColor = '#198754';
                        } else {
                            dragDropText.innerHTML = 'Drag & Drop your Excel file here';
                            dragDropSubtext.innerHTML = 'or click to browse';
                            dragDropZone.style.backgroundColor = '#f8f9fa';
                            dragDropZone.style.borderColor = '#dee2e6';
                        }
                    });

                    dragDropZone.addEventListener('dragover', (e) => {
                        dragDropZone.style.backgroundColor = '#e9ecef';
                    });
                    dragDropZone.addEventListener('dragleave', (e) => {
                        if(!excelFile.files || !excelFile.files.length) {
                            dragDropZone.style.backgroundColor = '#f8f9fa';
                        }
                    });
                }

                // Loading state
                const importForm = document.getElementById('import-resident-form');
                const importSubmitBtn = document.getElementById('import-submit-btn');

                if (importForm && importSubmitBtn) {
                    importForm.addEventListener('submit', function() {
                        importSubmitBtn.disabled = true;
                        importSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Importing...';
                        importForm.submit();
                    });
                }
                // View Toggle Logic
                const viewListBtn = document.getElementById('view_list');
                const viewGridBtn = document.getElementById('view_grid');
                const tableContainer = document.getElementById('table-container');
                const gridContainer = document.getElementById('grid-container');
                const gridContent = document.getElementById('resident-grid-content');
                const gridPagination = document.getElementById('grid-pagination');
                const gridInfo = document.getElementById('grid-info');

                let currentView = 'list';

                function renderGrid() {
                    const dt = window.LaravelDataTables['residents-table'];
                    if(!dt) return;

                    const data = dt.rows({page:'current'}).data().toArray();
                    gridContent.innerHTML = '';

                    if(data.length === 0) {
                        gridContent.innerHTML = `
                            <div class="col-12 text-center py-5">
                                <div class="mb-3">
                                    <i class="fa-solid fa-folder-open text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                                </div>
                                <h5 class="text-muted">No residents found</h5>
                                <p class="text-muted small">Try adjusting your filters or importing new data.</p>
                            </div>
                        `;
                    } else {
                        data.forEach(row => {
                            const isOwner = row.type.includes('Owner');
                            const bannerClass = isOwner ? 'bg-owner' : 'bg-rental';
                            const initial = row.user ? row.user.charAt(0).toUpperCase() : '?';
                            
                            const card = document.createElement('div');
                            card.className = 'col-sm-6 col-lg-4 col-xl-3';
                            card.innerHTML = `
                                <div class="resident-card h-100">
                                    <div class="card-banner ${bannerClass}"></div>
                                    <div class="px-3 pb-3 position-relative text-center">
                                        <div class="d-flex justify-content-center">
                                            <div class="resident-avatar fw-bold">${initial}</div>
                                        </div>
                                        <h5 class="mt-2 mb-0 fw-bold text-truncate" title="${row.user}">${row.user}</h5>
                                        <div class="mt-1">${row.type}</div>
                                        
                                        <div class="d-flex justify-content-between text-start mt-3 px-2">
                                            <div>
                                                <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Block</div>
                                                <div class="fw-semibold">${row.block}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Flat No</div>
                                                <div class="fw-semibold text-primary">${row.flat}</div>
                                            </div>
                                        </div>

                                        <div class="mt-3 pt-3 border-top">
                                            <div class="d-flex justify-content-center gap-2">
                                                <!-- We extract the ID to use the same routes -->
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-resident" data-url="/residents/${row.id}/edit" data-coreui-toggle="tooltip" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-resident" data-url="/residents/${row.id}" data-id="${row.id}" data-coreui-toggle="tooltip" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            gridContent.appendChild(card);
                        });
                    }

                    // Copy pagination
                    const dtWrapper = document.getElementById('residents-table_wrapper');
                    if(dtWrapper) {
                        const paginate = dtWrapper.querySelector('.dataTables_paginate');
                        const info = dtWrapper.querySelector('.dataTables_info');
                        if(paginate) gridPagination.innerHTML = paginate.outerHTML;
                        if(info) gridInfo.innerHTML = info.innerHTML;

                        // Attach events to new pagination links
                        const newLinks = gridPagination.querySelectorAll('.paginate_button, .page-link');
                        newLinks.forEach(link => {
                            if(link.classList.contains('disabled') || link.classList.contains('current')) return;
                            
                            link.addEventListener('click', (e) => {
                                e.preventDefault();
                                
                                // Find the corresponding link in the original table
                                let dtLink;
                                if(link.getAttribute('data-dt-idx')) {
                                    dtLink = dtWrapper.querySelector('.dataTables_paginate').querySelector(`[data-dt-idx="${link.getAttribute('data-dt-idx')}"]`);
                                } else {
                                    // Fallback if data-dt-idx is missing (standard DataTables without BS5)
                                    const linkText = link.innerText.trim();
                                    const allDtLinks = dtWrapper.querySelectorAll('.dataTables_paginate .paginate_button');
                                    for(let i = 0; i < allDtLinks.length; i++) {
                                        if(allDtLinks[i].innerText.trim() === linkText && !allDtLinks[i].classList.contains('disabled')) {
                                            dtLink = allDtLinks[i];
                                            break;
                                        }
                                    }
                                }
                                
                                if(dtLink) {
                                    // Trigger click on the original Datatables link
                                    dtLink.click();
                                }
                            });
                        });
                    }
                }

                viewListBtn.addEventListener('change', (e) => {
                    if(e.target.checked) {
                        currentView = 'list';
                        tableContainer.classList.remove('d-none');
                        gridContainer.classList.add('d-none');
                    }
                });

                viewGridBtn.addEventListener('change', (e) => {
                    if(e.target.checked) {
                        currentView = 'grid';
                        tableContainer.classList.add('d-none');
                        gridContainer.classList.remove('d-none');
                        renderGrid();
                    }
                });

                // Listen to datatable draw event
                $('#residents-table').on('draw.dt', function () {
                    if(currentView === 'grid') {
                        renderGrid();
                    }
                });
            });
        </script>
    @endpush
</x-user-page>
