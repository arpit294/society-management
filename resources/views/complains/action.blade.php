<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-complain" data-url="{{ route('complains.edit', $id) }}"
        data-title="Edit Complaint">Edit</button>

    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-complain" data-url="{{ route('complains.destroy', $id) }}"
        data-id="{{ $id }}">Delete</button>
</div>
