<form action="{{ route('maintenance-bills.store') }}" method="POST" id="maintenance-bill-ajax-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Generate Maintenance Bill</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="user_id" class="form-label">Resident <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">Select Resident</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="block_id" class="form-label">Block <span class="text-danger">*</span></label>
                <select class="form-select" id="block_id" name="block_id" required>
                    <option value="">Select Block</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}">{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="flat_id" class="form-label">Flat <span class="text-danger">*</span></label>
                <select class="form-select" id="flat_id" name="flat_id" required>
                    <option value="">Select Flat</option>
                    @foreach($flats as $flat)
                        <option value="{{ $flat->id }}" data-block-id="{{ $flat->block_id }}" data-maintenance-fee="{{ $flat->flatType ? $flat->flatType->maintenance_fee : 0 }}">
                            {{ $flat->flat_no }} ({{ $flat->flatType ? $flat->flatType->name . ' - $' . number_format($flat->flatType->maintenance_fee, 2) : 'No Type' }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required placeholder="0.00">
                </div>
            </div>

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
                <label for="generated_date" class="form-label">Generated Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="generated_date" name="generated_date" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="due">Due</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Generate Bill</button>
    </div>
</form>

<script>
    document.getElementById('user_id').addEventListener('change', function() {
        const userId = this.value;
        if (!userId) {
            document.getElementById('block_id').value = '';
            document.getElementById('flat_id').value = '';
            document.getElementById('amount').value = '';
            return;
        }

        fetch(`/maintenance-bills/resident-info/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('block_id').value = data.block_id;
                    document.getElementById('flat_id').value = data.flat_id;
                    document.getElementById('amount').value = data.amount;
                } else {
                    document.getElementById('block_id').value = '';
                    document.getElementById('flat_id').value = '';
                    document.getElementById('amount').value = '';
                }
            })
            .catch(error => console.error('Error fetching resident info:', error));
    });
</script>
