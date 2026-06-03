<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-flat-type"
        data-url="{{ route('flat-types.edit', $id) }}" data-title="Edit Flat Type">
        Edit
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-flat-type"
        data-url="{{ route('flat-types.destroy', $id) }}">
        Delete
    </button>
</div>
