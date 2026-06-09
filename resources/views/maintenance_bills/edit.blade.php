<form action="{{ route('maintenance-bills.update', $maintenanceBill->id) }}" method="POST" id="maintenance-bill-ajax-form">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title">Edit Maintenance Bill</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="user_id" class="form-label">Resident <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">Select Resident</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $maintenanceBill->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="block_id" class="form-label">Block <span class="text-danger">*</span></label>
                <select class="form-select" id="block_id" name="block_id" required>
                    <option value="">Select Block</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}" {{ $maintenanceBill->block_id == $block->id ? 'selected' : '' }}>{{ $block->block_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="flat_id" class="form-label">Flat <span class="text-danger">*</span></label>
                <select class="form-select" id="flat_id" name="flat_id" required>
                    <option value="">Select Flat</option>
                    @foreach($flats as $flat)
                        <option value="{{ $flat->id }}" data-block-id="{{ $flat->block_id }}" data-maintenance-fee="{{ $flat->flatType ? $flat->flatType->maintenance_fee : 0 }}" {{ $maintenanceBill->flat_id == $flat->id ? 'selected' : '' }}>
                            {{ $flat->flat_no }} ({{ $flat->flatType ? $flat->flatType->name . ' - $' . number_format($flat->flatType->maintenance_fee, 2) : 'No Type' }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required value="{{ $maintenanceBill->amount }}">
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="month" class="form-label">Month</label>
                <input type="text" class="form-control" id="month" name="month" readonly disabled value="{{ $maintenanceBill->maintenance->month }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" class="form-control" id="year" name="year" readonly disabled value="{{ $maintenanceBill->maintenance->year }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="generated_date" class="form-label">Generated Date</label>
                <input type="date" class="form-control" id="generated_date" name="generated_date" readonly disabled value="{{ $maintenanceBill->generated_date ? $maintenanceBill->generated_date->format('Y-m-d') : '' }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" readonly disabled value="{{ $maintenanceBill->maintenance->due_date ? \Carbon\Carbon::parse($maintenanceBill->maintenance->due_date)->format('Y-m-d') : '' }}">
            </div>

            <div class="col-md-12 mb-3">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">Bill Summary</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Maintenance:</td>
                                <td class="text-end fw-bold">₹{{ number_format($maintenanceBill->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Late Days (Calculated):</td>
                                <td class="text-end fw-bold">
                                    @php
                                        $dueDate = $maintenanceBill->maintenance->due_date ? \Carbon\Carbon::parse($maintenanceBill->maintenance->due_date)->startOfDay() : null;
                                        $endDate = $maintenanceBill->status === 'paid' && $maintenanceBill->paid_at ? $maintenanceBill->paid_at->startOfDay() : now()->startOfDay();
                                        $lateDays = ($dueDate && $endDate->gt($dueDate)) ? $dueDate->diffInDays($endDate) : 0;
                                    @endphp
                                    {{ $lateDays }} Days
                                </td>
                            </tr>
                            <tr>
                                <td>Penalty Applied:</td>
                                <td class="text-end fw-bold text-danger">₹{{ number_format($maintenanceBill->penalty_amount, 2) }}</td>
                            </tr>
                            <tr class="border-top border-dark">
                                <td class="fw-bold">Total Amount:</td>
                                <td class="text-end fw-bold text-success fs-5">₹{{ number_format($maintenanceBill->total_amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="due" {{ $maintenanceBill->status == 'due' ? 'selected' : '' }}>Due</option>
                    <option value="pending" {{ $maintenanceBill->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $maintenanceBill->status == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Bill</button>
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
