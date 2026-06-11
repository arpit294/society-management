<x-user-page>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Global Settings</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        @push('scripts')
                            <script>
                                $(document).ready(function() {
                                    toastr.success("{{ session('success') }}");
                                });
                            </script>
                        @endpush
                    @endif

                    <form action="{{ route('settings.store') }}" method="POST">
                        @csrf

                        <h5 class="mb-3 fw-bold">General Settings</h5>
                        <div class="row mb-5">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Society Name</label>
                                <input type="text" name="society_name" class="form-control"
                                    value="{{ $settings['society_name'] ?? 'My Society' }}">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Society Address</label>
                                <input type="text" name="society_address" class="form-control"
                                    value="{{ $settings['society_address'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control"
                                    value="{{ $settings['contact_email'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Contact Phone</label>
                                <input type="text" name="contact_phone" class="form-control"
                                    value="{{ $settings['contact_phone'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Financial Year Start</label>
                                <select name="financial_year_start" class="form-select">
                                    <option value="january_1"
                                        {{ ($settings['financial_year_start'] ?? 'january_1') == 'january_1' ? 'selected' : '' }}>
                                        1st January</option>
                                    <option value="april_1"
                                        {{ ($settings['financial_year_start'] ?? 'january_1') == 'april_1' ? 'selected' : '' }}>
                                        1st April</option>
                                </select>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h5 class="mb-3 fw-bold">Late Penalty Settings</h5>
                        <div class="row mb-5">
                            <div class="col-md-12 mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Allow Late Fees Penalty</label>
                                <div class="form-check form-switch fs-5">
                                    <input type="hidden" name="apply_penalty" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_penalty"
                                        name="apply_penalty" value="1"
                                        {{ ($settings['apply_penalty'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 ms-2 mt-1" for="apply_penalty">Yes, automatically apply penalty to unpaid invoices</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Penalty Charge Type</label>
                                <select name="penalty_type" id="penalty_type" class="form-select">
                                    <option value="percentage"
                                        {{ ($settings['penalty_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="fixed"
                                        {{ ($settings['penalty_type'] ?? 'percentage') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Penalty Grace Days</label>
                                <div class="input-group">
                                    <input type="number" name="penalty_due_days" class="form-control"
                                        value="{{ $settings['penalty_due_days'] ?? '15' }}">
                                    <span class="input-group-text text-muted">days after due date</span>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-penalty">Monthly Package</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_monthly_value" class="form-control text-end"
                                        value="{{ $settings['penalty_monthly_value'] ?? ($settings['penalty_monthly_percent'] ?? '0') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-penalty">Quarterly (3 Months)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_quarterly_value" class="form-control text-end"
                                        value="{{ $settings['penalty_quarterly_value'] ?? ($settings['penalty_quarterly_percent'] ?? '5') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-penalty">Half-Yearly (6 Months)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_half_yearly_value" class="form-control text-end"
                                        value="{{ $settings['penalty_half_yearly_value'] ?? ($settings['penalty_half_yearly_percent'] ?? '10') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-penalty">Yearly (12 Months)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_yearly_value" class="form-control text-end"
                                        value="{{ $settings['penalty_yearly_value'] ?? ($settings['penalty_yearly_percent'] ?? '15') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h5 class="mb-3 fw-bold">Prepayment Discount Settings</h5>
                        <div class="row mb-5">
                            <div class="col-md-12 mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Allow Prepayment Discount</label>
                                <div class="form-check form-switch fs-5">
                                    <input type="hidden" name="apply_discount" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_discount"
                                        name="apply_discount" value="1"
                                        {{ ($settings['apply_discount'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 ms-2 mt-1" for="apply_discount">Yes, automatically apply discounts to prepayments</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Discount Charge Type</label>
                                <select name="discount_type" id="discount_type" class="form-select">
                                    <option value="percentage"
                                        {{ ($settings['discount_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="fixed"
                                        {{ ($settings['discount_type'] ?? 'percentage') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount (₹)</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-discount">Monthly Package</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_monthly_value" class="form-control text-end"
                                        value="{{ $settings['discount_monthly_value'] ?? ($settings['discount_monthly_percent'] ?? '0') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-discount">Quarterly (3 Months)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_quarterly_value" class="form-control text-end"
                                        value="{{ $settings['discount_quarterly_value'] ?? ($settings['discount_quarterly_percent'] ?? '5') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-discount">Half-Yearly (6 Months)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_half_yearly_value" class="form-control text-end"
                                        value="{{ $settings['discount_half_yearly_value'] ?? ($settings['discount_half_yearly_percent'] ?? '10') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase label-discount">Yearly (12 Months)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_yearly_value" class="form-control text-end"
                                        value="{{ $settings['discount_yearly_value'] ?? ($settings['discount_yearly_percent'] ?? '15') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-end border-top pt-4">
                            <button type="submit" class="btn btn-primary fw-bold px-5 py-2 rounded-3"><i class="fa-solid fa-save me-2"></i> Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const applyPenaltyToggle = document.getElementById('apply_penalty');
                const penaltyValueInputs = [
                    document.querySelector('input[name="penalty_monthly_value"]'),
                    document.querySelector('input[name="penalty_quarterly_value"]'),
                    document.querySelector('input[name="penalty_half_yearly_value"]'),
                    document.querySelector('input[name="penalty_yearly_value"]'),
                    document.querySelector('input[name="penalty_due_days"]')
                ];

                const penaltyTypeSelect = document.getElementById('penalty_type');
                const discountTypeSelect = document.getElementById('discount_type');
                const applyDiscountToggle = document.getElementById('apply_discount');
                const discountInputs = [
                    document.querySelector('input[name="discount_monthly_value"]'),
                    document.querySelector('input[name="discount_quarterly_value"]'),
                    document.querySelector('input[name="discount_half_yearly_value"]'),
                    document.querySelector('input[name="discount_yearly_value"]')
                ];

                function togglePenaltyFields() {
                    const isChecked = applyPenaltyToggle.checked;
                    penaltyValueInputs.forEach(input => {
                        if (input) input.disabled = !isChecked;
                    });
                    if (penaltyTypeSelect) penaltyTypeSelect.disabled = !isChecked;
                }

                function toggleDiscountFields() {
                    const isChecked = applyDiscountToggle.checked;
                    discountInputs.forEach(input => {
                        if (input) input.disabled = !isChecked;
                    });
                    if (discountTypeSelect) discountTypeSelect.disabled = !isChecked;
                }

                function updatePenaltyLabels() {
                    const isFixed = penaltyTypeSelect.value === 'fixed';
                    const suffix = isFixed ? '₹' : '%';
                    document.querySelectorAll('.penalty-suffix').forEach(el => {
                        el.innerText = suffix;
                    });
                }

                function updateDiscountLabels() {
                    const isFixed = discountTypeSelect.value === 'fixed';
                    const suffix = isFixed ? '₹' : '%';
                    document.querySelectorAll('.discount-suffix').forEach(el => {
                        el.innerText = suffix;
                    });
                }

                if (applyPenaltyToggle) {
                    applyPenaltyToggle.addEventListener('change', togglePenaltyFields);
                    togglePenaltyFields(); // Run on load
                }

                if (applyDiscountToggle) {
                    applyDiscountToggle.addEventListener('change', toggleDiscountFields);
                    toggleDiscountFields(); // Run on load
                }

                if (penaltyTypeSelect) {
                    penaltyTypeSelect.addEventListener('change', updatePenaltyLabels);
                    updatePenaltyLabels();
                }

                if (discountTypeSelect) {
                    discountTypeSelect.addEventListener('change', updateDiscountLabels);
                    updateDiscountLabels();
                }
            });
        </script>
    @endpush
</x-user-page>
