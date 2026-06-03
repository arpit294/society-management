<div class="btn-group" role="group">
    @php
        $editUrl = route('expenses.edit', $id);
        $deleteUrl = route('expenses.destroy', $id);
    @endphp
    
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-expense" data-url="{{ $editUrl }}" data-title="Edit Expense">Edit</button>
    
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense" data-url="{{ $deleteUrl }}">Delete</button>
</div>
