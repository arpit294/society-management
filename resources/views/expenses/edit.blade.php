<div class="modal-header">
    <h5 class="modal-title" id="expenseModalLabel">Edit Expense</h5>
    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
</div>
<form id="expense-ajax-form" action="{{ route('expenses.update', $expense->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $expense->title }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="total_amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="{{ $expense->total_amount }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ $expense->category_id == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $expense->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mb-3">
                <label for="invoice" class="form-label">Invoice (Optional)</label>
                <input type="file" class="form-control" id="invoice" name="invoice" accept=".jpg,.jpeg,.png,.pdf">
                <!-- Image Preview Container -->
                <div id="invoice-preview-container" class="mt-3 d-none">
                    <p class="mb-1 text-muted small">New Invoice Preview:</p>
                    <img id="invoice-preview-img" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px; width: auto;">
                </div>
                @if($expense->invoice)
                    <div class="mt-2" id="current-invoice-container">
                        <a href="{{ asset('uploads/invoices/'.$expense->invoice) }}" target="_blank">View Current Invoice</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Expense</button>
    </div>
</form>
