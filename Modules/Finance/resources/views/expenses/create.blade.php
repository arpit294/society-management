<div class="modal-header">
    <h5 class="modal-title" id="expenseModalLabel">Add Expense</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="expense-ajax-form" action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="total_amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="expense_date" name="expense_date" placeholder="Select Month" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->resident_details }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mb-3">
                <label for="invoice" class="form-label">Invoice (Optional)</label>
                <input type="file" class="dropify" id="invoice" name="invoice" accept=".jpg,.jpeg,.png,.pdf" data-height="200">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Expense</button>
    </div>
</form>
