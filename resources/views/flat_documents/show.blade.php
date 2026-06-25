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
                                @if(isset($doc['file_path']))
                                    <a href="{{ asset('storage/' . $doc['file_path']) }}" target="_blank" class="btn btn-sm btn-info text-white" title="View">
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
                                    <input type="file" id="edit-doc-input-{{ $key }}" class="d-none edit-doc-input" data-url="{{ route('flat-documents.update-document', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}" accept=".pdf,.jpg,.jpeg,.png">
                                @endcan
                                @can('flat_document_delete')
                                    <button type="button" class="btn btn-sm btn-danger text-white btn-delete-doc" data-url="{{ route('flat-documents.delete-document', ['flat_document' => $flatDocument->id, 'doc_key' => $key]) }}" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endcan
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

<script>
    // Edit Document
    document.querySelectorAll('.btn-edit-doc').forEach(button => {
        button.addEventListener('click', function() {
            const key = this.getAttribute('data-key');
            document.getElementById('edit-doc-input-' + key).click();
        });
    });

    document.querySelectorAll('.edit-doc-input').forEach(input => {
        input.addEventListener('change', function() {
            if (this.files.length === 0) return;
            
            const file = this.files[0];
            const url = this.getAttribute('data-url');
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            const btn = this.previousElementSibling;
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    // Reload modal content
                    document.querySelector('[data-url="{{ route('flat-documents.show', $flatDocument->id) }}"]')?.click();
                    // Or reload page if modal button not easily found
                    if(!document.querySelector('[data-url="{{ route('flat-documents.show', $flatDocument->id) }}"]')) {
                        window.location.reload();
                    }
                } else {
                    toastr.error(data.message || 'Error updating document');
                }
            })
            .catch(error => {
                toastr.error('A network error occurred.');
                console.error(error);
            })
            .finally(() => {
                btn.innerHTML = originalIcon;
                btn.disabled = false;
                this.value = ''; // Reset input
            });
        });
    });

    // Delete Document
    document.querySelectorAll('.btn-delete-doc').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this document deletion!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success(data.message);
                            // Reload modal
                            const viewBtn = document.querySelector('[data-url="{{ route('flat-documents.show', $flatDocument->id) }}"]');
                            if (viewBtn) {
                                viewBtn.click();
                            } else {
                                window.location.reload();
                            }
                        } else {
                            toastr.error(data.message || 'Error deleting document');
                        }
                    })
                    .catch(error => {
                        toastr.error('A network error occurred.');
                        console.error(error);
                    });
                }
            });
        });
    });
</script>
