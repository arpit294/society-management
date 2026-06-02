<div class="modal-header">
    <h5 class="modal-title" id="expenseCategoryModalLabel">Edit Expense Category</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="expense-category-ajax-form" action="{{ route('expense-categories.update', $expenseCategory->id, false) }}" method="POST">
    @csrf
    <input type="hidden" name="_method" value="PUT">
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-12">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $expenseCategory->title }}">
            </div>
            
            <div class="col-md-12">
                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="slug" name="slug" value="{{ $expenseCategory->slug }}">
            </div>

            <div class="col-md-12">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status">
                    <option value="active" {{ $expenseCategory->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $expenseCategory->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update changes</button>
    </div>
</form>
