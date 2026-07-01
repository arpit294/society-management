<div class="d-flex gap-2 justify-content-center">
    @can('name_transfer_bill_view')
        @if(!$is_approved)
            @if($status === 'paid')
            <button type="button" class="btn btn-sm btn-outline-success btn-approve" 
                data-url="{{ route('name-transfer-bills.approve', $id, false) }}"
                data-coreui-toggle="tooltip" title="Approve Transfer">
                <i class="fa-solid fa-check"></i>
            </button>
            @else
            <span class="d-inline-block" tabindex="0" data-coreui-toggle="tooltip" title="Payment required before approval">
                <button type="button" class="btn btn-sm btn-outline-secondary opacity-50" disabled style="pointer-events: none;">
                    <i class="fa-solid fa-lock me-1"></i><i class="fa-solid fa-check"></i>
                </button>
            </span>
            @endif
        @endif
        <button type="button" class="btn btn-sm btn-outline-primary btn-status" 
            data-url="{{ route('name-transfer-bills.update-status', $id, false) }}" 
            data-status="{{ $status }}"
            data-coreui-toggle="tooltip" title="Update Status">
            <i class="fa-solid fa-pen"></i>
        </button>
    @endcan
    @can('name_transfer_bill_delete')
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-bill" 
        data-url="{{ route('name-transfer-bills.destroy', $id, false) }}"
        data-coreui-toggle="tooltip" title="Delete">
        <i class="fa-solid fa-trash"></i>
    </button>
    @endcan
</div>
