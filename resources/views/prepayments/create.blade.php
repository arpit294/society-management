<x-user-page>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header border-bottom-0 pt-4 pb-0 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Record Maintenance Prepayment</h4>
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

                <form action="{{ route('prepayments.store') }}" method="POST" enctype="multipart/form-data" id="prepayment-form">
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
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Subtotal</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">₹</span>
                                <input type="text" id="subtotal" class="form-control border-start-0 ps-0" readonly value="0.00">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Discount Applied</label>
                            <div class="input-group">
                                <span class="input-group-text text-success border-end-0">₹</span>
                                <input type="text" id="discount_applied" class="form-control text-success border-start-0 ps-0" readonly value="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Total Amount</label>
                            <div class="input-group">
                                <span class="input-group-text text-primary border-end-0 fw-bold">₹</span>
                                <input type="text" id="total_amount" class="form-control text-primary border-start-0 ps-0 fw-bold" readonly value="0.00">
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

                    <!-- Status -->
                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold text-muted small text-uppercase">Status</label>
                        <select id="status" class="form-select" disabled>
                            <option value="paid" selected>Paid</option>
                        </select>
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
    // Data injected from controller
    const residentFees = @json($residentFees);
    const discountSettings = @json($discountSettings);
    
    let currentMonthlyFee = 0;

    $(document).ready(function() {
        if ($('.dropify').length > 0) {
            $('.dropify').dropify();
        }

        const paymentMethodSelect = document.getElementById('payment_method');
        const upiDetails = document.getElementById('upi-details');
        const paymentSlip = document.getElementById('payment_slip');
        
        function toggleUpiDetails() {
            if (paymentMethodSelect.value === 'upi') {
                upiDetails.classList.remove('d-none');
                paymentSlip.setAttribute('required', 'required');
            } else {
                upiDetails.classList.add('d-none');
                paymentSlip.removeAttribute('required');
            }
        }
        paymentMethodSelect.addEventListener('change', toggleUpiDetails);
        toggleUpiDetails();

        // Resident Change Handler
        $('#resident_id').on('change', function() {
            const resId = $(this).val();
            if (resId && residentFees[resId]) {
                currentMonthlyFee = parseFloat(residentFees[resId]);
                $('#display_monthly_fee').text(currentMonthlyFee.toFixed(2));
                $('#display_monthly_total').text(currentMonthlyFee.toFixed(2));
                $('#maintenance-fees-section').slideDown();
            } else {
                currentMonthlyFee = 0;
                $('#maintenance-fees-section').slideUp();
            }
            calculateTotals();
        });

        // Date Change Handler
        $('#start_date, #end_date').on('change', calculateTotals);

        function calculateTotals() {
            const startDateVal = $('#start_date').val();
            const endDateVal = $('#end_date').val();
            
            let months = 0;
            
            if (startDateVal && endDateVal) {
                const start = new Date(startDateVal);
                const end = new Date(endDateVal);
                
                if (end >= start) {
                    months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth()) + 1;
                    
                    // Populate hidden fields for form submission
                    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                    $('#hidden_start_month').val(monthNames[start.getMonth()]);
                    $('#hidden_start_year').val(start.getFullYear());
                } else {
                    months = 0;
                }
            }
            
            $('#calculated_duration').val(`${months} Month(s)`);
            $('#hidden_months').val(months);

            if (months > 0 && currentMonthlyFee > 0) {
                const subtotal = currentMonthlyFee * months;
                
                let discountValue = 0;
                if (discountSettings.apply_discount == '1') {
                    if (months >= 12) {
                        discountValue = discountSettings.yearly_value;
                    } else if (months >= 6) {
                        discountValue = discountSettings.half_yearly_value;
                    } else if (months >= 3) {
                        discountValue = discountSettings.quarterly_value;
                    } else {
                        discountValue = discountSettings.monthly_value;
                    }
                }
                
                let discountAmount = 0;
                if (discountSettings.type === 'fixed') {
                    discountAmount = discountValue;
                } else {
                    discountAmount = subtotal * (discountValue / 100);
                }

                const totalAmount = subtotal - discountAmount;

                $('#subtotal').val(subtotal.toFixed(2));
                $('#discount_applied').val(discountAmount.toFixed(2));
                $('#total_amount').val(totalAmount.toFixed(2));
                
                // Max months check
                if (months > 12) {
                    $('#submit-btn').prop('disabled', true);
                    alert('You can prepay for a maximum of 12 months.');
                } else {
                    $('#submit-btn').prop('disabled', false);
                }
            } else {
                $('#subtotal').val('0.00');
                $('#discount_applied').val('0.00');
                $('#total_amount').val('0.00');
                $('#submit-btn').prop('disabled', true);
            }
        }
    });
</script>
</x-user-page>
