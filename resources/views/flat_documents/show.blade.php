<div class="modal-header">
    <h5 class="modal-title" id="viewDocumentModalLabel">
        Documents for {{ $flatDocument->flat->block->block_name ?? '' }} - {{ $flatDocument->flat->flat_no ?? '' }}
    </h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="mb-4">
        <h6 class="fw-bold">Resident Details</h6>
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
    
    @if(empty($flatDocument->documents))
        <div class="alert alert-warning">
            No documents found for this submission.
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
                    @foreach($flatDocument->documents as $key => $doc)
                        <tr>
                            <td>{{ $doc['title'] ?? ucfirst(str_replace('_', ' ', $key)) }}</td>
                            <td><span class="badge bg-secondary text-uppercase">{{ $doc['file_type'] ?? 'Unknown' }}</span></td>
                            <td>
                                @if(isset($doc['file_size']))
                                    {{ round($doc['file_size'] / 1024, 2) }} KB
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('flat-documents.download', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}" class="btn btn-sm btn-primary" title="Download">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
</div>
