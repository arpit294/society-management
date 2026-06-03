<form action="{{ route('maintenance-bills.store') }}" method="POST" id="maintenance-bill-ajax-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Generate Maintenance Bill</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                <select class="form-select" id="month" name="month" required>
                    <option value="">Select Month</option>
                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="year" name="year" required value="{{ date('Y') }}" min="2000">
            </div>

            <div class="col-md-6 mb-3">
                <label for="due_date" class="form-label">Payment Due Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>


            <div class="col-md-12 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="draft">Draft</option>
                    <option value="published">Publish</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Generate Bill</button>
    </div>
</form>
