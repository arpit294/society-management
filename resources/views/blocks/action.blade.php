<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-block" data-url="{{ route('blocks.edit', $id, false) }}"
        data-coreui-toggle="tooltip" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>

    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-block" data-url="{{ route('blocks.destroy', $id, false) }}"
        data-id="{{ $id }}" data-coreui-toggle="tooltip" title="Delete"><i class="fa-solid fa-trash"></i></button>
</div>
