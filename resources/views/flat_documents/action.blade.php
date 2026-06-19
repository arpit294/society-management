<div class="btn-group" role="group">
    @can('flat_document_view')
    <a href="{{ route('flat-documents.download', $id) }}" class="btn btn-sm btn-outline-primary" data-coreui-toggle="tooltip" title="Download">
        <i class="fas fa-download"></i>
    </a>
    @endcan
    @can('flat_document_delete')
    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-url="{{ route('flat-documents.destroy', $id) }}" data-coreui-toggle="tooltip" title="Delete">
        <i class="fas fa-trash"></i>
    </button>
    @endcan
</div>
