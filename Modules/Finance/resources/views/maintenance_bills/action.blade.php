<div class="d-flex gap-2 justify-content-center">
    @can('maintenance_bill_create')
    <button type="button" class="btn btn-sm btn-outline-primary btn-status-maintenance"
        data-url="{{ route('maintenance-bills.update-status', $id, false) }}"
        data-status="{{ $status }}"
        data-coreui-toggle="tooltip" title="Update Status">
        <i class="fa-solid fa-pen"></i>
    </button>
    @endcan
    @can('maintenance_bill_delete')
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-maintenance-bill"
        data-url="{{ route('maintenance-bills.destroy', $id) }}"
        data-coreui-toggle="tooltip" title="Delete">
        <i class="fa-solid fa-trash"></i>
    </button>
    @endcan
</div>
