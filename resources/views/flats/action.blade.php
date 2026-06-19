<div class="d-flex gap-2 justify-content-center">
    @can('flat_view')
    <button type="button" class="btn btn-sm btn-outline-info btn-history-flat" data-url="{{ route('flats.show', $id, false) }}"
        data-coreui-toggle="tooltip" title="Resident History"><i class="fa-solid fa-clock-rotate-left"></i></button>
    @endcan

    @can('flat_edit')
    <button type="button" class="btn btn-sm btn-outline-warning btn-transfer-flat" data-url="{{ route('flats.transfer.create', $id, false) }}"
        data-coreui-toggle="tooltip" title="Transfer Ownership"><i class="fa-solid fa-exchange-alt"></i></button>

    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-flat" data-url="{{ route('flats.edit', $id, false) }}"
        data-coreui-toggle="tooltip" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
    @endcan

    @can('flat_delete')
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-flat" data-url="{{ route('flats.destroy', $id, false) }}"
        data-id="{{ $id }}" data-coreui-toggle="tooltip" title="Delete"><i class="fa-solid fa-trash"></i></button>
    @endcan
</div>
