<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-flat" data-url="{{ route('flats.edit', $id, false) }}"
        data-title="Edit Flat">Edit</button>

    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-flat" data-url="{{ route('flats.destroy', $id, false) }}"
        data-id="{{ $id }}">Delete</button>
</div>
