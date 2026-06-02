<form id="expense-ajax-form" method="POST" action="{{ route('expenses.update', $expense->id) }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" value="PUT">

    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Edit Expense</h5>
            <p class="text-muted mb-0 small">Update the details of the expense.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div id="expense-form-errors" class="alert alert-danger d-none"></div>

        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Expense Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $expense->title) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Total Amount ($)</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="total_amount" class="form-control" value="{{ old('total_amount', $expense->total_amount) }}">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Logged By</label>
                <select name="user_id" class="form-select">
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $expense->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ $expense->category_id == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Invoice (Bill)</label>
                <input type="file" name="invoice" class="form-control" accept="image/jpeg,image/png,image/jpg,application/pdf">
                <small class="text-muted">Upload a new file to replace the existing one.</small>
                @if ($expense->invoice)
                    <div class="mt-2">
                        <a href="{{ asset('uploads/invoices/' . $expense->invoice) }}" target="_blank" class="badge bg-info text-decoration-none">📄 View Current Bill</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
