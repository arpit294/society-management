<div class="d-flex gap-2 justify-content-center">
    @can('resident_edit')
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-resident" data-url="{{ route('residents.edit', $id) }}" data-coreui-toggle="tooltip" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
    @endcan
    @can('resident_delete')
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-resident" data-url="{{ route('residents.destroy', $id) }}" data-id="{{ $id }}" data-coreui-toggle="tooltip" title="Delete"><i class="fa-solid fa-trash"></i></button>
    @endcan
</div>
