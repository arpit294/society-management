<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-user" data-url="{{ route('users.edit', $id, false) }}"
        data-title="Edit User">Edit</button>

    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-user" data-url="{{ route('users.destroy', $id, false) }}"
        data-id="{{ $id }}">Delete</button>
</div>
