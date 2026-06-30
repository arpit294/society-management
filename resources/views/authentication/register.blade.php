<x-layout>
    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @elseif ($errors->any())
        <div id="users-toast-source" data-message="{{ e($errors->first()) }}" data-type="danger" hidden></div>
    @endif



    <main class="auth-page d-flex align-items-center justify-content-center py-5">

        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-8">

                    <div class="card register-card border-0 shadow-lg">

                        <div class="card-header bg-primary text-white text-center py-4">
                            <h3 class="mb-1">Register User</h3>
                            <p class="mb-0 small">
                                Create a new society user account
                            </p>
                        </div>

                        <div class="card-body p-4 p-md-5">

                            <form action="{{ route('register.store') }}" method="POST">
                                @csrf

                                <div class="row">

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-primary fw-semibold">
                                            Name
                                        </label>

                                        <input type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            placeholder="Enter full name" value="{{ old('name') }}">

                                        @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-primary fw-semibold">
                                            Email
                                        </label>

                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="user@example.com" value="{{ old('email') }}">

                                        @error('email')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-info fw-semibold">
                                            Phone
                                        </label>

                                        <input type="tel" name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            placeholder="+91 9876543210" value="{{ old('phone') }}">

                                        @error('phone')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-warning fw-semibold">
                                            Role
                                        </label>

                                        <select class="form-select @error('role') is-invalid @enderror" id="role"
                                            name="role" required>
                                            <option value="">Select role</option>
                                            @foreach (config('roles.self_register') as $role)
                                                <option value="{{ $role }}"
                                                    {{ old('role') == $role ? 'selected' : '' }}>
                                                    {{ config('roles.labels.' . $role, ucfirst(str_replace('_', ' ', $role))) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('role')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-success fw-semibold">
                                            Aadhar ID <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" name="aadhar_id"
                                            class="form-control @error('aadhar_id') is-invalid @enderror"
                                            placeholder="12-digit Aadhar" inputmode="numeric" pattern="[0-9]{12}"
                                            maxlength="12" value="{{ old('aadhar_id') }}">
                                        <small class="field-hint">Must be exactly 12 digits</small>

                                        @error('aadhar_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-warning fw-semibold">
                                            Status
                                        </label>

                                        <select name="status"
                                            class="form-select @error('status') is-invalid @enderror">

                                            @foreach (['active', 'inactive'] as $status)
                                                <option value="{{ $status }}" @selected(old('status', 'active') === $status)>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('status')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-danger fw-semibold">
                                            Password
                                        </label>

                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Enter secure password">

                                        @error('password')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-danger fw-semibold">
                                            Confirm Password
                                        </label>

                                        <input type="password" name="password_confirmation" class="form-control"
                                            placeholder="Confirm password">
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-3">

                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>

                                    <button type="submit" class="btn btn-primary">
                                        Register
                                    </button>
                                </div>

                                <div class="text-center mt-4">
                                    <small class="text-muted">
                                        Already have an account?
                                        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                                            Login here
                                        </a>
                                    </small>
                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>
        </div>

    </main>

</x-layout>
