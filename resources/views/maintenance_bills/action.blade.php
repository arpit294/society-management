<div class="d-flex gap-2 justify-content-center">
    {{-- <button type="button" class="btn btn-sm btn-outline-primary btn-edit-maintenance-bill"
        data-url="{{ route('maintenance-bills.edit', $id) }}" data-title="Edit Bill">
        Edit
    </button> --}}
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-maintenance-bill"
        data-url="{{ route('maintenance-bills.destroy', $id) }}">
        Delete
    </button>
</div>
