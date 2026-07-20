<x-user-page>
    {{-- Styles consolidated into public/css/style.css --}}

    @php
        $currencySymbol = \App\Helpers\CurrencyHelper::getCurrencySymbol();
        $availableCurrencies = \App\Helpers\CurrencyHelper::getAvailableCurrencies();
        $penaltyRateSuffix = ($settings['penalty_type'] ?? 'percentage') === 'fixed' ? $currencySymbol : '%';
        $discountRateSuffix = ($settings['discount_type'] ?? 'percentage') === 'fixed' ? $currencySymbol : '%';
    @endphp

    <div class="row" id="general-settings">
        <div class="col-12">
            <form action="{{ route('settings.store') }}" method="POST">
                @csrf

                <!-- Card 1: General Settings -->
                <div class="card mb-4 border-0 shadow-sm" id="general-settings">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fa-solid fa-gear text-primary me-2"></i>General Settings</h4>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1 shadow-sm fw-semibold rounded-2" data-module="general-settings"><i class="fa-solid fa-save me-1"></i>Save</button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Society
                                    Name</label>
                                <input type="text" name="society_name" class="form-control"
                                    value="{{ $settings['society_name'] ?? 'My Society' }}">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Society
                                    Address</label>
                                <input type="text" name="society_address" class="form-control"
                                    value="{{ $settings['society_address'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Contact
                                    Email</label>
                                <input type="email" name="contact_email" class="form-control"
                                    value="{{ $settings['contact_email'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Contact
                                    Phone</label>
                                <input type="text" name="contact_phone" class="form-control"
                                    value="{{ $settings['contact_phone'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Financial Year
                                    Start</label>
                                <select name="financial_year_start" class="form-select">
                                    <option value="january_1"
                                        {{ ($settings['financial_year_start'] ?? 'january_1') == 'january_1' ? 'selected' : '' }}>
                                        1st January</option>
                                    <option value="april_1"
                                        {{ ($settings['financial_year_start'] ?? 'january_1') == 'april_1' ? 'selected' : '' }}>
                                        1st April</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Currency</label>
                                <select name="currency" id="currency_select" class="form-select">
                                    @foreach ($availableCurrencies as $code => $details)
                                        <option value="{{ $code }}"
                                            {{ ($settings['currency'] ?? 'INR') == $code ? 'selected' : '' }}>
                                            {{ $code }} - {{ $details['name'] }} ({{ $details['symbol'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Name Transfer Fee
                                    (<span class="currency-symbol-preview">{{ $currencySymbol }}</span>)</label>
                                <input type="number" step="0.01" name="name_transfer_fee" class="form-control"
                                    value="{{ $settings['name_transfer_fee'] ?? '0' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Society GSTIN (Tax
                                    ID)</label>
                                <input type="text" name="society_gstin" class="form-control"
                                    placeholder="e.g. 22AAAAA0000A1Z5" value="{{ $settings['society_gstin'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Society
                                    Registration No.</label>
                                <input type="text" name="society_registration_no" class="form-control"
                                    placeholder="e.g. SOC/REG/2026/001"
                                    value="{{ $settings['society_registration_no'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3 d-flex flex-column justify-content-between">
                                <label class="form-label text-body small fw-semibold text-uppercase">Laravel
                                    Debugger</label>
                                <div class="form-check form-switch m-0 p-2 px-3 bg-body-tertiary rounded border d-flex align-items-center justify-content-between shadow-sm"
                                    style="height: 38px;">
                                    <label class="form-check-label small fw-semibold mb-0" for="enable_debugger"
                                        style="cursor: pointer;">Enable Debug Toolbar</label>
                                    <input type="hidden" name="enable_debugger" value="0">
                                    <input class="form-check-input m-0" type="checkbox" id="enable_debugger"
                                        name="enable_debugger" value="1" style="cursor: pointer;"
                                        {{ ($settings['enable_debugger'] ?? '0') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Administration Hub -->
                <div class="card mb-4 border-0 shadow-sm" id="database-administration">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-server text-danger me-2"></i>Database Administration</h4>
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill small">System Level</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-sm rounded-4 p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3 me-3">
                                            <i class="fa-solid fa-database fa-xl"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1 text-body">Full SQL Backup</h5>
                                            <p class="text-body small mb-0">Download complete database structure and data (.sql)</p>
                                        </div>
                                    </div>
                                    <hr class="text-body opacity-25 mb-4">
                                    <p class="text-muted small mb-4">
                                        This will generate a complete <code>.sql</code> dump of the current database. This is a system-heavy operation, please do not run it during peak hours.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('settings.database_backup') }}" class="btn btn-danger w-100 fw-bold py-2 rounded-3 shadow-sm">
                                            <i class="fa-solid fa-download me-2"></i>Download SQL Backup
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 1.5: Property & Structure Configuration (SMP 2.0) -->
                <div class="card mb-4 border-0 shadow-sm" id="structure-settings">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fa-solid fa-city text-primary me-2"></i>Property & Structure
                            Configuration</h4>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1 shadow-sm fw-semibold rounded-2" data-module="structure-settings"><i class="fa-solid fa-save me-1"></i>Save</button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label text-body small fw-semibold text-uppercase mb-0">Society
                                        Structure Type</label>
                                    <button type="button" class="btn btn-sm btn-link py-0 text-decoration-none"
                                        data-coreui-toggle="modal" data-coreui-target="#managePropertyTypesModal">
                                        <i class="fa-solid fa-gear me-1"></i>Manage Types
                                    </button>
                                </div>
                                <select name="society_property_type" id="society_property_type_select"
                                    class="form-select">
                                    @if (isset($propertyTypes) && $propertyTypes->count() > 0)
                                        @foreach ($propertyTypes as $pt)
                                            <option value="{{ $pt->code }}"
                                                {{ ($settings['society_property_type'] ?? 'flat_residential') == $pt->code ? 'selected' : '' }}>
                                                {{ $pt->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="flat_residential"
                                            {{ ($settings['society_property_type'] ?? 'flat_residential') == 'flat_residential' ? 'selected' : '' }}>
                                            Flat Residential Society (Vertical Towers)</option>
                                        <option value="commercial_complex"
                                            {{ ($settings['society_property_type'] ?? 'flat_residential') == 'commercial_complex' ? 'selected' : '' }}>
                                            Commercial Shopping Complex / Arcade / IT Park</option>
                                        <option value="rowhouse_villa"
                                            {{ ($settings['society_property_type'] ?? 'flat_residential') == 'rowhouse_villa' ? 'selected' : '' }}>
                                            Bungalows / Villas / Tenements / Row Houses</option>
                                        <option value="mixed_use"
                                            {{ ($settings['society_property_type'] ?? 'flat_residential') == 'mixed_use' ? 'selected' : '' }}>
                                            Mixed-Use (Flats + Commercial Shops + Villas)</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Billing
                                    Calculation Method</label>
                                <select name="maintenance_billing_method" id="maintenance_billing_method_select"
                                    class="form-select">
                                    <option value="fixed"
                                        {{ ($settings['maintenance_billing_method'] ?? 'fixed') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Rate per Unit / Flat (Standard)</option>
                                    <option value="per_sqft"
                                        {{ ($settings['maintenance_billing_method'] ?? 'fixed') == 'per_sqft' ? 'selected' : '' }}>
                                        Carpet Area Based (Per Sq. Ft. x Area)</option>
                                    <option value="both" id="billing_method_both_option"
                                        class="{{ ($settings['society_property_type'] ?? 'flat_residential') === 'mixed_use' ? '' : 'd-none' }}"
                                        style="{{ ($settings['society_property_type'] ?? 'flat_residential') === 'mixed_use' ? '' : 'display: none;' }}"
                                        {{ ($settings['society_property_type'] ?? 'flat_residential') !== 'mixed_use' ? 'disabled hidden' : '' }}
                                        {{ ($settings['maintenance_billing_method'] ?? 'fixed') == 'both' ? 'selected' : '' }}>
                                        Both (Hybrid: Fixed / Sq. Ft. per Category)</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 {{ ($settings['maintenance_billing_method'] ?? 'fixed') == 'fixed' && !in_array($settings['society_property_type'] ?? 'flat_residential', ['mixed_use', 'commercial_complex']) ? 'd-none' : '' }}"
                                id="maintenance_rate_per_sqft_wrapper" style="{{ ($settings['maintenance_billing_method'] ?? 'fixed') == 'fixed' && !in_array($settings['society_property_type'] ?? 'flat_residential', ['mixed_use', 'commercial_complex']) ? 'display: none;' : '' }}">
                                <label class="form-label text-body small fw-semibold text-uppercase">Per Sq. Ft. Rate
                                    (₹) <small class="text-muted fw-normal" style="font-size: 0.75rem;">(Commercial / Area Based)</small></label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() }}</span>
                                    <input type="number" step="0.01" min="0"
                                        name="maintenance_rate_per_sqft" id="maintenance_rate_per_sqft_input"
                                        class="form-control"
                                        value="{{ $settings['maintenance_rate_per_sqft'] ?? ($settings['commercial_rate_per_sqft'] ?? '0') }}"
                                        placeholder="e.g. 2.50"
                                        oninput="document.getElementById('commercial_rate_per_sqft_input').value = this.value;">
                                    <input type="hidden" name="commercial_rate_per_sqft" id="commercial_rate_per_sqft_input" value="{{ $settings['commercial_rate_per_sqft'] ?? ($settings['maintenance_rate_per_sqft'] ?? '0') }}">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Block/Group
                                    Vocabulary</label>
                                <input type="text" name="ui_label_block" class="form-control"
                                    value="{{ \App\Models\Setting::label('block', 'Block/Wing') }}"
                                    placeholder="e.g., Wing, Tower, Sector, Phase">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Unit Vocabulary
                                    (Singular)</label>
                                <input type="text" name="ui_label_unit" class="form-control"
                                    value="{{ \App\Models\Setting::label('unit', 'Flat') }}"
                                    placeholder="e.g., Flat, Shop, Villa, Unit">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Occupant
                                    Vocabulary</label>
                                <input type="text" name="ui_label_resident" class="form-control"
                                    value="{{ \App\Models\Setting::label('resident', 'Resident') }}"
                                    placeholder="e.g., Resident, Occupant, Owner / Occupant">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Late Penalty Settings -->
                <div class="card mb-4 border-0 shadow-sm" id="penalty-settings">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fa-solid fa-clock-rotate-left text-danger me-2"></i>Late Penalty
                            Settings</h4>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1 shadow-sm fw-semibold rounded-2" data-module="penalty-settings"><i class="fa-solid fa-save me-1"></i>Save</button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div
                                    class="form-check form-switch m-0 p-3 bg-body-tertiary rounded border d-flex align-items-center justify-content-between shadow-sm">
                                    <div>
                                        <label class="form-check-label fw-bold mb-1 text-body d-block"
                                            for="apply_penalty" style="cursor: pointer; font-size: 1rem;">Allow Late
                                            Fees Penalty</label>
                                        <div class="small text-muted">Automatically apply late penalty charges to
                                            unpaid invoices after grace period</div>
                                    </div>
                                    <input type="hidden" name="apply_penalty" value="0">
                                    <input class="form-check-input m-0" type="checkbox" id="apply_penalty"
                                        name="apply_penalty" value="1"
                                        style="cursor: pointer; transform: scale(1.3);"
                                        {{ ($settings['apply_penalty'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Penalty Charge
                                    Type</label>
                                <select name="penalty_type" id="penalty_type" class="form-select">
                                    <option value="percentage"
                                        {{ ($settings['penalty_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="fixed"
                                        {{ ($settings['penalty_type'] ?? 'percentage') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount ({{ $currencySymbol }})</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Penalty Grace
                                    Days</label>
                                <div class="input-group">
                                    <input type="number" name="penalty_due_days" class="form-control"
                                        value="{{ $settings['penalty_due_days'] ?? '15' }}">
                                    <span class="input-group-text text-body">days after due date</span>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_monthly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="penalty_monthly_enabled"
                                        name="penalty_monthly_enabled" value="1"
                                        {{ ($settings['penalty_monthly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-penalty"
                                        for="penalty_monthly_enabled">Monthly (1 Month)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_monthly_value"
                                        class="form-control" value="{{ $settings['penalty_monthly_value'] }}">
                                    <span class="input-group-text penalty-suffix">{{ $penaltyRateSuffix }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_quarterly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="penalty_quarterly_enabled"
                                        name="penalty_quarterly_enabled" value="1"
                                        {{ ($settings['penalty_quarterly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-penalty"
                                        for="penalty_quarterly_enabled">Quarterly (3 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_quarterly_value"
                                        class="form-control" value="{{ $settings['penalty_quarterly_value'] }}">
                                    <span class="input-group-text penalty-suffix">{{ $penaltyRateSuffix }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_half_yearly_enabled" value="0">
                                        <input class="form-check-input" type="checkbox" id="penalty_half_yearly_enabled"
                                        name="penalty_half_yearly_enabled" value="1"
                                        {{ ($settings['penalty_half_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-penalty"
                                        for="penalty_half_yearly_enabled">Half-Yearly (6 Months)</label>
                                </div>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="penalty_half_yearly_value"
                                            class="form-control" value="{{ $settings['penalty_half_yearly_value'] }}">
                                        <span class="input-group-text penalty-suffix">{{ $penaltyRateSuffix }}</span>
                                    </div>
                                </div>
                            <div class="col-md-3 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_yearly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="penalty_yearly_enabled"
                                        name="penalty_yearly_enabled" value="1"
                                        {{ ($settings['penalty_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-penalty"
                                        for="penalty_yearly_enabled">Yearly (12 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_yearly_value"
                                        class="form-control" value="{{ $settings['penalty_yearly_value'] }}">
                                    <span class="input-group-text penalty-suffix">{{ $penaltyRateSuffix }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Prepayment Discount Settings -->
                <div class="card mb-4 border-0 shadow-sm" id="discount-settings">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fa-solid fa-tag text-success me-2"></i>Prepayment Discount
                            Settings</h4>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1 shadow-sm fw-semibold rounded-2" data-module="discount-settings"><i class="fa-solid fa-save me-1"></i>Save</button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div
                                    class="form-check form-switch m-0 p-3 bg-body-tertiary rounded border d-flex align-items-center justify-content-between shadow-sm">
                                    <div>
                                        <label class="form-check-label fw-bold mb-1 text-body d-block"
                                            for="apply_discount" style="cursor: pointer; font-size: 1rem;">Allow
                                            Prepayment Discount</label>
                                        <div class="small text-muted">Automatically apply discounts when residents pay
                                            their maintenance bills in advance</div>
                                    </div>
                                    <input type="hidden" name="apply_discount" value="0">
                                    <input class="form-check-input m-0" type="checkbox" id="apply_discount"
                                        name="apply_discount" value="1"
                                        style="cursor: pointer; transform: scale(1.3);"
                                        {{ ($settings['apply_discount'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Discount Charge
                                    Type</label>
                                <select name="discount_type" id="discount_type" class="form-select">
                                    <option value="percentage"
                                        {{ ($settings['discount_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="fixed"
                                        {{ ($settings['discount_type'] ?? 'percentage') == 'fixed' ? 'selected' : '' }}>
                                        Fixed Amount ({{ $currencySymbol }})</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_quarterly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_quarterly_enabled"
                                        name="discount_quarterly_enabled" value="1"
                                        {{ ($settings['discount_quarterly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-discount"
                                        for="discount_quarterly_enabled">Quarterly (3 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_quarterly_value"
                                        class="form-control" value="{{ $settings['discount_quarterly_value'] }}">
                                    <span class="input-group-text discount-suffix">{{ $discountRateSuffix }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_half_yearly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_half_yearly_enabled"
                                        name="discount_half_yearly_enabled" value="1"
                                        {{ ($settings['discount_half_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-discount"
                                        for="discount_half_yearly_enabled">Half-Yearly (6 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_half_yearly_value"
                                        class="form-control" value="{{ $settings['discount_half_yearly_value'] }}">
                                    <span class="input-group-text discount-suffix">{{ $discountRateSuffix }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_yearly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_yearly_enabled"
                                        name="discount_yearly_enabled" value="1"
                                        {{ ($settings['discount_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-discount"
                                        for="discount_yearly_enabled">Yearly (12 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_yearly_value"
                                        class="form-control" value="{{ $settings['discount_yearly_value'] }}">
                                    <span class="input-group-text discount-suffix">{{ $discountRateSuffix }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Required Documents Settings -->
                <div class="card mb-4 border-0 shadow-sm" id="documents-settings">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fa-solid fa-folder-open text-warning me-2"></i>Required Documents
                            Settings</h4>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1 shadow-sm fw-semibold rounded-2" data-module="documents-settings"><i class="fa-solid fa-save me-1"></i>Save</button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-4 border-bottom pb-4">
                            <div class="col-md-6">
                                <label for="max_document_size" class="form-label text-muted small fw-semibold text-uppercase">Max Document Size (in MB)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0.1" name="max_document_size" id="max_document_size" class="form-control" value="{{ $settings['max_document_size'] ?? 2 }}" required>
                                    <span class="input-group-text">MB</span>
                                </div>
                                <small class="text-muted">Maximum allowed file size for all document uploads (e.g., Aadhar, PAN, payment slips).</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold">Required Documents for Owner</h5>
                            <div>
                                <button type="button"
                                    class="btn btn-sm btn-danger text-white fw-semibold px-3 py-1 rounded-pill set-all-select-btn shadow-sm"
                                    data-target="req_doc_owner_" data-val="1"><i
                                        class="fa-solid fa-circle-exclamation me-1"></i> Set All Required</button>
                                <button type="button"
                                    class="btn btn-sm btn-warning text-dark fw-semibold px-3 py-1 rounded-pill set-all-select-btn ms-1 shadow-sm"
                                    data-target="req_doc_owner_" data-val="2"><i
                                        class="fa-solid fa-circle-question me-1"></i> Set All Optional</button>
                                <button type="button"
                                    class="btn btn-sm btn-secondary text-white fw-semibold px-3 py-1 rounded-pill set-all-select-btn ms-1 shadow-sm"
                                    data-target="req_doc_owner_" data-val="0"><i
                                        class="fa-solid fa-eye-slash me-1"></i> Disable All</button>
                            </div>
                        </div>
                        <div class="row mb-5">
                            @php
                                $ownerDocs = [
                                    'passport_photo' => 'Passport Size Photo',
                                    'adhar_card' => 'Aadhar Card',
                                    'pan_card' => 'PAN Card',
                                    'index_copy' => 'Index Copy',
                                    'possession_letter' => 'Possession Letter',
                                    'tax_bill' => 'Copy of Tax Bill',
                                ];
                            @endphp
                            @foreach ($ownerDocs as $key => $label)
                                @php
                                    $currentVal = $settings['req_doc_owner_' . $key] ?? '1';
                                    $isEnabled = $currentVal != '0';
                                    $optVal = $currentVal == '2' ? '2' : '1';
                                @endphp
                                <div class="col-md-4 mb-3">
                                    <div class="doc-setting-card h-100 d-flex flex-column justify-content-between transition-all"
                                        data-setting-key="req_doc_owner_{{ $key }}">
                                        <input type="hidden" name="req_doc_owner_{{ $key }}"
                                            class="real-setting-input" value="{{ $currentVal }}">

                                        <div
                                            class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                            <label class="form-label doc-title-label fw-bold mb-0 cursor-pointer"
                                                for="toggle_owner_{{ $key }}">{{ $label }}</label>
                                            <div class="form-check form-switch m-0 fs-5">
                                                <input class="form-check-input doc-enable-toggle cursor-pointer m-0"
                                                    type="checkbox" role="switch"
                                                    id="toggle_owner_{{ $key }}"
                                                    {{ $isEnabled ? 'checked' : '' }}
                                                    title="Enable or Disable {{ $label }}">
                                            </div>
                                        </div>

                                        <div class="doc-options-container mt-1 transition-all"
                                            style="{{ $isEnabled ? '' : 'opacity: 0.35; pointer-events: none;' }}">
                                            <label
                                                class="text-muted doc-req-label small fw-semibold d-block mb-1">Requirement
                                                Type:</label>
                                            <select
                                                class="form-select form-select-sm border shadow-sm doc-type-select fw-medium"
                                                {{ $isEnabled ? '' : 'disabled' }}>
                                                <option value="1" {{ $optVal == '1' ? 'selected' : '' }}>🔴
                                                    Required (Mandatory)</option>
                                                <option value="2" {{ $optVal == '2' ? 'selected' : '' }}>🟡
                                                    Optional (If Available)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr class="mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold">Required Documents for Rental</h5>
                            <div>
                                <button type="button"
                                    class="btn btn-sm btn-danger text-white fw-semibold px-3 py-1 rounded-pill set-all-select-btn shadow-sm"
                                    data-target="req_doc_rental_" data-val="1"><i
                                        class="fa-solid fa-circle-exclamation me-1"></i> Set All Required</button>
                                <button type="button"
                                    class="btn btn-sm btn-warning text-dark fw-semibold px-3 py-1 rounded-pill set-all-select-btn ms-1 shadow-sm"
                                    data-target="req_doc_rental_" data-val="2"><i
                                        class="fa-solid fa-circle-question me-1"></i> Set All Optional</button>
                                <button type="button"
                                    class="btn btn-sm btn-secondary text-white fw-semibold px-3 py-1 rounded-pill set-all-select-btn ms-1 shadow-sm"
                                    data-target="req_doc_rental_" data-val="0"><i
                                        class="fa-solid fa-eye-slash me-1"></i> Disable All</button>
                            </div>
                        </div>
                        <div class="row mb-4">
                            @php
                                $rentalDocs = [
                                    'passport_photo' => 'Passport Size Photo',
                                    'adhar_card' => 'Aadhar Card',
                                    'pan_card' => 'PAN Card',
                                    'rent_agreement' => 'Rent Agreement',
                                    'police_verification' => 'Police Verification',
                                    'permanent_address_proof' => 'Permanent Address Proof',
                                ];
                            @endphp
                            @foreach ($rentalDocs as $key => $label)
                                @php
                                    $currentVal = $settings['req_doc_rental_' . $key] ?? '1';
                                    $isEnabled = $currentVal != '0';
                                    $optVal = $currentVal == '2' ? '2' : '1';
                                @endphp
                                <div class="col-md-4 mb-3">
                                    <div class="doc-setting-card h-100 d-flex flex-column justify-content-between transition-all"
                                        data-setting-key="req_doc_rental_{{ $key }}">
                                        <input type="hidden" name="req_doc_rental_{{ $key }}"
                                            class="real-setting-input" value="{{ $currentVal }}">

                                        <div
                                            class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                            <label class="form-label doc-title-label fw-bold mb-0 cursor-pointer"
                                                for="toggle_rental_{{ $key }}">{{ $label }}</label>
                                            <div class="form-check form-switch m-0 fs-5">
                                                <input class="form-check-input doc-enable-toggle cursor-pointer m-0"
                                                    type="checkbox" role="switch"
                                                    id="toggle_rental_{{ $key }}"
                                                    {{ $isEnabled ? 'checked' : '' }}
                                                    title="Enable or Disable {{ $label }}">
                                            </div>
                                        </div>

                                        <div class="doc-options-container mt-1 transition-all"
                                            style="{{ $isEnabled ? '' : 'opacity: 0.35; pointer-events: none;' }}">
                                            <label
                                                class="text-muted doc-req-label small fw-semibold d-block mb-1">Requirement
                                                Type:</label>
                                            <select
                                                class="form-select form-select-sm border shadow-sm doc-type-select fw-medium"
                                                {{ $isEnabled ? '' : 'disabled' }}>
                                                <option value="1" {{ $optVal == '1' ? 'selected' : '' }}>🔴
                                                    Required (Mandatory)</option>
                                                <option value="2" {{ $optVal == '2' ? 'selected' : '' }}>🟡
                                                    Optional (If Available)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Card 5: Toaster & Alert Notification Settings -->
                <div class="card mb-4 border-0 shadow-sm" id="toaster-settings">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fa-solid fa-bell text-primary me-2"></i>Toaster & Alert
                            Notification Settings</h4>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1 shadow-sm fw-semibold rounded-2" data-module="toaster-settings"><i class="fa-solid fa-save me-1"></i>Save</button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3 mb-3 d-flex flex-column justify-content-between">
                                <label class="form-label text-body small fw-semibold text-uppercase">Toaster
                                    Popups</label>
                                <div class="form-check form-switch m-0 p-2 px-3 bg-body-tertiary rounded border d-flex align-items-center justify-content-between shadow-sm"
                                    style="height: 38px;">
                                    <label class="form-check-label small fw-semibold mb-0" for="toastr_enabled"
                                        style="cursor: pointer;">Enable Popups</label>
                                    <input type="hidden" name="toastr_enabled" value="0">
                                    <input class="form-check-input m-0" type="checkbox" id="toastr_enabled"
                                        name="toastr_enabled" value="1" style="cursor: pointer;"
                                        {{ ($settings['toastr_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Popup
                                    Position</label>
                                <select name="toastr_position" class="form-select border shadow-sm fw-medium">
                                    <option value="toast-top-right"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-top-right' ? 'selected' : '' }}>
                                        Top Right</option>
                                    <option value="toast-top-left"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-top-left' ? 'selected' : '' }}>
                                        Top Left</option>
                                    <option value="toast-top-center"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-top-center' ? 'selected' : '' }}>
                                        Top Center</option>
                                    <option value="toast-bottom-right"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-bottom-right' ? 'selected' : '' }}>
                                        Bottom Right</option>
                                    <option value="toast-bottom-left"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-bottom-left' ? 'selected' : '' }}>
                                        Bottom Left</option>
                                    <option value="toast-bottom-center"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-bottom-center' ? 'selected' : '' }}>
                                        Bottom Center</option>
                                    <option value="toast-top-full-width"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-top-full-width' ? 'selected' : '' }}>
                                        Top Full Width</option>
                                    <option value="toast-bottom-full-width"
                                        {{ ($settings['toastr_position'] ?? 'toast-top-right') == 'toast-bottom-full-width' ? 'selected' : '' }}>
                                        Bottom Full Width</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">Duration
                                    (ms)</label>
                                <input type="number" step="500" min="0" name="toastr_timeout"
                                    class="form-control border shadow-sm fw-medium"
                                    value="{{ $settings['toastr_timeout'] ?? '3000' }}" placeholder="3000">
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">0 = Sticky (No Auto
                                    Close)</small>
                            </div>
                            <div class="col-md-2 mb-3 d-flex flex-column justify-content-between">
                                <label class="form-label text-body small fw-semibold text-uppercase">Close
                                    Button</label>
                                <div class="form-check form-switch m-0 p-2 px-3 bg-body-tertiary rounded border d-flex align-items-center justify-content-between shadow-sm"
                                    style="height: 38px;">
                                    <label class="form-check-label small fw-semibold mb-0" for="toastr_close_button"
                                        style="cursor: pointer;">Show (X)</label>
                                    <input type="hidden" name="toastr_close_button" value="0">
                                    <input class="form-check-input m-0" type="checkbox" id="toastr_close_button"
                                        name="toastr_close_button" value="1" style="cursor: pointer;"
                                        {{ ($settings['toastr_close_button'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3 d-flex flex-column justify-content-between">
                                <label class="form-label text-body small fw-semibold text-uppercase">Progress
                                    Bar</label>
                                <div class="form-check form-switch m-0 p-2 px-3 bg-body-tertiary rounded border d-flex align-items-center justify-content-between shadow-sm"
                                    style="height: 38px;">
                                    <label class="form-check-label small fw-semibold mb-0" for="toastr_progress_bar"
                                        style="cursor: pointer;">Show Bar</label>
                                    <input type="hidden" name="toastr_progress_bar" value="0">
                                    <input class="form-check-input m-0" type="checkbox" id="toastr_progress_bar"
                                        name="toastr_progress_bar" value="1" style="cursor: pointer;"
                                        {{ ($settings['toastr_progress_bar'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Society Location Settings -->
    <div class="row mt-4" id="location-settings">
        <div class="col-12">
            <form action="{{ route('settings.store') }}" method="POST" id="society-location-form">
                @csrf
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Society Location
                            Setting</h4>
                        <button type="submit" class="btn btn-sm btn-primary px-3 py-1 shadow-sm fw-semibold rounded-2" data-module="location-settings"><i class="fa-solid fa-save me-1"></i>Save</button>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-body small mb-4">
                            Search your location or click anywhere on the interactive map below to set your society's
                            exact GPS coordinates. The address bar will automatically update as you move the pin.
                        </p>

                        <input type="hidden" id="society_latitude" name="society_latitude"
                            value="{{ $settings['society_latitude'] ?? '19.0760' }}">
                        <input type="hidden" id="society_longitude" name="society_longitude"
                            value="{{ $settings['society_longitude'] ?? '72.8777' }}">

                        <div class="row mb-4">
                            <div class="col-md-8 mb-3 mb-md-0">
                                <label class="form-label small fw-semibold text-uppercase">Search Location /
                                    Address</label>
                                <div class="input-group">
                                    <input type="text" id="map_search_input" name="society_map_address"
                                        class="form-control"
                                        value="{{ $settings['society_map_address'] ?? 'Mumbai, Maharashtra, India' }}"
                                        placeholder="e.g. Bandra West, Mumbai or Society Name, City"
                                        autocomplete="off">
                                    <button class="btn btn-primary" type="button"
                                        id="btn_search_location">Search</button>
                                </div>
                                <div id="search_results_list"
                                    class="list-group position-absolute w-100 shadow-sm d-none"
                                    style="z-index: 1050; max-height: 220px; overflow-y: auto;"></div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary w-100"
                                    id="btn_get_my_current_location">
                                    <i class="fa-solid fa-location-crosshairs me-1"></i> GPS Auto-Detect
                                </button>
                            </div>
                        </div>

                        <div class="position-relative mb-2">
                            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
                            <div id="society_location_map"
                                style="height: 420px; width: 100%; border-radius: 12px; z-index: 1;"
                                class="shadow-sm border"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mt-2" id="role-settings">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Role and Permission Setting</h4>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('roles.store') }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label for="role-name" class="form-label fw-semibold">Add New Role</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="role-name" name="name" value="{{ old('name') }}"
                                placeholder="Enter role name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary text-white fw-medium w-100">Add
                                Role</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Role Cards -->
            <div class="row mb-4">
                @foreach ($roles as $role)
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100 role-card" style="cursor: pointer;"
                            data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}"
                            data-role-permissions='@json($role->permissions->pluck('name')->values())' onclick="selectRole(this, event)">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0 fw-bold border-start border-primary border-4 ps-2">
                                        {{ $role->name }}</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-body p-0" type="button"
                                            data-coreui-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('roles.edit', $role->id) }}">Edit Role Name</a>
                                            </li>
                                            <li>
                                                <button type="button"
                                                    class="dropdown-item text-danger btn-delete-role"
                                                    data-url="{{ route('roles.destroy', $role->id) }}"
                                                    data-role-name="{{ $role->name }}">
                                                    Delete Role
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="text-body small">Permission</span>
                                    <span class="text-primary small fw-medium">View</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-body small">Member :</span>
                                    <span class="fw-bold small">{{ $role->users_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach 
            </div>

            <!-- Permissions Table -->
            <div class="card border-0 shadow-sm mb-5" id="permissions-container" style="display: none;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Module Permissions</h4>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" id="global-select-all-permissions-btn" style="font-size: 0.8rem;">
                            <i class="fa-solid fa-check-double me-1"></i>Select All Permissions
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <form id="role-permissions-form" action="" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" id="selected-role-name" value="">

                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3 border-0 text-body">Module</th>
                                        <th class="py-3 border-0 text-body">Permissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissionsByModule as $moduleName => $permissions)
                                        <tr class="module-permission-row">
                                            <td class="ps-4 align-middle fw-semibold text-body" style="width: 270px;">
                                                <div class="d-flex flex-column align-items-start gap-1 py-1">
                                                    <span class="fs-6">{{ $moduleName ?? 'General' }}</span>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 module-select-all-btn shadow-sm"
                                                        style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-check-double me-1"></i>Select All
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-4 py-2">
                                                    @foreach ($permissions as $permission)
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-checkbox"
                                                                type="checkbox" name="permissions[]"
                                                                value="{{ $permission }}"
                                                                id="perm_{{ Str::slug($permission) }}">
                                                            <label class="form-check-label text-body"
                                                                for="perm_{{ Str::slug($permission) }}">
                                                                {{ ucwords(str_replace(['_', '-'], ' ', $permission)) }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-end py-3 border-0">
                            <button type="submit" class="btn btn-primary fw-medium px-4">Update Permissions</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-5 text-center py-5" id="no-role-selected">
                <div class="card-body py-5">
                    <i class="fa-solid fa-list-check fa-3x text-body mb-3" style="opacity: 0.1;"></i>
                    <p class="text-body fw-medium mb-0">Select a role to assign permissions</p>
                </div>
            </div>

            <!-- Global Import Export Hub -->
            <div class="row mt-4" id="global-import-export">
                <div class="col-12">
                    <div class="card mb-5 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-database text-primary me-2"></i>Global
                                Import Export</h4>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small">All
                                Modules Engine</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <!-- Export Panel -->
                                <div class="col-md-6">
                                    <div class="card h-100 border shadow-sm rounded-4 p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                                <i class="fa-solid fa-file-export fa-xl"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-1 text-body">Export Records</h5>
                                                <p class="text-body small mb-0">Download database records into Excel
                                                    (.xlsx)</p>
                                            </div>
                                        </div>
                                        <hr class="text-body opacity-25 mb-4">

                                        <form id="global-export-form"
                                            action="{{ route('settings.global.export_master') }}" method="GET">
                                            <input type="hidden" name="format" value="excel">

                                            <div id="export_master_container" class="mb-4">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label
                                                        class="form-label small fw-bold text-uppercase text-body mb-0">Select
                                                        Tables to Include</label>
                                                    <div class="form-check form-check-sm mb-0">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="export_master_select_all" checked
                                                            style="cursor: pointer;">
                                                        <label class="form-check-label small fw-bold text-primary"
                                                            for="export_master_select_all"
                                                            style="cursor: pointer;">Select All</label>
                                                    </div>
                                                </div>
                                                <div class="row g-2 border border-secondary border-opacity-25 rounded p-3"
                                                    style="max-height: 220px; overflow-y: auto; background: rgba(0,0,0,0.15);">
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="blocks"
                                                                id="em_blocks" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_blocks">Blocks</label></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="flat_types"
                                                                id="em_flat_types" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_flat_types">{{ \App\Models\Setting::label('unit_types', 'Flat Types') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="flats"
                                                                id="em_flats" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_flats">Flats</label></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="users"
                                                                id="em_users" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_users">Staff & Users</label></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="residents"
                                                                id="em_residents" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_residents">Residents</label></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]"
                                                                value="expense_categories" id="em_expense_categories"
                                                                checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_expense_categories">Expense Categories</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="expenses"
                                                                id="em_expenses" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_expenses">Expenses</label></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="complaints"
                                                                id="em_complaints" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_complaints">Complaints</label></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]" value="maintenances"
                                                                id="em_maintenances" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_maintenances">Maintenance Batches</label></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]"
                                                                value="maintenance_bills" id="em_maintenance_bills"
                                                                checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_maintenance_bills">Maintenance Bills</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check small"><input
                                                                class="form-check-input export-master-chk"
                                                                type="checkbox" name="tables[]"
                                                                value="name_transfer_bills"
                                                                id="em_name_transfer_bills" checked><label
                                                                class="form-check-label text-body fw-medium"
                                                                for="em_name_transfer_bills">Transfer Fees</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-auto pt-2">
                                                <button type="submit" id="btn_submit_export"
                                                    class="btn btn-primary btn-lg w-100 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2 py-3">
                                                    <i class="fa-solid fa-cloud-arrow-down"></i> <span
                                                        id="export_btn_text">Export Master Excel (All Data)</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Import Panel -->
                                <div class="col-md-6">
                                    <div
                                        class="card h-100 border shadow-sm rounded-4 p-4 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                                                        <i class="fa-solid fa-file-import fa-xl"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="fw-bold mb-1 text-body">Bulk Import</h5>
                                                        <p class="text-body small mb-0">Upload Excel to restore
                                                            database in bulk</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="text-body opacity-25 mb-4">
                                            <p class="text-body small mb-4">
                                                Upload an Excel (.xlsx) Master backup workbook to bulk restore database
                                                records across Blocks, Flats, Residents, Staff, Expenses, Complaints,
                                                and Settings.
                                            </p>
                                        </div>

                                        <div class="mt-auto pt-2">
                                            <button type="button"
                                                class="btn btn-primary btn-lg w-100 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2 py-3"
                                                data-coreui-toggle="modal" data-coreui-target="#master-import-modal">
                                                <i class="fa-solid fa-file-import"></i> <span>Global Bulk Import</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Import Preview & Mapping Modal -->
            <div class="modal fade" id="globalImportPreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-success text-white py-3">
                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-table-columns me-2"></i>Map
                                Spreadsheet Columns (<span id="modal_import_table_name"></span>)</h5>
                            <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info border-0 shadow-sm small mb-4 d-flex align-items-center">
                                <i class="fa-solid fa-circle-info fa-xl me-3 text-info"></i>
                                <div>
                                    Map your Excel sheet columns to the database fields below. Required fields are
                                    marked with <strong>(*)</strong>.<br>
                                    <em>Note: Any duplicates or conflicts will stop import and display exact line
                                        errors.</em>
                                </div>
                            </div>

                            <h6 class="fw-bold text-body mb-3">1. Column Field Mapping</h6>
                            <div class="row g-3 mb-4" id="global_mapping_container"></div>

                            <h6 class="fw-bold text-body mb-3">2. Data Preview (First 5 Rows)</h6>
                            <div class="table-responsive border rounded shadow-sm"
                                style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-bordered table-striped table-hover mb-0 small"
                                    id="global_preview_table">
                                    <thead class="table-light sticky-top" id="global_preview_thead"></thead>
                                    <tbody id="global_preview_tbody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3">
                            <input type="hidden" id="global_temp_file_path">
                            <input type="hidden" id="global_target_table">
                            <button type="button" class="btn btn-secondary px-4 fw-medium"
                                data-coreui-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success px-5 fw-bold text-white shadow-sm"
                                id="btn_process_global_import">
                                <i class="fa-solid fa-check me-1"></i> Start Import
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Master All-in-One Import Modal (3-Step Resident Style) -->
            <div class="modal fade" id="master-import-modal" tabindex="-1" aria-labelledby="masterImportModalLabel"
                aria-hidden="true" data-coreui-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="masterImportModalLabel">Global Bulk Import</h5>
                            <button type="button" class="btn-close" data-coreui-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <!-- Step 1: Upload File -->
                        <div id="master-import-step-1">
                            <form id="master-import-preview-form" onsubmit="return false;">
                                <div class="modal-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p class="mb-0">Upload an Excel (.xlsx) file to bulk import global records.
                                        </p>
                                        <a href="{{ route('settings.global.template_master') }}"
                                            class="btn btn-sm btn-light rounded-pill text-primary fw-semibold border shadow-sm">
                                            <i class="fa-solid fa-download me-1"></i>Download Template
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <div class="drag-drop-zone border border-2 border-dashed rounded p-4 text-center position-relative"
                                            id="master-drag-drop-zone"
                                            style="background-color: #f8f9fa; cursor: pointer; transition: all 0.3s ease;">
                                            <input type="file"
                                                class="position-absolute w-100 h-100 top-0 start-0 opacity-0 no-dropify"
                                                id="global_import_file" accept=".xlsx, .xls, .csv"
                                                style="cursor: pointer;">
                                            <i class="fa-solid fa-file-excel text-success mb-2"
                                                style="font-size: 3rem;"></i>
                                            <h6 class="mb-1 text-dark fw-bold" id="master-drag-drop-text">Drag & Drop
                                                your Excel file here</h6>
                                            <p class="text-body small mb-0" id="master-drag-drop-subtext">or click to
                                                browse</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0">
                                    <button type="button" class="btn btn-secondary"
                                        data-coreui-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary bg-gradient border-0"
                                        id="btn_preview_global_import">
                                        <i class="fa-solid fa-eye me-2"></i>Preview Data
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Step 2: Preview Sheets & Records -->
                        <div id="master-import-step-2" class="d-none">
                            <div class="modal-body">
                                <div class="alert alert-info mb-3">
                                    Below is a preview of valid data found in your Excel file. Click "Process Import" to
                                    validate and insert these records across all modules.
                                </div>
                                <input type="hidden" id="master_import_file_path">
                                <div id="master_sheets_summary_container"></div>
                            </div>
                            <div class="modal-footer border-top-0">
                                <button type="button" class="btn btn-secondary" id="btn_master_back_to_step_1"><i
                                        class="fa-solid fa-arrow-left me-2"></i>Back</button>
                                <button type="button" class="btn btn-primary bg-gradient border-0"
                                    id="btn_process_master_import">
                                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Process Import
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Execution Summary & Errors -->
                        <div id="master-import-step-3" class="d-none">
                            <div class="modal-body">
                                <div class="alert alert-success d-none mb-3" id="master-import-success-alert"></div>
                                <div class="alert alert-danger d-none mb-3" id="master-import-error-alert"></div>
                                <div id="master-import-summary-container" class="mb-3 text-center">
                                    <h5 class="fw-bold"><span id="master-import-success-count"
                                            class="text-success">0</span> record(s) imported, <span
                                            id="master-import-failed-count" class="text-danger">0</span> failed</h5>
                                </div>

                                <div id="master_import_failure_container" class="d-none">
                                    <h6 class="fw-bold text-danger mb-2">Failed Records Details:</h6>
                                    <div class="table-responsive border rounded" style="max-height: 300px;">
                                        <table class="table table-sm table-hover table-striped mb-0"
                                            style="font-size: 0.875rem;">
                                            <tbody id="master_import_failure_tbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0">
                                <button type="button" class="btn btn-primary"
                                    onclick="window.location.reload();">Finish & Reload</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="settings-data" class="d-none" data-created-role-id="{{ session('created_role_id') }}"
        data-society-lat="{{ $settings['society_latitude'] ?? '19.0760' }}"
        data-society-lng="{{ $settings['society_longitude'] ?? '72.8777' }}"
        data-currency-symbol="{{ $currencySymbol }}"
        data-available-currencies="{{ json_encode($availableCurrencies) }}"
        data-routes="{{ json_encode([
            'previewMaster' => route('settings.global.preview_master'),
            'processGlobal' => route('settings.global.process'),
            'processMaster' => route('settings.global.process_master'),
            'settingsStore' => route('settings.store'),
        ]) }}">
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            window.SMP_SETTINGS_CONFIG = {
                activeModule: @json(session('active_module')),
                availableCurrencies: @json($availableCurrencies),
                currencySymbol: '{{ $currencySymbol }}',
                societyLat: parseFloat("{{ $settings['society_latitude'] ?? '19.0760' }}") || 19.0760,
                societyLng: parseFloat("{{ $settings['society_longitude'] ?? '72.8777' }}") || 72.8777,
                csrfToken: '{{ csrf_token() }}',
                routes: {
                    previewMaster: "{{ route('settings.global.preview_master') }}",
                    processGlobal: "{{ route('settings.global.process') }}",
                    processMaster: "{{ route('settings.global.process_master') }}"
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                const billingSelect = document.getElementById('maintenance_billing_method_select');
                const propertyTypeSelect = document.getElementById('society_property_type_select');
                const rateWrapper = document.getElementById('maintenance_rate_per_sqft_wrapper');
                const fixedWrapper = document.getElementById('maintenance_fixed_rate_wrapper');
                const sidebarRatesLink = document.getElementById('sidebar-maintenance-rates-link');

                function updateFixedMaintenanceVisibility() {
                    const propType = propertyTypeSelect ? propertyTypeSelect.value : 'flat_residential';
                    const bothOption = document.getElementById('billing_method_both_option');
                    if (bothOption) {
                        if (propType === 'mixed_use') {
                            bothOption.classList.remove('d-none');
                            bothOption.style.display = '';
                            bothOption.disabled = false;
                            bothOption.hidden = false;
                            bothOption.removeAttribute('disabled');
                            bothOption.removeAttribute('hidden');
                        } else {
                            bothOption.classList.add('d-none');
                            bothOption.style.display = 'none';
                            bothOption.disabled = true;
                            bothOption.hidden = true;
                            bothOption.setAttribute('disabled', 'disabled');
                            bothOption.setAttribute('hidden', 'hidden');
                            if (billingSelect && billingSelect.value === 'both') {
                                billingSelect.value = 'fixed';
                            }
                        }
                    }

                    const currentMethod = billingSelect ? billingSelect.value : 'fixed';
                    const isNotCommercial = (propType !== 'commercial_complex');

                    if (currentMethod === 'fixed') {
                        if (rateWrapper) {
                            if (propType === 'mixed_use' || propType === 'commercial_complex') {
                                rateWrapper.classList.remove('d-none');
                                rateWrapper.style.display = '';
                            } else {
                                rateWrapper.classList.add('d-none');
                                rateWrapper.style.display = 'none';
                            }
                        }
                        if (fixedWrapper) {
                            if (isNotCommercial) {
                                fixedWrapper.classList.remove('d-none');
                                fixedWrapper.style.display = '';
                            } else {
                                fixedWrapper.classList.add('d-none');
                                fixedWrapper.style.display = 'none';
                            }
                        }
                    } else if (currentMethod === 'per_sqft') {
                        if (rateWrapper) {
                            rateWrapper.classList.remove('d-none');
                            rateWrapper.style.display = '';
                        }
                        if (fixedWrapper) {
                            fixedWrapper.classList.add('d-none');
                            fixedWrapper.style.display = 'none';
                        }
                    } else if (currentMethod === 'both') {
                        if (rateWrapper) {
                            rateWrapper.classList.remove('d-none');
                            rateWrapper.style.display = '';
                        }
                        if (fixedWrapper) {
                            fixedWrapper.classList.remove('d-none');
                            fixedWrapper.style.display = '';
                        }
                    }

                    if (sidebarRatesLink) {
                        if (isNotCommercial) sidebarRatesLink.classList.remove('d-none');
                        else sidebarRatesLink.classList.add('d-none');
                    }
                }

                window.applyStructureLiveUpdate = function(propType, customUnit, customBlock, customResident,
                    fromDropdownChange = false) {
                    let unit = customUnit;
                    let block = customBlock;
                    let resident = customResident;

                    const unitInput = document.querySelector('input[name="ui_label_unit"]');
                    const blockInput = document.querySelector('input[name="ui_label_block"]');
                    const residentInput = document.querySelector('input[name="ui_label_resident"]');

                    if (!unit && unitInput) unit = unitInput.value;
                    if (!block && blockInput) block = blockInput.value;
                    if (!resident && residentInput) resident = residentInput.value;

                    // Adapt preset terminology dynamically ONLY when the user explicitly changes the Society Structure Type dropdown
                    if (fromDropdownChange && propType) {
                        if (propType === 'commercial_complex') {
                            unit = 'Shop / Office';
                            block = 'Wing';
                            resident = 'Occupant / Corporate';
                        } else if (propType === 'rowhouse_villa') {
                            unit = 'Villa';
                            block = 'Phase';
                            resident = 'Villa Owner / Occupant';
                        } else if (propType === 'mixed_use') {
                            unit = 'Property Unit';
                            block = 'Wing / Phase';
                            resident = 'Occupant / Resident';
                        } else if (propType === 'flat_residential') {
                            unit = 'Flat';
                            block = 'Block/Wing';
                            resident = 'Resident';
                        }
                        if (unitInput) unitInput.value = unit;
                        if (blockInput) blockInput.value = block;
                        if (residentInput) residentInput.value = resident;
                    }

                    const cleanUnit = (unit || 'Flat').trim();
                    const cleanBlock = (block || 'Block/Wing').trim();
                    const cleanResident = (resident || 'Resident').trim();

                    let residentPlural = cleanResident;
                    if (cleanResident === 'Villa Owner / Occupant') residentPlural = 'Villa Owner / Occupants';
                    else if (cleanResident === 'Occupant / Corporate') residentPlural = 'Occupants / Corporates';
                    else if (cleanResident === 'Occupant / Resident') residentPlural = 'Occupants / Residents';
                    else if (cleanResident === 'Tenant / Owner') residentPlural = 'Tenant / Owners';
                    else if (cleanResident === 'Villa Owner') residentPlural = 'Villa Owners';
                    else residentPlural = cleanResident.endsWith('s') ? cleanResident : (cleanResident + 's');

                    let unitTypes = cleanUnit + ' Types';
                    if (cleanUnit === 'Villa') unitTypes = 'Villa Categories';
                    else if (cleanUnit === 'Shop') unitTypes = 'Shop Categories';
                    else if (cleanUnit === 'Shop / Office') unitTypes = 'Shop & Office Categories';
                    else if (cleanUnit === 'Property Unit') unitTypes = 'Property Unit Categories';

                    document.querySelectorAll('.sidebar-label-unit').forEach(el => el.textContent = cleanUnit);
                    document.querySelectorAll('.sidebar-label-unit-types').forEach(el => el.textContent =
                    unitTypes);
                    document.querySelectorAll('.sidebar-label-block').forEach(el => el.textContent = cleanBlock);
                    document.querySelectorAll('.sidebar-label-resident').forEach(el => el.textContent =
                        cleanResident);
                    document.querySelectorAll('.sidebar-label-resident-plural').forEach(el => el.textContent =
                        residentPlural);
                };

                if (billingSelect) {
                    billingSelect.addEventListener('change', updateFixedMaintenanceVisibility);
                }
                if (propertyTypeSelect) {
                    let previousSocietyPropertyType = propertyTypeSelect.value;
                    propertyTypeSelect.addEventListener('change', function(e) {
                        let selectEl = this;
                        let newValue = selectEl.value;

                        Swal.fire({
                            title: 'Change Society Structure?',
                            text: 'Changing this will update default vocabularies and may affect billing calculation methods. Are you sure?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, change it!',
                            cancelButtonText: 'No, cancel',
                            background: document.documentElement.getAttribute('data-coreui-theme') === 'dark' ? '#2b2c3b' : '#fff',
                            color: document.documentElement.getAttribute('data-coreui-theme') === 'dark' ? '#fff' : '#545454'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                previousSocietyPropertyType = newValue;
                                updateFixedMaintenanceVisibility();
                                window.applyStructureLiveUpdate(newValue, null, null, null, true);
                            } else {
                                selectEl.value = previousSocietyPropertyType;
                            }
                        });
                    });
                }

                const unitInputEl = document.querySelector('input[name="ui_label_unit"]');
                const blockInputEl = document.querySelector('input[name="ui_label_block"]');
                const residentInputEl = document.querySelector('input[name="ui_label_resident"]');

                if (unitInputEl || blockInputEl || residentInputEl) {
                    [unitInputEl, blockInputEl, residentInputEl].forEach(inputEl => {
                        if (inputEl) {
                            inputEl.addEventListener('input', function() {
                                window.applyStructureLiveUpdate(
                                    propertyTypeSelect ? propertyTypeSelect.value : null,
                                    unitInputEl ? unitInputEl.value : null,
                                    blockInputEl ? blockInputEl.value : null,
                                    residentInputEl ? residentInputEl.value : null,
                                    false
                                );
                            });
                        }
                    });
                }

                updateFixedMaintenanceVisibility();
                window.applyStructureLiveUpdate(propertyTypeSelect ? propertyTypeSelect.value : null, null, null, null,
                    false);

                // Ensure document requirement hidden values sync right before form submission when clicking Save Settings
                document.querySelectorAll('form[action="{{ route('settings.store') }}"]').forEach(form => {
                    form.addEventListener('submit', function() {
                        this.querySelectorAll('.doc-setting-card').forEach(card => {
                            const hiddenInput = card.querySelector('.real-setting-input');
                            const toggle = card.querySelector('.doc-enable-toggle');
                            const select = card.querySelector('.doc-type-select');
                            if (hiddenInput && toggle && select) {
                                hiddenInput.value = toggle.checked ? select.value : '0';
                            }
                        });
                    });
                });

                const debuggerSwitch = document.getElementById('enable_debugger');
                if (debuggerSwitch) {
                    debuggerSwitch.addEventListener('change', function() {
                        const val = this.checked ? '1' : '0';
                        if (val === '0') {
                            const bar = document.querySelector('.phpdebugbar');
                            if (bar) bar.style.display = 'none';
                        }
                        fetch("{{ route('settings.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                enable_debugger: val
                            })
                        }).then(response => {
                            if (response.ok) {
                                window.location.reload();
                            }
                        });
                    });
                }

                // Document Settings 2-Step Interactive Flow
                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('doc-enable-toggle')) {
                        const card = e.target.closest('.doc-setting-card');
                        if (!card) return;
                        const hiddenInput = card.querySelector('.real-setting-input');
                        const select = card.querySelector('.doc-type-select');
                        const container = card.querySelector('.doc-options-container');

                        if (e.target.checked) {
                            if (select) select.disabled = false;
                            if (container) {
                                container.style.opacity = '1';
                                container.style.pointerEvents = 'auto';
                            }
                            if (hiddenInput && select) hiddenInput.value = select.value;
                        } else {
                            if (select) select.disabled = true;
                            if (container) {
                                container.style.opacity = '0.35';
                                container.style.pointerEvents = 'none';
                            }
                            if (hiddenInput) hiddenInput.value = '0';
                        }
                        if (typeof window.triggerGlobalSettingsAutoSave === 'function') {
                            window.triggerGlobalSettingsAutoSave(card.closest('form'));
                        }
                    } else if (e.target.classList.contains('doc-type-select')) {
                        const card = e.target.closest('.doc-setting-card');
                        if (!card) return;
                        const hiddenInput = card.querySelector('.real-setting-input');
                        const toggle = card.querySelector('.doc-enable-toggle');

                        if (toggle && toggle.checked && hiddenInput) {
                            hiddenInput.value = e.target.value;
                        }
                        if (typeof window.triggerGlobalSettingsAutoSave === 'function') {
                            window.triggerGlobalSettingsAutoSave(card.closest('form'));
                        }
                    }
                });
            });
        </script>
    @endpush

    <!-- Manage Property Types Modal -->
    <div class="modal fade" id="managePropertyTypesModal" tabindex="-1"
        aria-labelledby="managePropertyTypesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="managePropertyTypesModalLabel"><i
                            class="fa-solid fa-city text-primary me-2"></i>Manage Society Structure Types</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add New Form -->
                    <form action="{{ route('property-types.store') }}" method="POST"
                        class="mb-4 bg-body-tertiary p-3 rounded border">
                        @csrf
                        <label class="form-label small fw-semibold text-uppercase">Add New Structure Type</label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g. Row House Sector / Bungalows" required>
                            <button type="submit" class="btn btn-primary"><i
                                    class="fa-solid fa-plus me-1"></i>Add</button>
                        </div>
                    </form>

                    <!-- Existing List -->
                    <h6 class="small fw-semibold text-uppercase mb-2">Available Structure Types</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Structure Type Name</th>
                                    <th>System Code</th>
                                    <th class="text-end" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($propertyTypes) && $propertyTypes->count() > 0)
                                    @foreach ($propertyTypes as $pt)
                                        <tr>
                                            <td>{{ $pt->id }}</td>
                                            <td>
                                                <form action="{{ route('property-types.update', $pt->id) }}"
                                                    method="POST" class="d-flex align-items-center">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="name"
                                                        value="{{ $pt->name }}"
                                                        class="form-control form-control-sm me-2" required>
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-primary py-0 px-2"
                                                        title="Update"><i class="fa-solid fa-check"></i></button>
                                                </form>
                                            </td>
                                            <td><code>{{ $pt->code }}</code></td>
                                            <td class="text-end">
                                                @if (($settings['society_property_type'] ?? 'flat_residential') !== $pt->code)
                                                    <form action="{{ route('property-types.destroy', $pt->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this structure type?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-danger py-0 px-2"
                                                            title="Delete"><i
                                                                class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-success small">Active</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No structure types
                                            found.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-user-page>
