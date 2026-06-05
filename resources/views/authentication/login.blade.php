<x-layout>
    <div id="users-toasts" class="users-toast-container" aria-live="polite" aria-atomic="true"></div>

    @if (session('success'))
        <div id="users-toast-source" data-message="{{ e(session('success')) }}" data-type="success" hidden></div>
    @elseif ($errors->any())
        <div id="users-toast-source" data-message="{{ e($errors->first()) }}" data-type="danger" hidden></div>
    @endif

    @push('styles')
        <style>
            .auth-page {
                min-height: 100vh;
                background: #f3f4f7;
            }

            .auth-card {
                width: min(100%, 440px);
            }

            .login-card {
                background: #ffffff;
                border: 1px solid #dbdfe6;
                border-radius: 16px;
                overflow: hidden;
            }

            .card-header {
                background: #5856d6 !important;
                border-bottom: 1px solid #5856d6;
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
                color: #212631;
                font-weight: 600;
            }

            .form-control {
                background: #ffffff;
                border: 1px solid #dbdfe6;
                color: #212631;
                border-radius: 10px;
                height: 48px;
            }

            .form-control::placeholder {
                color: #6d7d9c;
            }

            .form-control:focus {
                background: #ffffff;
                color: #212631;
                border-color: #5856d6;
                box-shadow: 0 0 0 0.25rem rgba(88, 86, 214, 0.25);
            }

            .btn-primary {
                background: #5856d6;
                color: #ffffff;
                border: 1px solid #5856d6;
                border-radius: 10px;
                padding: 10px 22px;
                font-weight: 600;
            }

            .btn-primary:hover {
                background: #4644b8;
                border-color: #4644b8;
                color: #ffffff;
            }

            .text-muted {
                color: #6d7d9c !important;
            }

            .text-muted a {
                color: #5856d6 !important;
                font-weight: 600;
                text-decoration: none;
            }

            .invalid-feedback {
                color: #e55353;
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

                        <p class="text-center text-muted small mb-0 mt-3">
                            <a href="{{ route('password.request') }}">Forgot password?</a>
                        </p>

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
