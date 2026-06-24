<form id="prepayment-form" action="{{ route('maintenance-bills.store') }}" method="POST" enctype="multipart/form-data" data-fees="{{ json_encode($residentFees) }}" data-discount="{{ json_encode($discountSettings) }}" data-penalty="{{ json_encode($penaltySettings) }}">
    @csrf
    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Record Maintenance Payment</h5>
            <p class="text-muted mb-0 small">Fill in the details to record a payment.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <!-- Hidden inputs required by controller -->
        <input type="hidden" name="months" id="hidden_months" value="0">
        <input type="hidden" name="start_month" id="hidden_start_month" value="">
        <input type="hidden" name="start_year" id="hidden_start_year" value="">

        <!-- Select Resident -->
        <div class="mb-4">
            <label for="resident_id" class="form-label fw-semibold text-muted small text-uppercase">Select Resident</label>
            <select name="resident_id" id="resident_id" class="form-select" required>
                <option value="">Select Resident</option>
                @foreach($residents as $resident)
                    <option value="{{ $resident->id }}">
                        {{ $resident->user->name ?? 'Unknown' }} 
                        ({{ $resident->flat->block->block_name ?? '' }} - {{ $resident->flat->flat_no ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Enrolled Batches / Maintenance Fees Section -->
        <div class="rounded p-3 mb-4 border" id="maintenance-fees-section" style="display: none;">
            <h6 class="fw-bold text-uppercase small text-muted mb-3">MAINTENANCE FEES</h6>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                <span class="text-secondary" id="fee-description">Basic Maintenance Fee</span>
                <span class="fw-medium">₹ <span id="display_monthly_fee">0.00</span></span>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-1">
                <span class="fw-bold">Monthly Total</span>
                <span class="fw-bold text-primary fs-5">₹ <span id="display_monthly_total">0.00</span></span>
            </div>
        </div>

        <!-- Dates -->
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="start_date" class="form-label fw-semibold text-muted small text-uppercase">Start Month</label>
                <input type="text" id="start_date" name="start_date" class="form-control @error('start_month') is-invalid @enderror" placeholder="Select Month" required>
                @error('start_month')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label fw-semibold text-muted small text-uppercase">End Month</label>
                <input type="text" id="end_date" name="end_date" class="form-control" placeholder="Select Month" required>
            </div>
        </div>

        <!-- Calculated Duration -->
        <div class="mb-4">
            <label for="calculated_duration" class="form-label fw-semibold text-muted small text-uppercase">Calculated Duration</label>
            <input type="text" id="calculated_duration" class="form-control fw-bold text-end pe-3" readonly value="0 Month(s)">
        </div>

        <!-- Totals Row -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="form-label fw-semibold text-muted small text-uppercase">Subtotal</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0">₹</span>
                    <input type="text" id="subtotal" class="form-control border-start-0 ps-0" readonly value="0.00">
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="form-label fw-semibold text-muted small text-uppercase">Penalty Amount</label>
                <div class="input-group">
                    <span class="input-group-text text-danger border-end-0">₹</span>
                    <input type="number" step="0.01" name="penalty_amount" id="penalty_amount" class="form-control text-danger border-start-0 ps-0" value="0.00">
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="form-label fw-semibold text-muted small text-uppercase">Discount Applied</label>
                <div class="input-group">
                    <span class="input-group-text text-success border-end-0">₹</span>
                    <input type="number" step="0.01" name="discount_amount" id="discount_applied" class="form-control text-success border-start-0 ps-0" value="0.00">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-muted small text-uppercase">Total Amount</label>
                <div class="input-group">
                    <span class="input-group-text bg-primary-subtle text-primary border-primary-subtle border-end-0 fw-bold">₹</span>
                    <input type="text" name="total_amount" id="total_amount" class="form-control bg-primary-subtle text-primary border-primary-subtle border-start-0 ps-0 fw-bold" readonly value="0.00">
                </div>
            </div>
        </div>

        <!-- Payment Method -->
        <div class="mb-3">
            <label for="payment_method" class="form-label fw-semibold text-muted small text-uppercase">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
            </select>
        </div>

        <div id="upi-details" class="d-none p-3 rounded mb-3 border">
            <div class="mb-3">
                <label for="transaction_id" class="form-label">Transaction ID (Optional)</label>
                <input type="text" name="transaction_id" id="transaction_id" class="form-control" value="{{ old('transaction_id') }}">
            </div>
            <div class="mb-2">
                <label for="payment_slip" class="form-label">Payment Slip (Required for UPI)</label>
                <input type="file" name="payment_slip" id="payment_slip" class="dropify" accept="image/*" data-height="120">
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
            Record Fee Payment
        </button>
    </div>
</form>


