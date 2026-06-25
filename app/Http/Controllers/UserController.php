<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * Uses Yajra DataTables to handle AJAX rendering automatically.
     */
    public function index(UsersDataTable $dataTable)
    {
        abort_if(! auth()->user()->can('user_view'), 403);

        return $dataTable->render('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(! auth()->user()->can('user_create'), 403);
        if (request()->ajax()) {
            return view('users.create', [
                'user' => null,
                'action' => route('users.store'),
            ]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Store a newly created resource in storage.
     */ 
    public function store(StoreUserRequest $request)
    {
        abort_if(! auth()->user()->can('user_create'), 403);
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
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        abort_if(! auth()->user()->can('user_edit'), 403);
        if (request()->ajax()) {
            return view('users.edit', [
                'user' => $user,
                'action' => route('users.update', $user),
            ]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        abort_if(! auth()->user()->can('user_edit'), 403);
        $validatedData = $request->validated();

        // TRICKY: If the user didn't type a new password in the edit form,
        // we remove 'password' from the array so we don't accidentally overwrite
        // their current password with an empty string!
        // (Note: Hashing is handled automatically by the 'hashed' cast in the User model)
        // Prevent removing the last secretary
        if ($user->role === 'secretary' && $validatedData['role'] !== 'secretary') {
            $secretaryCount = User::where('role', 'secretary')->count();
            if ($secretaryCount <= 1) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot remove the secretary role from the last remaining secretary.',
                    ], 403);
                }

                return redirect()->back()->with('error', 'You cannot remove the secretary role from the last remaining secretary.');
            }
        }

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
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        abort_if(! auth()->user()->can('user_delete'), 403);
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
