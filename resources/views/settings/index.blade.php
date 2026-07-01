<x-user-page>
    {{-- Styles moved to public/css/custom-premium.css --}}

    @php
        $currencySymbol = \App\Helpers\CurrencyHelper::getCurrencySymbol();
        $availableCurrencies = \App\Helpers\CurrencyHelper::getAvailableCurrencies();
        $penaltyRateSuffix = ($settings['penalty_type'] ?? 'percentage') === 'fixed' ? $currencySymbol : '%';
        $discountRateSuffix = ($settings['discount_type'] ?? 'percentage') === 'fixed' ? $currencySymbol : '%';
    @endphp

    <div class="row" id="general-settings">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Global Settings</h4>
                </div>
                <div class="card-body">


                    <form action="{{ route('settings.store') }}" method="POST">
                        @csrf

                        <h5 class="mb-3 fw-bold">General Settings</h5>
                        <div class="row mb-5">
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
                                <label class="form-label text-body small fw-semibold text-uppercase">Laravel
                                    Debugger</label>
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="enable_debugger" value="0">
                                    <input class="form-check-input" type="checkbox" id="enable_debugger"
                                        name="enable_debugger" value="1"
                                        {{ ($settings['enable_debugger'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 ms-2" for="enable_debugger">Enable Debug
                                        Toolbar</label>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h5 class="mb-3 fw-bold">Late Penalty Settings</h5>
                        <div class="row mb-5">
                            <div class="col-md-12 mb-4">
                                <label class="form-label text-body small fw-semibold text-uppercase">Allow Late Fees
                                    Penalty</label>
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="apply_penalty" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_penalty"
                                        name="apply_penalty" value="1"
                                        {{ ($settings['apply_penalty'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 ms-2" for="apply_penalty">Yes,
                                        automatically apply penalty to unpaid invoices</label>
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

                        <hr class="mb-4">

                        <h5 class="mb-3 fw-bold">Prepayment Discount Settings</h5>
                        <div class="row mb-5">
                            <div class="col-md-12 mb-4">
                                <label class="form-label text-body small fw-semibold text-uppercase">Allow Prepayment
                                    Discount</label>
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="apply_discount" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_discount"
                                        name="apply_discount" value="1"
                                        {{ ($settings['apply_discount'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 ms-2" for="apply_discount">Yes,
                                        automatically apply discounts to prepayments</label>
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

                            <div class="col-md-3 mb-3 settings-rate-option">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_monthly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_monthly_enabled"
                                        name="discount_monthly_enabled" value="1"
                                        {{ ($settings['discount_monthly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-body small fw-semibold text-uppercase label-discount"
                                        for="discount_monthly_enabled">Monthly (1 Month)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_monthly_value"
                                        class="form-control" value="{{ $settings['discount_monthly_value'] }}">
                                    <span class="input-group-text discount-suffix">{{ $discountRateSuffix }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 settings-rate-option">
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
                            <div class="col-md-3 mb-3 settings-rate-option">
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
                            <div class="col-md-3 mb-3 settings-rate-option">
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

                        <hr class="mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Required Documents for Owner</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary checkall-btn"
                                data-target="req_doc_owner_">Check All</button>
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
                                    // 'contact_no' => 'Contact No',
                                    // 'email' => 'Email Address',
                                ];
                            @endphp
                            @foreach ($ownerDocs as $key => $label)
                                <div class="col-md-3 mb-3">
                                    <div class="form-check form-switch mt-1">
                                        <input type="hidden" name="req_doc_owner_{{ $key }}"
                                            value="0">
                                        <input class="form-check-input" type="checkbox"
                                            id="req_doc_owner_{{ $key }}"
                                            name="req_doc_owner_{{ $key }}" value="1"
                                            {{ ($settings['req_doc_owner_' . $key] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label text-body small fw-semibold ms-1"
                                            for="req_doc_owner_{{ $key }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr class="mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Required Documents for Rental</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary checkall-btn"
                                data-target="req_doc_rental_">Check All</button>
                        </div>
                        <div class="row mb-5">
                            @php
                                $rentalDocs = [
                                    'passport_photo' => 'Passport Size Photo',
                                    'adhar_card' => 'Aadhar Card',
                                    'pan_card' => 'PAN Card',
                                    'rent_agreement' => 'Rent Agreement',
                                    'police_verification' => 'Police Verification',
                                    'permanent_address_proof' => 'Permanent Address Proof',
                                    // 'contact_no' => 'Contact Number',
                                    // 'email' => 'Email Address',
                                ];
                            @endphp
                            @foreach ($rentalDocs as $key => $label)
                                <div class="col-md-3 mb-3">
                                    <div class="form-check form-switch mt-1">
                                        <input type="hidden" name="req_doc_rental_{{ $key }}"
                                            value="0">
                                        <input class="form-check-input" type="checkbox"
                                            id="req_doc_rental_{{ $key }}"
                                            name="req_doc_rental_{{ $key }}" value="1"
                                            {{ ($settings['req_doc_rental_' . $key] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label text-body small fw-semibold ms-1"
                                            for="req_doc_rental_{{ $key }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-end border-top pt-4">
                            <button type="submit" class="btn btn-primary fw-bold px-5 py-2 rounded-3"><i
                                    class="fa-solid fa-save me-2"></i> Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Society Location Settings -->
    <div class="row mt-4" id="location-settings">
        <div class="col-12">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="mb-0"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Society Location
                        Setting</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.store') }}" method="POST">
                        @csrf
                        <p class="text-body small mb-4">
                            Search your location or click anywhere on the interactive map below to set your society's
                            exact GPS coordinates. The address bar will automatically update as you move the pin.
                        </p>

                        <input type="hidden" id="society_latitude" name="society_latitude"
                            value="{{ $settings['society_latitude'] ?? '19.0760' }}">
                        <input type="hidden" id="society_longitude" name="society_longitude"
                            value="{{ $settings['society_longitude'] ?? '72.8777' }}">

                        <div class="row mb-3">
                            <div class="col-md-8 mb-3 position-relative">
                                <label class="form-label text-body small fw-semibold text-uppercase">Search Location /
                                    Address</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="fa-solid fa-magnifying-glass text-body"></i></span>
                                    <input type="text" id="map_search_input" name="society_map_address"
                                        class="form-control border-start-0 ps-0 py-2"
                                        placeholder="Type city, area, society name or street..."
                                        value="{{ $settings['society_map_address'] ?? '' }}" autocomplete="off">
                                    <button class="btn btn-primary px-4 fw-semibold" type="button"
                                        id="btn_search_location">Search</button>
                                </div>
                                <div id="search_results_list"
                                    class="list-group position-absolute w-100 shadow-lg border-0 rounded-3 mt-1 d-none text-start bg-white"
                                    style="z-index: 1050; max-height: 300px; overflow-y: auto; left: 12px; width: calc(100% - 24px) !important;">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-body small fw-semibold text-uppercase">GPS
                                    Auto-Detect</label>
                                <button type="button"
                                    class="btn btn-outline-primary w-100 fw-semibold py-2 shadow-sm"
                                    id="btn_get_my_current_location">
                                    <i class="fa-solid fa-location-crosshairs me-2"></i>Detect Device GPS
                                </button>
                            </div>
                        </div>

                        <div class="position-relative mb-4">
                            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
                            <div id="society_location_map"
                                style="height: 420px; width: 100%; border-radius: 12px; z-index: 1;"
                                class="shadow-sm border"></div>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary fw-bold px-5 py-2 rounded-3">
                                <i class="fa-solid fa-save me-2"></i> Save Society Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                                        <tr>
                                            <td class="ps-4 align-middle fw-semibold text-body" style="width: 250px;">
                                                {{ $moduleName ?? 'General' }}</td>
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
                                                                for="em_flat_types">Flat Types</label></div>
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

    <div id="settings-data" class="d-none" data-created-role-id="{{ session('created_role_id') }}"></div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            window.SMP_SETTINGS_CONFIG = {
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
            });
        </script>
    @endpush
</x-user-page>
