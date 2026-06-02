<div class="d-flex justify-content-center gap-2">
    <button class="btn btn-sm btn-info text-white btn-edit-expense" data-url="{{ route('expenses.edit', $id) }}" data-title="Edit Expense">
        <i class="fa-solid fa-pen-to-square"></i>
    </button>
    <button class="btn btn-sm btn-danger text-white btn-delete-expense" data-url="{{ route('expenses.destroy', $id) }}">
        <i class="fa-solid fa-trash"></i>
    </button>
</div>
