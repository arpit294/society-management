<div class="d-flex gap-2 justify-content-center" role="group">
    @php
        $editUrl = route('expenses.edit', $id);
        $deleteUrl = route('expenses.destroy', $id);
    @endphp
    
    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-expense" data-url="{{ $editUrl }}" data-coreui-toggle="tooltip" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
    
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense" data-url="{{ $deleteUrl }}" data-coreui-toggle="tooltip" title="Delete"><i class="fa-solid fa-trash"></i></button>
</div>
