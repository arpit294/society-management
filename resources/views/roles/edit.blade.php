<x-user-page>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Role</h4>
        <a href="{{ route('settings.index') }}#role-settings" class="btn btn-secondary">Back to Settings</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Role Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update Role</button>
            </form>
        </div>
    </div>
</x-user-page>
