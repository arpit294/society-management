<x-user-page>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header border-bottom-0 pt-4 pb-0 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Record Maintenance Payment</h4>
                    <a href="{{ route('maintenance-bills.index') }}" class="btn-close text-reset" aria-label="Close"></a>
                </div>
                <hr>
            </div>
            <div class="card-body px-4 pb-4">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data" id="prepayment-form">
                    @csrf
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
                            <label for="start_date" class="form-label fw-semibold text-muted small text-uppercase">Start Date</label>
                            <input type="date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label fw-semibold text-muted small text-uppercase">End Date</label>
                            <input type="date" id="end_date" class="form-control" required>
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



                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 d-flex justify-content-center align-items-center" id="submit-btn" disabled>
                        <i class="fa-regular fa-circle-check fs-5 me-2"></i> Record Fee Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables for script.js
    window.residentFees = @json($residentFees);
    window.discountSettings = @json($discountSettings);
    window.penaltySettings = @json($penaltySettings);
    
    $(document).ready(function() {
        try {
            if ($('.dropify').length > 0) {
                $('.dropify').dropify();
            }
        } catch (e) {
            console.log("Dropify not loaded.");
        }

        // Initialize Select2 for Resident Dropdown
        $('#resident_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Resident',
            width: '100%'
        });
    });
</script>
</x-user-page>
