<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-expense-category"
        data-url="{{ route('expense-categories.edit', $id) }}" data-coreui-toggle="tooltip" title="Edit">
        <i class="fa-solid fa-pen-to-square"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense-category"
        data-url="{{ route('expense-categories.destroy', $id) }}" data-coreui-toggle="tooltip" title="Delete">
        <i class="fa-solid fa-trash"></i>
    </button>
</div>
