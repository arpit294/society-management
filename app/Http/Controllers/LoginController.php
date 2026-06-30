<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        try {
            return view('authentication.login');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in LoginController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            // Attempt to authenticate the user with the provided credentials
            if (! Auth::attempt($request->validated())) {
                throw ValidationException::withMessages([
                    'email' => 'The provided credentials do not match our records.',
                ]);
            }

            $request->session()->regenerate();

            return redirect('/')->with('success', 'Logged in successfully.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in LoginController@store: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred during login: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            Auth::logout();
            // Invalidate the session and regenerate the CSRF token to prevent session fixation attacks
            $request->session()->invalidate();
            // Regenerate the CSRF token to ensure the old token cannot be used after logout
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Logged out successfully.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in LoginController@destroy: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred during logout: ' . $e->getMessage());
        }
    }
}
