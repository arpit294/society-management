<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class RegisterController extends Controller
{
    public function create(): View
    {
        try {
            return view('authentication.register');
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in RegisterController@create: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        try {
            $user = User::create($request->validated());
            if ($user && $user->role && class_exists(\Spatie\Permission\Models\Role::class)) {
                try {
                    $user->assignRole($user->role);
                } catch (\Exception $ex) {
                    Log::warning('Could not assign spatie role during registration: ' . $ex->getMessage());
                }
            }

            return redirect()
                ->route('login')
                ->with('success', 'Registration completed successfully.');
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in RegisterController@store: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred during registration: ' . $e->getMessage());
        }
    }
}
