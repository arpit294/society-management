<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('authentication.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Attempt to authenticate the user with the provided credentials
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        return redirect('/')->with('success', 'Logged in successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        // Invalidate the session and regenerate the CSRF token to prevent session fixation attacks
        $request->session()->invalidate();
        // Regenerate the CSRF token to ensure the old token cannot be used after logout
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
