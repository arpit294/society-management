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

            <!-- Global Import Export Hub -->
            <div class="row mt-4" id="global-import-export">
                <div class="col-12">
                    <div class="card mb-5 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-database text-primary me-2"></i>Global Import Export</h4>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small">All Modules Engine</span>
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
                                                <p class="text-muted small mb-0">Download database records into Excel (.xlsx)</p>
                                            </div>
                                        </div>
                                        <hr class="text-muted opacity-25 mb-4">
                                        
                                        <form id="global-export-form" action="{{ route('settings.global.export') }}" method="GET">
                                            <input type="hidden" name="format" value="excel">
                                            <div class="mb-4 position-relative" style="z-index: 10;">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Select Table Module</label>
                                                <select name="table" class="form-select form-select-lg shadow-none border-secondary border-opacity-25" id="export_module_select" style="cursor: pointer;">
                                                    <option value="blocks">Blocks</option>
                                                    <option value="flats">Flats</option>
                                                    <option value="users">Staff & Users</option>
                                                    <option value="residents">Residents</option>
                                                    <option value="complaints">Complaints</option>
                                                    <option value="expenses">Expenses</option>
                                                    <option value="flat_types">Flat Types</option>
                                                    <option value="expense_categories">Expense Categories</option>
                                                </select>
                                            </div>
                                            <div class="mt-auto pt-2">
                                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2 py-3">
                                                    <i class="fa-solid fa-cloud-arrow-down"></i> <span id="export_btn_text">Export Blocks</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Import Panel -->
                                <div class="col-md-6">
                                    <div class="card h-100 border shadow-sm rounded-4 p-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                                                    <i class="fa-solid fa-file-import fa-xl"></i>
                                                </div>
                                                <div>
                                                    <h5 class="fw-bold mb-1 text-body">Bulk Import</h5>
                                                    <p class="text-muted small mb-0">Upload Excel to add records in bulk</p>
                                                </div>
                                            </div>
                                            <a href="{{ route('settings.global.template', ['table' => 'blocks']) }}" id="btn_dl_template" class="btn btn-sm btn-outline-success fw-semibold d-flex align-items-center gap-1">
                                                <i class="fa-solid fa-download"></i> Template
                                            </a>
                                        </div>
                                        <hr class="text-muted opacity-25 mb-4">

                                        <form id="global-import-form" onsubmit="return false;">
                                            <div class="mb-3 position-relative" style="z-index: 10;">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Target Table Module</label>
                                                <select name="import_table" class="form-select form-select-lg shadow-none border-secondary border-opacity-25" id="import_module_select" style="cursor: pointer;">
                                                    <option value="blocks">Blocks</option>
                                                    <option value="flats">Flats</option>
                                                    <option value="users">Staff & Users</option>
                                                    <option value="residents">Residents</option>
                                                    <option value="complaints">Complaints</option>
                                                    <option value="expenses">Expenses</option>
                                                    <option value="flat_types">Flat Types</option>
                                                    <option value="expense_categories">Expense Categories</option>
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label small fw-bold text-uppercase text-muted">Upload Spreadsheet</label>
                                                <div class="border border-2 border-dashed rounded-3 p-4 text-center position-relative" style="transition: all 0.2s;">
                                                    <input type="file" class="position-absolute w-100 h-100 top-0 start-0 opacity-0" id="global_import_file" accept=".xlsx, .xls, .csv" style="cursor: pointer;">
                                                    <i class="fa-solid fa-cloud-arrow-up text-success mb-2" style="font-size: 2.2rem;"></i>
                                                    <h6 class="mb-1 fw-bold text-body" id="import_file_label">Click or Drag Excel file here</h6>
                                                    <p class="text-muted small mb-0">Max file size: 5MB</p>
                                                </div>
                                            </div>

                                            <div class="mt-auto pt-1">
                                                <button type="button" id="btn_preview_global_import" class="btn btn-success btn-lg w-100 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2 py-3">
                                                    <i class="fa-solid fa-play"></i> Preview & Map Columns
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Import Preview & Mapping Modal -->
            <div class="modal fade" id="globalImportPreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-success text-white py-3">
                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-table-columns me-2"></i>Map Spreadsheet Columns (<span id="modal_import_table_name"></span>)</h5>
                            <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info border-0 shadow-sm small mb-4 d-flex align-items-center">
                                <i class="fa-solid fa-circle-info fa-xl me-3 text-info"></i>
                                <div>
                                    Map your Excel sheet columns to the database fields below. Required fields are marked with <strong>(*)</strong>.<br>
                                    <em>Note: Any duplicates or conflicts will stop import and display exact line errors.</em>
                                </div>
                            </div>

                            <h6 class="fw-bold text-body mb-3">1. Column Field Mapping</h6>
                            <div class="row g-3 mb-4" id="global_mapping_container"></div>

                            <h6 class="fw-bold text-body mb-3">2. Data Preview (First 5 Rows)</h6>
                            <div class="table-responsive border rounded shadow-sm">
                                <table class="table table-bordered table-striped table-hover mb-0 small" id="global_preview_table">
                                    <thead class="table-light" id="global_preview_thead"></thead>
                                    <tbody id="global_preview_tbody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3">
                            <input type="hidden" id="global_temp_file_path">
                            <input type="hidden" id="global_target_table">
                            <button type="button" class="btn btn-secondary px-4 fw-medium" data-coreui-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success px-5 fw-bold text-white shadow-sm" id="btn_process_global_import">
                                <i class="fa-solid fa-check me-1"></i> Start Import
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Function to toggle input disabled state based on checkbox
                function toggleInputState(checkboxId, inputName) {
                    const checkbox = document.getElementById(checkboxId);
                    const input = document.querySelector(`input[name="${inputName}"]`);
                    if (checkbox && input) {
                        input.disabled = !checkbox.checked;

                        // Add event listener for changes
                        checkbox.addEventListener('change', function() {
                            input.disabled = !this.checked;
                            if (!this.checked) {
                                input.value = ''; // Optional: clear value when unchecked
                            }
                        });
                    }
                }

                // List of prefixes
                const prefixes = ['penalty', 'discount'];
                const phases = ['monthly', 'quarterly', 'half_yearly', 'yearly'];

                prefixes.forEach(prefix => {
                    phases.forEach(phase => {
                        toggleInputState(`${prefix}_${phase}_enabled`, `${prefix}_${phase}_value`);
                    });
                });

                // Update suffix based on penalty/discount type
                function updateSuffix(selectId, suffixClass) {
                    const select = document.getElementById(selectId);
                    const suffixes = document.querySelectorAll(suffixClass);

                    function update() {
                        const symbol = select.value === 'percentage' ? '%' : '₹';
                        suffixes.forEach(el => el.textContent = symbol);
                    }

                    if (select) {
                        update();
                        select.addEventListener('change', update);
                    }
                }

                updateSuffix('penalty_type', '.penalty-suffix');
                updateSuffix('discount_type', '.discount-suffix');

                const createdRoleId = @json(session('created_role_id'));
                if (createdRoleId) {
                    const card = document.querySelector(`.role-card[data-role-id="${createdRoleId}"]`);
                    if (card) {
                        selectRole(card);
                    }
                }
            });

            function selectRole(element, event) {
                // If the click originated from the dropdown menu, don't trigger role selection
                if (event && event.target.closest('.dropdown')) {
                    return;
                }

                // Remove active class from all cards
                document.querySelectorAll('.role-card').forEach(card => {
                    card.classList.remove('border-primary');
                    card.classList.add('border-0');
                });

                // Add active class to selected card
                element.classList.remove('border-0');
                element.classList.add('border-primary');

                // Show permissions container, hide placeholder
                document.getElementById('permissions-container').style.display = 'block';
                document.getElementById('no-role-selected').style.display = 'none';

                // Get role data
                const roleId = element.getAttribute('data-role-id');
                const roleName = element.getAttribute('data-role-name');
                const permissions = JSON.parse(element.getAttribute('data-role-permissions'));

                // Set form data
                document.getElementById('selected-role-name').value = roleName;

                // Set form action using base URL (assuming /roles/{role} is the update endpoint)
                const baseUrl = "{{ url('roles') }}";
                document.getElementById('role-permissions-form').action = baseUrl + '/' + roleId;

                // Uncheck all checkboxes
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Check the ones the role has
                permissions.forEach(permission => {
                    const checkbox = document.querySelector(`.permission-checkbox[value="${permission}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                // Smooth scroll to permissions table
                document.getElementById('permissions-container').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            // Check All / Uncheck All Buttons
            document.querySelectorAll('.checkall-btn').forEach(btn => {
                const targetPrefix = btn.getAttribute('data-target');
                const checkboxes = document.querySelectorAll(`input[type="checkbox"][name^="${targetPrefix}"]`);
                
                // Initialize button state
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                btn.textContent = allChecked ? 'Uncheck All' : 'Check All';

                // Handle button click
                btn.addEventListener('click', function() {
                    const isCheckAll = this.textContent === 'Check All';
                    
                    checkboxes.forEach(cb => {
                        cb.checked = isCheckAll;
                    });
                    
                    this.textContent = isCheckAll ? 'Uncheck All' : 'Check All';
                });

                // Update button state when individual checkboxes change
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
                        btn.textContent = anyUnchecked ? 'Check All' : 'Uncheck All';
                    });
                });
            });

            // Global Import Export Hub Script
            const exportSelect = document.getElementById('export_module_select');
            const exportBtnText = document.getElementById('export_btn_text');
            if (exportSelect && exportBtnText) {
                const updateExportBtn = () => {
                    const opt = exportSelect.options[exportSelect.selectedIndex];
                    exportBtnText.textContent = opt ? 'Export ' + opt.text : 'Export Spreadsheet';
                };
                exportSelect.addEventListener('change', updateExportBtn);
                updateExportBtn();
            }

            const importSelect = document.getElementById('import_module_select');
            const dlTemplateBtn = document.getElementById('btn_dl_template');
            if (importSelect && dlTemplateBtn) {
                importSelect.addEventListener('change', function() {
                    dlTemplateBtn.href = "{{ route('settings.global.template') }}?table=" + this.value;
                });
            }

            const globalFileInput = document.getElementById('global_import_file');
            const globalFileLabel = document.getElementById('import_file_label');
            if (globalFileInput && globalFileLabel) {
                globalFileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        globalFileLabel.textContent = this.files[0].name;
                        globalFileLabel.classList.add('text-success');
                    } else {
                        globalFileLabel.textContent = 'Click or Drag Excel file here';
                        globalFileLabel.classList.remove('text-success');
                    }
                });
            }

            const btnPreviewImport = document.getElementById('btn_preview_global_import');
            if (btnPreviewImport) {
                btnPreviewImport.addEventListener('click', function() {
                    const table = importSelect ? importSelect.value : 'blocks';
                    if (!globalFileInput || !globalFileInput.files || globalFileInput.files.length === 0) {
                        toastr.error('Please select a spreadsheet file (.xlsx, .csv) first.');
                        return;
                    }

                    const originalText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Reading Spreadsheet...';

                    const fd = new FormData();
                    fd.append('_token', '{{ csrf_token() }}');
                    fd.append('table', table);
                    fd.append('import_file', globalFileInput.files[0]);

                    fetch("{{ route('settings.global.preview') }}", {
                        method: 'POST',
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.disabled = false;
                        this.innerHTML = originalText;

                        if (!data.success) {
                            toastr.error(data.message || 'Error parsing spreadsheet.');
                            return;
                        }

                        document.getElementById('modal_import_table_name').textContent = table.toUpperCase();
                        document.getElementById('global_temp_file_path').value = data.file_path;
                        document.getElementById('global_target_table').value = data.table;

                        const container = document.getElementById('global_mapping_container');
                        container.innerHTML = '';

                        data.expected_headers.forEach((dbField, idx) => {
                            const label = data.expected_labels[idx];
                            const colDiv = document.createElement('div');
                            colDiv.className = 'col-md-4';

                            let optionsHtml = '<option value="">-- Ignore / Skip --</option>';
                            data.headers.forEach((sheetHeader, hIdx) => {
                                const cleanSheetH = String(sheetHeader).trim().toLowerCase();
                                const cleanDbH = dbField.toLowerCase().replace('_', ' ');
                                const selected = (cleanSheetH === cleanDbH || cleanSheetH === dbField.toLowerCase() || hIdx === idx) ? 'selected' : '';
                                optionsHtml += `<option value="${hIdx}" ${selected}>Col ${hIdx + 1}: ${sheetHeader}</option>`;
                            });

                            colDiv.innerHTML = `
                                <div class="p-3 bg-white border rounded shadow-sm">
                                    <label class="form-label small fw-bold text-body d-block mb-1">${label}</label>
                                    <select class="form-select form-select-sm global-map-select" data-db-field="${dbField}">
                                        ${optionsHtml}
                                    </select>
                                </div>
                            `;
                            container.appendChild(colDiv);
                        });

                        const thead = document.getElementById('global_preview_thead');
                        const tbody = document.getElementById('global_preview_tbody');
                        thead.innerHTML = '';
                        tbody.innerHTML = '';

                        let trHead = '<tr>';
                        data.headers.forEach((h, i) => {
                            trHead += `<th class="text-body text-truncate" style="max-width:150px;">Col ${i+1}: ${h}</th>`;
                        });
                        trHead += '</tr>';
                        thead.innerHTML = trHead;

                        data.preview_rows.forEach(r => {
                            let trBody = '<tr>';
                            r.forEach(c => {
                                trBody += `<td class="text-truncate text-muted" style="max-width:150px;">${c !== null ? c : ''}</td>`;
                            });
                            trBody += '</tr>';
                            tbody.innerHTML += trBody;
                        });

                        const modalElem = document.getElementById('globalImportPreviewModal');
                        const modal = coreui.Modal.getOrCreateInstance(modalElem);
                        modal.show();
                    })
                    .catch(err => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        toastr.error('Network error during file preview.');
                    });
                });
            }

            const btnProcessImport = document.getElementById('btn_process_global_import');
            if (btnProcessImport) {
                btnProcessImport.addEventListener('click', function() {
                    const filePath = document.getElementById('global_temp_file_path').value;
                    const targetTable = document.getElementById('global_target_table').value;

                    const mapping = {};
                    document.querySelectorAll('.global-map-select').forEach(sel => {
                        mapping[sel.getAttribute('data-db-field')] = sel.value;
                    });

                    const origHtml = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing...';

                    fetch("{{ route('settings.global.process') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            table: targetTable,
                            file_path: filePath,
                            mapping: mapping
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.disabled = false;
                        this.innerHTML = origHtml;

                        if (data.success) {
                            const modalElem = document.getElementById('globalImportPreviewModal');
                            const modal = coreui.Modal.getInstance(modalElem);
                            if (modal) modal.hide();

                            toastr.success(data.message);
                            if (globalFileInput) globalFileInput.value = '';
                            if (globalFileLabel) globalFileLabel.textContent = 'Click or Drag Excel file here';
                        } else {
                            toastr.error(data.message || 'Import failed due to duplicate conflict.');
                        }
                    })
                    .catch(err => {
                        this.disabled = false;
                        this.innerHTML = origHtml;
                        toastr.error('Server error during bulk import.');
                    });
                });
            }
    </script>
    @endpush
</x-user-page>
