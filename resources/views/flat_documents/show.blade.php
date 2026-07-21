<div class="modal-header">
    <h5 class="modal-title" id="viewDocumentModalLabel">
        Documents for {{ $flatDocument->flat->block->block_name ?? '' }} - {{ $flatDocument->flat->flat_no ?? '' }}
    </h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="mb-4">
        <h6 class="fw-bold">{{ \App\Models\Setting::label('resident', 'Resident') }} Details</h6>
        <div class="row">
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Name</p>
                <p class="fw-semibold">{{ $flatDocument->user->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Type</p>
                <p class="fw-semibold">
                    {{ $flatDocument->resident_type === 'owner' ? 'Owner' : ($flatDocument->resident_type === 'rental' ? 'Tenant' : ucfirst($flatDocument->resident_type)) }}
                </p>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Contact</p>
                <p class="fw-semibold">{{ $flatDocument->user->phone ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    
    <hr>
    
    <h6 class="fw-bold mb-3">Uploaded Documents</h6>
    
    @if(empty($expectedDocs))
        <div class="alert alert-warning">
            No documents configured for this resident type.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Document Title</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expectedDocs as $key => $docInfo)
                        @php
                            $doc = $flatDocument->documents[$key] ?? null;
                        @endphp
                        <tr>
                            <td>{{ $docInfo['label'] }}</td>
                            <td>
                                @if($doc)
                                    <span class="badge bg-secondary text-uppercase">{{ $doc['file_type'] ?? 'Unknown' }}</span>
                                @else
                                    <span class="badge bg-danger">Missing</span>
                                @endif
                            </td>
                            <td>
                                @if($doc && isset($doc['file_size']))
                                    {{ round($doc['file_size'] / 1024, 2) }} KB
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($doc)
                                    @if(isset($doc['file_path']))
                                        <a href="{{ route('flat-documents.download', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}?inline=true" target="_blank" class="btn btn-sm btn-info text-white" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('flat-documents.download', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}" class="btn btn-sm btn-primary" title="Download">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    @can('flat_document_edit')
                                        <button type="button" class="btn btn-sm btn-warning text-white btn-edit-doc" data-key="{{ $key }}" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <input type="file" id="edit-doc-input-{{ $key }}" class="d-none edit-doc-input" data-url="{{ route('flat-documents.update-document', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    @endcan
                                    @can('flat_document_delete')
                                        <button type="button" class="btn btn-sm btn-danger text-white btn-delete-doc" data-url="{{ route('flat-documents.delete-document', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                @else
                                    @can('flat_document_edit')
                                        <button type="button" class="btn btn-sm btn-success text-white btn-edit-doc" data-key="{{ $key }}" title="Upload Document">
                                            <i class="fa-solid fa-upload"></i> Upload
                                        </button>
                                        <input type="file" id="edit-doc-input-{{ $key }}" class="d-none edit-doc-input" data-url="{{ route('flat-documents.update-document', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<div id="flat-doc-modal-config" class="d-none" data-show-url="{{ route('flat-documents.show', $flatDocument->id) }}"></div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
</div>
