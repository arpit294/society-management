<form id="user-ajax-form" action="{{ $action }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Edit User</h5>
            <p class="text-muted mb-0 small">Update the user details and save the changes.</p>
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div id="user-form-errors" class="alert alert-danger d-none"></div>

        <div class="mb-3">
            <label class="form-label text-primary fw-semibold">Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="Full name" value="{{ old('name', $user->name ?? '') }}">
            @error('name')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-primary fw-semibold">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="user@example.com" value="{{ old('email', $user->email ?? '') }}">
            @error('email')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-info fw-semibold">Phone</label>
            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                placeholder="+91 9876543210" value="{{ old('phone', $user->phone ?? '') }}">
            @error('phone')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-primary fw-semibold">Role</label>
            <select name="role" class="form-select @error('role') is-invalid @enderror">
                <option value="">Select role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>
                        {{ config('roles.labels.' . $role, ucfirst(str_replace('_', ' ', $role))) }}</option>
                @endforeach
            </select>
            @error('role')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-primary fw-semibold">Committee / Society Designation <span class="opacity-50 small">(Optional)</span></label>
            <select name="designation" class="form-select @error('designation') is-invalid @enderror">
                <option value="">None / Regular Resident</option>
                <option value="Secretary" @selected(old('designation', $user->designation ?? '') === 'Secretary')>Secretary</option>
                <option value="Committee Member" @selected(old('designation', $user->designation ?? '') === 'Committee Member')>Committee Member</option>
                <option value="Admin" @selected(old('designation', $user->designation ?? '') === 'Admin')>Admin</option>
                <option value="Chairman" @selected(old('designation', $user->designation ?? '') === 'Chairman')>Chairman</option>
                <option value="Treasurer" @selected(old('designation', $user->designation ?? '') === 'Treasurer')>Treasurer</option>
                <option value="Joint Secretary" @selected(old('designation', $user->designation ?? '') === 'Joint Secretary')>Joint Secretary</option>
            </select>
            <small class="field-hint text-muted">Assign a committee designation even if their primary role is Owner/Resident.</small>
            @error('designation')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-success fw-semibold">National ID / Aadhar / Tax ID <span class="opacity-50 small">(Optional)</span></label>
            <input type="text" name="aadhar_id" class="form-control @error('aadhar_id') is-invalid @enderror"
                placeholder="Aadhar, PAN, or Tax ID" maxlength="25"
                value="{{ old('aadhar_id', $user->aadhar_id ?? '') }}">
            <small class="field-hint">Enter Gov/Tax ID up to 25 alphanumeric characters</small>
            @error('aadhar_id')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-warning fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $user->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3">
            <label class="form-label text-danger fw-semibold">Password <span class="opacity-50 small">(Leave blank to
                    keep current)</span></label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Enter new password or leave blank">
            @error('password')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
