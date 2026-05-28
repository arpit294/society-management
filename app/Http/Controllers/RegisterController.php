<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('authentication.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => ['required', Rule::in(['owner', 'rental', 'security', 'committee_member'])],
            'aadhar_id' => 'required|string|max:20',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create($validated);

        return redirect()
            ->route('login')
            ->with('success', 'Registration completed successfully.');
    }
}
