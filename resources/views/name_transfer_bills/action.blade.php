<div class="d-flex gap-2 justify-content-center">
    @if(!$is_approved)
    <button type="button" class="btn btn-sm btn-outline-success btn-approve" 
        data-url="{{ route('name-transfer-bills.approve', $id, false) }}"
        data-coreui-toggle="tooltip" title="Approve Transfer">
        <i class="fa-solid fa-check"></i>
    </button>
    @endif
    <button type="button" class="btn btn-sm btn-outline-primary btn-status" 
        data-url="{{ route('name-transfer-bills.update-status', $id, false) }}" 
        data-status="{{ $status }}"
        data-coreui-toggle="tooltip" title="Update Status">
        <i class="fa-solid fa-pen"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-bill" 
        data-url="{{ route('name-transfer-bills.destroy', $id, false) }}"
        data-coreui-toggle="tooltip" title="Delete">
        <i class="fa-solid fa-trash"></i>
    </button>
</div>
