<x-layout>

    @push('styles')
        <style>
            .auth-page {
                min-height: 100vh;
                background: #ffffff;
            }

            .auth-card {
                width: min(100%, 440px);
            }

            .login-card {
                background: #fff;
                border: 1px solid #e5e5e5;
                border-radius: 20px;
                overflow: hidden;
            }

            .card-header {
                background: #000 !important;
                border-bottom: 1px solid #000;
                padding: 25px;
            }

            .card-header h4,
            .card-header p {
                color: #fff;
            }

            .card-body {
                padding: 35px;
            }

            .form-label {
                color: #000;
                font-weight: 600;
            }

            .form-control {
                background: #fff;
                border: 1px solid #d1d5db;
                color: #000;
                border-radius: 10px;
                height: 48px;
            }

            .form-control::placeholder {
                color: #6b7280;
            }

            .form-control:focus {
                background: #fff;
                color: #000;
                border-color: #000;
                box-shadow: none;
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

            .text-muted {
                color: #6b7280 !important;
            }

            .text-muted a {
                color: #000 !important;
                font-weight: 600;
                text-decoration: none;
            }

            .alert-success {
                background: #f3f4f6;
                color: #000;
                border: 1px solid #d1d5db;
                border-radius: 10px;
            }

            .invalid-feedback {
                color: #dc2626;
            }
        </style>
    @endpush

    <main class="auth-page d-flex align-items-center justify-content-center px-3">

        <div class="auth-card">

            <div class="card login-card shadow-sm border-0">

                <div class="card-header">
                    <h4 class="mb-1">Login</h4>

                    <p class="mb-0 small">
                        Sign in to your society management account.
                    </p>
                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('login.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">
                                Email
                            </label>

                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email"
                                value="{{ old('email') }}">

                            @error('email')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                Password
                            </label>

                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password">

                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Login
                            </button>
                        </div>

                        <p class="text-center text-muted small mb-0 mt-4">
                            Don't have an account?
                            <a href="{{ route('register') }}">
                                Register here
                            </a>
                        </p>

                    </form>

                </div>

            </div>

        </div>

    </main>

</x-layout>

{{-- --}}
