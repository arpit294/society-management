<x-user-page>
<div class="row">
    <div class="col-12 col-md-8 offset-md-2">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Add Prepayment</h4>
            </div>
            <div class="card-body">
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

                <form action="{{ route('prepayments.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="resident_id" class="form-label">Resident</label>
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

                    <div class="mb-3">
                        <label for="months" class="form-label">Number of Months</label>
                        <input type="number" name="months" id="months" class="form-control" value="6" min="1" max="12" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_month" class="form-label">Start Month</label>
                            <select name="start_month" id="start_month" class="form-select" required>
                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                    <option value="{{ $month }}" {{ now()->format('F') == $month ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_year" class="form-label">Start Year</label>
                            <input type="number" name="start_year" id="start_year" class="form-control" value="{{ now()->year }}" min="{{ now()->year }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Select Mode</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                        </select>
                    </div>

                    <div id="upi-details" class="d-none">
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID (Optional)</label>
                            <input type="text" name="transaction_id" id="transaction_id" class="form-control" value="{{ old('transaction_id') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="payment_slip" class="form-label">Payment Slip (Required for UPI)</label>
                            <input type="file" name="payment_slip" id="payment_slip" class="dropify" accept="image/*" data-height="150">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('maintenance-bills.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Process Prepayment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-user-page>

<script>
    $(document).ready(function() {
        $('.dropify').dropify();
    });

    document.addEventListener('DOMContentLoaded', function() {
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
        toggleUpiDetails(); // Initial state
    });
</script>
