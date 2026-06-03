<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-expense-category"
        data-url="{{ route('expense-categories.edit', $id) }}" data-title="Edit Expense Category">
        Edit
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense-category"
        data-url="{{ route('expense-categories.destroy', $id) }}">
        Delete
    </button>
</div>
