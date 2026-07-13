<x-layout>



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

                            <div class="input-group password-input-group">
                                <input type="password" name="password" id="login-password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter your password" autocomplete="current-password">
                                <span class="input-group-text d-flex align-items-center justify-content-center">
                                    <button type="button" class="toggle-password" tabindex="-1" aria-label="Show password" title="Show password">
                                        <i class="fa-regular fa-eye fs-5"></i>
                                    </button>
                                </span>
                            </div>

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
