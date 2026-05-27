<div class="d-flex justify-content-center gap-2">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-user" data-url="{{ route('users.edit', $id) }}"
        data-title="Edit User">
        Edit
    </button>

    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-user"
        data-url="{{ route('users.destroy', $id) }}" data-id="{{ $id }}">
        Delete
    </button>
</div>
