<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-block" data-url="{{ route('blocks.edit', $id) }}"
        data-title="Edit Block">Edit</button>

    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-block" data-url="{{ route('blocks.destroy', $id) }}"
        data-id="{{ $id }}">Delete</button>
</div>
