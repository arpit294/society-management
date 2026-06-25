<x-user-page>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="mb-0">Residents Management</h4>
        <div class="d-flex flex-wrap align-items-center gap-2">

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



    <!-- Resident Modal -->
    <div class="modal fade" id="resident-modal" tabindex="-1" aria-labelledby="residentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="resident-modal-content">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <!-- Import Resident Modal -->
    <div class="modal fade" id="import-resident-modal" tabindex="-1" aria-labelledby="importResidentModalLabel" aria-hidden="true" data-coreui-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importResidentModalLabel">Import Residents</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Step 1: Upload File -->
                <div id="import-step-1">
                    <form action="{{ route('residents.import.preview') }}" method="POST" enctype="multipart/form-data" id="import-preview-form">
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
                            <button type="submit" class="btn btn-primary bg-gradient border-0" id="preview-submit-btn">
                                <i class="fa-solid fa-eye me-2"></i>Preview Data
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Map Fields -->
                <div id="import-step-2" class="d-none">
                    <form action="{{ route('residents.import.process') }}" method="POST" id="import-process-form">
                        @csrf
                        <input type="hidden" name="file_path" id="process-file-path" value="">
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                Please map the columns from your Excel file to the corresponding database fields using the dropdowns above the table. Select "Skip" to ignore a column.
                            </div>
                            
                            <div id="preview-error-alert" class="alert alert-danger d-none"></div>

                            <div class="table-responsive" style="max-height: 450px;">
                                <table class="table table-bordered table-striped" id="preview-table">
                                    <thead class="sticky-top">
                                        <tr id="preview-dropdown-row">
                                            <!-- Dropdowns injected here -->
                                        </tr>
                                    </thead>
                                    <tbody id="preview-tbody">
                                        <!-- Data injected here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-secondary" id="btn-back-to-step-1">Back</button>
                            <button type="submit" class="btn btn-success bg-gradient border-0 text-white" id="process-submit-btn">
                                <i class="fa-solid fa-file-import me-2"></i>Process Import
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 3: Import Results -->
                <div id="import-step-3" class="d-none">
                    <div class="modal-body">
                        <div class="alert alert-success d-none mb-3" id="import-success-alert"></div>
                        <div class="alert alert-danger d-none mb-3" id="import-error-alert"></div>
                        <div id="import-summary-container" class="mb-3 text-center">
                            <h5 class="fw-bold"><span id="import-success-count" class="text-success">0</span> record(s) imported, <span id="import-failed-count" class="text-danger">0</span> failed</h5>
                        </div>
                        
                        <div id="import-failure-table-container" class="d-none">
                            <h6 class="fw-bold text-danger mb-2">Failed Records Details:</h6>
                            <div class="table-responsive border rounded" style="max-height: 300px;">
                                <table class="table table-sm table-hover table-striped mb-0" style="font-size: 0.875rem;">

                                    <tbody id="import-failure-tbody">
                                        <!-- Injected via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-primary" onclick="window.location.reload();">Finish & Reload</button>
                    </div>
                </div>
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
            });
        </script>
    @endpush
</x-user-page>
