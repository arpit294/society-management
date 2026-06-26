<form id="user-ajax-form" action="{{ $action }}" method="POST">
    @csrf
    <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Add User</h5>
            <p class="text-muted mb-0 small">Fill in the details and save the user.</p>
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
            <label class="form-label text-success fw-semibold">Aadhar ID <span class="text-danger">*</span></label>
            <input type="text" name="aadhar_id" class="form-control @error('aadhar_id') is-invalid @enderror"
                placeholder="12-digit Aadhar number" inputmode="numeric" pattern="[0-9]{12}" maxlength="12"
                value="{{ old('aadhar_id', $user->aadhar_id ?? '') }}">
            <small class="field-hint">Must be exactly 12 digits</small>
            @error('aadhar_id')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-warning fw-semibold">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach (['active', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $user->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label text-danger fw-semibold">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Enter secure password">
            @error('password')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
