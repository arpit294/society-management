<x-user-page>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Flat Documents</h1>
            <button type="button" class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#addDocumentModal"
                data-url="{{ route('flat-documents.create') }}" data-title="Upload Document">
                <i class="fas fa-plus"></i> Upload Document
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="m-0 text-body">Document List</h5>
                <div class="d-flex align-items-end gap-3 flex-wrap">
                    <div class="filter-col" style="min-width: 220px;">
                        <label class="form-label mb-1" for="flat-documents-filter-block">Filter by Block</label>
                        <select id="flat-documents-filter-block" class="form-select select2-filter" style="width: 100%;">
                            <option value="">All Blocks</option>
                            @foreach ($blocks as $block)
                                <option value="{{ $block->block_name }}">{{ $block->block_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-col d-none" id="flat-documents-filter-reset-col" style="min-width: 150px;">
                        <button type="button" id="flat-documents-filter-reset" class="btn btn-outline-secondary w-100">
                            Reset filters
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Document Modal -->
    <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable add-document-dialog">
            <div class="modal-content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- View Document Modal -->
    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush

    @push('styles')
        <style>
            #addDocumentModal .add-document-dialog .modal-content {
                max-height: calc(100vh - 3.5rem);
                overflow: hidden;
            }

            #addDocumentModal .add-document-form {
                display: flex;
                flex: 1 1 auto;
                flex-direction: column;
                min-height: 0;
                overflow: hidden;
            }

            #addDocumentModal .add-document-form .modal-body {
                min-height: 0;
                overflow-y: auto;
            }

            #addDocumentModal .add-document-form .modal-footer {
                flex-shrink: 0;
            }

            @media (max-width: 575.98px) {
                #addDocumentModal .add-document-dialog .modal-content {
                    max-height: calc(100vh - 1rem);
                }
            }
        </style>
    @endpush
</x-user-page>
