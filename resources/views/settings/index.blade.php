<x-user-page>
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
                                <label class="form-label text-muted small fw-semibold text-uppercase">Society
                                    Name</label>
                                <input type="text" name="society_name" class="form-control"
                                    value="{{ $settings['society_name'] ?? 'My Society' }}">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Society
                                    Address</label>
                                <input type="text" name="society_address" class="form-control"
                                    value="{{ $settings['society_address'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Contact
                                    Email</label>
                                <input type="email" name="contact_email" class="form-control"
                                    value="{{ $settings['contact_email'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Contact
                                    Phone</label>
                                <input type="text" name="contact_phone" class="form-control"
                                    value="{{ $settings['contact_phone'] ?? '' }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Financial Year
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
                                <label class="form-label text-muted small fw-semibold text-uppercase">Name Transfer Fee
                                    (₹)</label>
                                <input type="number" step="0.01" name="name_transfer_fee" class="form-control"
                                    value="{{ $settings['name_transfer_fee'] ?? '0' }}">
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h5 class="mb-3 fw-bold">Late Penalty Settings</h5>
                        <div class="row mb-5">
                            <div class="col-md-12 mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Allow Late Fees
                                    Penalty</label>
                                <div class="form-check form-switch fs-5">
                                    <input type="hidden" name="apply_penalty" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_penalty"
                                        name="apply_penalty" value="1"
                                        {{ ($settings['apply_penalty'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 ms-2 mt-1" for="apply_penalty">Yes,
                                        automatically apply penalty to unpaid invoices</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Penalty Charge
                                    Type</label>
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
                                <label class="form-label text-muted small fw-semibold text-uppercase">Penalty Grace
                                    Days</label>
                                <div class="input-group">
                                    <input type="number" name="penalty_due_days" class="form-control"
                                        value="{{ $settings['penalty_due_days'] ?? '15' }}">
                                    <span class="input-group-text text-muted">days after due date</span>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_monthly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="penalty_monthly_enabled"
                                        name="penalty_monthly_enabled" value="1"
                                        {{ ($settings['penalty_monthly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-penalty"
                                        for="penalty_monthly_enabled">Monthly (1 Month)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_monthly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['penalty_monthly_value'] ?? ($settings['penalty_monthly_percent'] ?? '2') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_quarterly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="penalty_quarterly_enabled"
                                        name="penalty_quarterly_enabled" value="1"
                                        {{ ($settings['penalty_quarterly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-penalty"
                                        for="penalty_quarterly_enabled">Quarterly (3 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_quarterly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['penalty_quarterly_value'] ?? ($settings['penalty_quarterly_percent'] ?? '5') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_half_yearly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="penalty_half_yearly_enabled"
                                        name="penalty_half_yearly_enabled" value="1"
                                        {{ ($settings['penalty_half_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-penalty"
                                        for="penalty_half_yearly_enabled">Half-Yearly (6 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_half_yearly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['penalty_half_yearly_value'] ?? ($settings['penalty_half_yearly_percent'] ?? '10') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="penalty_yearly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="penalty_yearly_enabled"
                                        name="penalty_yearly_enabled" value="1"
                                        {{ ($settings['penalty_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-penalty"
                                        for="penalty_yearly_enabled">Yearly (12 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="penalty_yearly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['penalty_yearly_value'] ?? ($settings['penalty_yearly_percent'] ?? '15') }}">
                                    <span class="input-group-text penalty-suffix">%</span>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h5 class="mb-3 fw-bold">Prepayment Discount Settings</h5>
                        <div class="row mb-5">
                            <div class="col-md-12 mb-4">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Allow Prepayment
                                    Discount</label>
                                <div class="form-check form-switch fs-5">
                                    <input type="hidden" name="apply_discount" value="0">
                                    <input class="form-check-input" type="checkbox" id="apply_discount"
                                        name="apply_discount" value="1"
                                        {{ ($settings['apply_discount'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 ms-2 mt-1" for="apply_discount">Yes,
                                        automatically apply discounts to prepayments</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Discount Charge
                                    Type</label>
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
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_monthly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_monthly_enabled"
                                        name="discount_monthly_enabled" value="1"
                                        {{ ($settings['discount_monthly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-discount"
                                        for="discount_monthly_enabled">Monthly (1 Month)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_monthly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['discount_monthly_value'] ?? ($settings['discount_monthly_percent'] ?? '2') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_quarterly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_quarterly_enabled"
                                        name="discount_quarterly_enabled" value="1"
                                        {{ ($settings['discount_quarterly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-discount"
                                        for="discount_quarterly_enabled">Quarterly (3 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_quarterly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['discount_quarterly_value'] ?? ($settings['discount_quarterly_percent'] ?? '5') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_half_yearly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_half_yearly_enabled"
                                        name="discount_half_yearly_enabled" value="1"
                                        {{ ($settings['discount_half_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-discount"
                                        for="discount_half_yearly_enabled">Half-Yearly (6 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_half_yearly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['discount_half_yearly_value'] ?? ($settings['discount_half_yearly_percent'] ?? '10') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check mb-1">
                                    <input type="hidden" name="discount_yearly_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" id="discount_yearly_enabled"
                                        name="discount_yearly_enabled" value="1"
                                        {{ ($settings['discount_yearly_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label text-muted small fw-semibold text-uppercase label-discount"
                                        for="discount_yearly_enabled">Yearly (12 Months)</label>
                                </div>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="discount_yearly_value"
                                        class="form-control text-end"
                                        value="{{ $settings['discount_yearly_value'] ?? ($settings['discount_yearly_percent'] ?? '15') }}">
                                    <span class="input-group-text discount-suffix">%</span>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Required Documents for Owner</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary checkall-btn" data-target="req_doc_owner_">Check All</button>
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
                                    'contact_no' => 'Contact No',
                                    'email' => 'Email Address'
                                ];
                            @endphp
                            @foreach($ownerDocs as $key => $label)
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="req_doc_owner_{{ $key }}" value="0">
                                    <input class="form-check-input" type="checkbox" id="req_doc_owner_{{ $key }}"
                                        name="req_doc_owner_{{ $key }}" value="1"
                                        {{ ($settings['req_doc_owner_'.$key] ?? '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted small fw-semibold ms-1" for="req_doc_owner_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <hr class="mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Required Documents for Rental</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary checkall-btn" data-target="req_doc_rental_">Check All</button>
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
                                    'contact_no' => 'Contact Number',
                                    'email' => 'Email Address'
                                ];
                            @endphp
                            @foreach($rentalDocs as $key => $label)
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="req_doc_rental_{{ $key }}" value="0">
                                    <input class="form-check-input" type="checkbox" id="req_doc_rental_{{ $key }}"
                                        name="req_doc_rental_{{ $key }}" value="1"
                                        {{ ($settings['req_doc_rental_'.$key] ?? '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted small fw-semibold ms-1" for="req_doc_rental_{{ $key }}">{{ $label }}</label>
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
                    <h4 class="mb-0"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Society Location Setting</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.store') }}" method="POST">
                        @csrf
                        <p class="text-muted small mb-4">
                            Search your location or click anywhere on the interactive map below to set your society's exact GPS coordinates. The address bar will automatically update as you move the pin.
                        </p>
                        
                        <input type="hidden" id="society_latitude" name="society_latitude" value="{{ $settings['society_latitude'] ?? '19.0760' }}">
                        <input type="hidden" id="society_longitude" name="society_longitude" value="{{ $settings['society_longitude'] ?? '72.8777' }}">

                        <div class="row mb-3">
                            <div class="col-md-8 mb-3 position-relative">
                                <label class="form-label text-muted small fw-semibold text-uppercase">Search Location / Address</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" id="map_search_input" name="society_map_address" class="form-control border-start-0 ps-0 py-2" placeholder="Type city, area, society name or street..." value="{{ $settings['society_map_address'] ?? '' }}" autocomplete="off">
                                    <button class="btn btn-primary px-4 fw-semibold" type="button" id="btn_search_location">Search</button>
                                </div>
                                <div id="search_results_list" class="list-group position-absolute w-100 shadow-lg border-0 rounded-3 mt-1 d-none text-start bg-white" style="z-index: 1050; max-height: 300px; overflow-y: auto; left: 12px; width: calc(100% - 24px) !important;"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small fw-semibold text-uppercase">GPS Auto-Detect</label>
                                <button type="button" class="btn btn-outline-primary w-100 fw-semibold py-2 shadow-sm" id="btn_get_my_current_location">
                                    <i class="fa-solid fa-location-crosshairs me-2"></i>Detect Device GPS
                                </button>
                            </div>
                        </div>

                        <div class="position-relative mb-4">
                            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
                            <div id="society_location_map" style="height: 420px; width: 100%; border-radius: 12px; z-index: 1;" class="shadow-sm border"></div>
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
                            data-role-permissions='@json($role->permissions->pluck("name")->values())'
                            onclick="selectRole(this, event)">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0 fw-bold border-start border-primary border-4 ps-2">
                                        {{ $role->name }}</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0" type="button"
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
                                    <span class="text-muted small">Permission</span>
                                    <span class="text-primary small fw-medium">View</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Member :</span>
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
                                            <td class="ps-4 align-middle fw-semibold text-body"
                                                style="width: 250px;">{{ $moduleName ?? 'General' }}</td>
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
                    <i class="fa-solid fa-list-check fa-3x text-muted mb-3" style="opacity: 0.1;"></i>
                    <p class="text-muted fw-medium mb-0">Select a role to assign permissions</p>
                </div>
            </div>
        </div>
    </div>

    <div id="settings-data" class="d-none" data-created-role-id="{{ session('created_role_id') }}"></div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mapElem = document.getElementById('society_location_map');
            if (!mapElem) return;

            var initialLat = parseFloat("{{ $settings['society_latitude'] ?? '19.0760' }}") || 19.0760;
            var initialLng = parseFloat("{{ $settings['society_longitude'] ?? '72.8777' }}") || 72.8777;

            var map = L.map('society_location_map').setView([initialLat, initialLng], 15);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            var marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            function reverseGeocode(lat, lng) {
                fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                    .then(response => response.json())
                    .then(data => {
                        var searchInput = document.getElementById('map_search_input');
                        if (searchInput && data && data.display_name) {
                            searchInput.value = data.display_name;
                        }
                    })
                    .catch(err => console.log(err));
            }

            function updateCoordinates(lat, lng, doReverse = false) {
                var latInput = document.getElementById('society_latitude');
                var lngInput = document.getElementById('society_longitude');
                if (latInput && lngInput) {
                    latInput.value = lat.toFixed(6);
                    lngInput.value = lng.toFixed(6);
                }
                if (doReverse) {
                    reverseGeocode(lat, lng);
                }
            }

            marker.on('dragend', function(e) {
                var position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng, true);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng, true);
            });

            function renderSearchResults(results) {
                var listElem = document.getElementById('search_results_list');
                if (!listElem) return;
                listElem.innerHTML = '';
                
                if (!results || results.length === 0) {
                    listElem.innerHTML = '<div class="list-group-item text-muted small p-3">No societies or matching locations found in India. Try searching city name + area.</div>';
                    listElem.classList.remove('d-none');
                    return;
                }

                results.forEach(function(place) {
                    var item = document.createElement('a');
                    item.href = 'javascript:void(0)';
                    item.className = 'list-group-item list-group-item-action p-3 border-bottom d-flex align-items-center';
                    var title = place.display_name.split(',')[0] || 'Location';
                    item.innerHTML = '<div class="bg-light p-2 rounded-circle me-3 text-primary"><i class="fa-solid fa-location-dot"></i></div>' +
                                     '<div class="flex-grow-1 overflow-hidden">' +
                                         '<div class="fw-semibold text-primary mb-0 text-truncate">' + title + '</div>' +
                                         '<div class="small text-muted text-truncate">' + place.display_name + '</div>' +
                                     '</div>';
                    
                    item.addEventListener('click', function() {
                        var lat = parseFloat(place.lat);
                        var lon = parseFloat(place.lon);
                        map.setView([lat, lon], 17);
                        marker.setLatLng([lat, lon]);
                        updateCoordinates(lat, lon, false);
                        var searchInput = document.getElementById('map_search_input');
                        if (searchInput) searchInput.value = place.display_name;
                        listElem.classList.add('d-none');
                        toastr.success('Location pin updated!');
                    });

                    listElem.appendChild(item);
                });

                listElem.classList.remove('d-none');
            }

            function searchAddress(query) {
                if (!query || !query.trim()) return;
                toastr.info('Searching locations in India...');
                
                // Search Nominatim + Photon for comprehensive society & landmark matching
                var p1 = fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&countrycodes=in&limit=6&q=' + encodeURIComponent(query))
                    .then(res => res.json()).catch(() => []);
                var p2 = fetch('https://photon.komoot.io/api/?filter=countrycode:in&limit=6&q=' + encodeURIComponent(query))
                    .then(res => res.json()).catch(() => ({ features: [] }));

                Promise.all([p1, p2]).then(function(resArray) {
                    var nData = resArray[0] || [];
                    var pData = resArray[1] || { features: [] };
                    
                    var combined = [];
                    var seen = new Set();

                    nData.forEach(function(item) {
                        var key = parseFloat(item.lat).toFixed(3) + '_' + parseFloat(item.lon).toFixed(3);
                        if (!seen.has(key)) {
                            seen.add(key);
                            combined.push({ lat: item.lat, lon: item.lon, display_name: item.display_name });
                        }
                    });

                    (pData.features || []).forEach(function(f) {
                        if (f.geometry && f.geometry.coordinates) {
                            var lon = f.geometry.coordinates[0];
                            var lat = f.geometry.coordinates[1];
                            var key = parseFloat(lat).toFixed(3) + '_' + parseFloat(lon).toFixed(3);
                            if (!seen.has(key)) {
                                seen.add(key);
                                var nameParts = [f.properties.name, f.properties.street, f.properties.district, f.properties.city, f.properties.state].filter(Boolean);
                                combined.push({ lat: lat, lon: lon, display_name: nameParts.join(', ') });
                            }
                        }
                    });

                    renderSearchResults(combined);
                    if (combined.length > 0) toastr.success('Found ' + combined.length + ' matching locations!');
                });
            }

            var btnSearch = document.getElementById('btn_search_location');
            var searchInput = document.getElementById('map_search_input');

            if (btnSearch && searchInput) {
                btnSearch.addEventListener('click', function() {
                    searchAddress(searchInput.value);
                });
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchAddress(searchInput.value);
                    }
                });
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    var listElem = document.getElementById('search_results_list');
                    if (listElem && !listElem.contains(e.target) && e.target !== searchInput && e.target !== btnSearch) {
                        listElem.classList.add('d-none');
                    }
                });
            }

            var btnDetect = document.getElementById('btn_get_my_current_location');
            if (btnDetect) {
                btnDetect.addEventListener('click', function() {
                    if (navigator.geolocation) {
                        toastr.info('Detecting device GPS location...');
                        navigator.geolocation.getCurrentPosition(function(position) {
                            var lat = position.coords.latitude;
                            var lng = position.coords.longitude;
                            map.setView([lat, lng], 16);
                            marker.setLatLng([lat, lng]);
                            updateCoordinates(lat, lng, true);
                            toastr.success('Device location detected!');
                        }, function(error) {
                            toastr.error('Unable to retrieve GPS location. Check browser location permissions.');
                        });
                    } else {
                        toastr.error('Geolocation is not supported by your browser.');
                    }
                });
            }
        });
    </script>
    @endpush
</x-user-page>
