<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-resident" data-url="{{ route('residents.edit', $id, false) }}"
        data-title="Edit Resident">Edit</button>

    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-resident" data-url="{{ route('residents.destroy', $id, false) }}"
        data-id="{{ $id }}">Delete</button>
</div>
