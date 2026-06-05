<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     * Uses Yajra DataTables to handle AJAX rendering automatically.
     *
     * @return mixed
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('users.index');
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View|RedirectResponse
     */
    public function create()
    {
        if (request()->ajax()) {
            return view('users.create', [
                'user' => null,
                'action' => route('users.store'),
            ]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Store a newly created user in storage.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     *
     * @return View|RedirectResponse
     */
    public function edit(User $user)
    {
        if (request()->ajax()) {
            return view('users.edit', [
                'user' => $user,
                'action' => route('users.update', $user),
            ]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Update the specified user in storage.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        // TRICKY: If the user didn't type a new password in the edit form,
        // we remove 'password' from the array so we don't accidentally overwrite
        // their current password with an empty string!
        // (Note: Hashing is handled automatically by the 'hashed' cast in the User model)
        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                ], 403);
            }

            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $id = $user->id;
        $user->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
                'id' => $id,
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
