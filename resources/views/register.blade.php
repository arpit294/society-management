<x-layout>

    @push('styles')
        <style>
            .auth-page {
                min-height: 100vh;
                background: #ffffff;
            }

            .register-card {
                background: #fff;
                border: 1px solid #e5e5e5;
                border-radius: 20px;
                overflow: hidden;
            }

            .card-header {
                background: #000 !important;
                border-bottom: 1px solid #000;
            }

            .card-header h3,
            .card-header p {
                color: #fff;
            }

            .form-label {
                color: #000;
                font-weight: 600;
            }

            .form-control,
            .form-select {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #000;
                border-radius: 10px;
                height: 48px;
            }

            .form-control::placeholder {
                color: #6b7280;
            }

            .form-control:focus,
            .form-select:focus {
                background: #fff;
                color: #000;
                border-color: #000;
                box-shadow: none;
            }

            .form-select option {
                background: #fff;
                color: #000;
            }

            .btn-primary {
                background: #000;
                color: #fff;
                border: none;
                border-radius: 10px;
                padding: 10px 22px;
                font-weight: 600;
            }

            .btn-primary:hover {
                background: #222;
                color: #fff;
            }

            .btn-outline-secondary {
                border-radius: 10px;
                padding: 10px 22px;
                border: 1px solid #000;
                color: #000;
            }

            .btn-outline-secondary:hover {
                background: #000;
                color: #fff;
            }

            .text-muted {
                color: #6b7280 !important;
            }

            .text-muted a {
                color: #000 !important;
                font-weight: 600;
            }

            .alert-success {
                background: #f3f4f6;
                color: #000;
                border: 1px solid #d1d5db;
            }

            .invalid-feedback {
                color: #dc2626;
            }
        </style>
    @endpush

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

                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <form action="{{ route('register.store') }}" method="POST">
                                @csrf

                                <div class="row">

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">
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
                                        <label class="form-label fw-semibold">
                                            Email
                                        </label>

                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="Enter email" value="{{ old('email') }}">

                                        @error('email')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">
                                            Phone
                                        </label>

                                        <input type="text" name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            placeholder="Enter phone number" value="{{ old('phone') }}">

                                        @error('phone')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">
                                            Role
                                        </label>

                                        <select name="role" class="form-select @error('role') is-invalid @enderror">

                                            <option value="">
                                                Select role
                                            </option>

                                            @foreach (['owner', 'rental', 'security', 'committee_member'] as $role)
                                                <option value="{{ $role }}" @selected(old('role') === $role)>
                                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
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
                                        <label class="form-label fw-semibold">
                                            Aadhar ID
                                        </label>

                                        <input type="text" name="aadhar_id"
                                            class="form-control @error('aadhar_id') is-invalid @enderror"
                                            placeholder="Enter Aadhar ID" value="{{ old('aadhar_id') }}">

                                        @error('aadhar_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">
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
                                        <label class="form-label fw-semibold">
                                            Password
                                        </label>

                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Enter password">

                                        @error('password')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">
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
