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
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}">
            @error('name')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}">
            @error('email')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}">
            @error('phone')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select @error('role') is-invalid @enderror">
                <option value="">Select role</option>
                @foreach (['owner', 'rental', 'security', 'committee_member', 'secretary'] as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                @endforeach
            </select>
            @error('role')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Aadhar ID</label>
            <input type="text" name="aadhar_id" class="form-control @error('aadhar_id') is-invalid @enderror" value="{{ old('aadhar_id', $user->aadhar_id ?? '') }}">
            @error('aadhar_id')
                <div class="invalid-feedback d-block field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
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
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
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
