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

                        <h5 class="mb-3">General Settings</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Society Name</label>
                                <input type="text" name="society_name" class="form-control"
                                    value="{{ $settings['society_name'] ?? 'My Society' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Society Address</label>
                                <input type="text" name="society_address" class="form-control"
                                    value="{{ $settings['society_address'] ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control"
                                    value="{{ $settings['contact_email'] ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="contact_phone" class="form-control"
                                    value="{{ $settings['contact_phone'] ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Financial Year Start</label>
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


                        <h5 class="mb-3">Late Penalty Settings</h5>
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Apply Late Penalty</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="apply_penalty" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_penalty"
                                        name="apply_penalty" value="1"
                                        {{ ($settings['apply_penalty'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="apply_penalty">Yes, automatically apply penalty
                                        if past due days</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Penalty Type</label>
                                <select name="penalty_type" id="penalty_type" class="form-select">
                                    <option value="percentage"
                                        {{ ($settings['penalty_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="fixed"
                                        {{ ($settings['penalty_type'] ?? 'percentage') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label label-penalty">Monthly Penalty (%)</label>
                                <input type="number" step="0.01" name="penalty_monthly_value" class="form-control"
                                    value="{{ $settings['penalty_monthly_value'] ?? ($settings['penalty_monthly_percent'] ?? '5') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label label-penalty">Quarterly Penalty (%)</label>
                                <input type="number" step="0.01" name="penalty_quarterly_value" class="form-control"
                                    value="{{ $settings['penalty_quarterly_value'] ?? ($settings['penalty_quarterly_percent'] ?? '10') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label label-penalty">Yearly Penalty (%)</label>
                                <input type="number" step="0.01" name="penalty_yearly_value" class="form-control"
                                    value="{{ $settings['penalty_yearly_value'] ?? ($settings['penalty_yearly_percent'] ?? '15') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Penalty apply Days</label>
                                <input type="number" name="penalty_due_days" class="form-control"
                                    value="{{ $settings['penalty_due_days'] ?? '15' }}">
                                <small class="text-muted">Number of days after generation bills of maitenance</small>
                            </div>
                        </div>

                        <h5 class="mb-3">Payment Discount Settings</h5>
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Apply Discount</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="apply_discount" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_discount"
                                        name="apply_discount" value="1"
                                        {{ ($settings['apply_discount'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="apply_discount">Yes, automatically apply
                                        payment discount</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Discount Type</label>
                                <select name="discount_type" id="discount_type" class="form-select">
                                    <option value="percentage"
                                        {{ ($settings['discount_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="fixed"
                                        {{ ($settings['discount_type'] ?? 'percentage') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label label-discount">Monthly Discount (%)</label>
                                <input type="number" step="0.01" name="discount_monthly_value"
                                    class="form-control"
                                    value="{{ $settings['discount_monthly_value'] ?? ($settings['discount_monthly_percent'] ?? '0') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label label-discount">Quarterly Discount (%)</label>
                                <input type="number" step="0.01" name="discount_quarterly_value"
                                    class="form-control"
                                    value="{{ $settings['discount_quarterly_value'] ?? ($settings['discount_quarterly_percent'] ?? '5') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label label-discount">Yearly Discount (%)</label>
                                <input type="number" step="0.01" name="discount_yearly_value"
                                    class="form-control"
                                    value="{{ $settings['discount_yearly_value'] ?? ($settings['discount_yearly_percent'] ?? '10') }}">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
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
                const penaltyInputs = [
                    document.querySelector('input[name="penalty_monthly_percent"]'),
                    document.querySelector('input[name="penalty_quarterly_percent"]'),
                    document.querySelector('input[name="penalty_yearly_percent"]'),
                    document.querySelector('input[name="penalty_due_days"]')
                ];

                const penaltyTypeSelect = document.getElementById('penalty_type');
                const discountTypeSelect = document.getElementById('discount_type');
                const applyDiscountToggle = document.getElementById('apply_discount');
                const discountInputs = [
                    document.querySelector('input[name="discount_monthly_value"]'),
                    document.querySelector('input[name="discount_quarterly_value"]'),
                    document.querySelector('input[name="discount_yearly_value"]')
                ];

                // Ensure we select the new value inputs for penalty
                const penaltyValueInputs = [
                    document.querySelector('input[name="penalty_monthly_value"]'),
                    document.querySelector('input[name="penalty_quarterly_value"]'),
                    document.querySelector('input[name="penalty_yearly_value"]'),
                    document.querySelector('input[name="penalty_due_days"]')
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
                    const suffix = isFixed ? '(₹)' : '(%)';
                    document.querySelectorAll('.label-penalty').forEach((label, index) => {
                        const prefix = ['Monthly', 'Quarterly', 'Yearly'][index];
                        label.innerText = `${prefix} Penalty ${suffix}`;
                    });
                }

                function updateDiscountLabels() {
                    const isFixed = discountTypeSelect.value === 'fixed';
                    const suffix = isFixed ? '(₹)' : '(%)';
                    document.querySelectorAll('.label-discount').forEach((label, index) => {
                        const prefix = ['Monthly', 'Quarterly', 'Yearly'][index];
                        label.innerText = `${prefix} Discount ${suffix}`;
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
