<div class="d-flex gap-2 justify-content-center">
    @can('user_edit')
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-user" data-url="{{ route('users.edit', $id, false) }}"
        data-coreui-toggle="tooltip" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
    @endcan
    @can('user_delete')
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-user" data-url="{{ route('users.destroy', $id, false) }}"
        data-id="{{ $id }}" data-coreui-toggle="tooltip" title="Delete"><i class="fa-solid fa-trash"></i></button>
    @endcan
</div>
