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
        return $dataTable->render('users.index');
    }

    /**
     * Show the form for creating a new resource.
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
     * Store a newly created resource in storage.
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
     * Show the form for editing the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
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

    /**
     * Bulk Delete Users
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('select', []);

        // TRICKY: Datatables might send the IDs as a single comma-separated string (e.g. "1,2,3") 
        // rather than an array. We need to explode it into a real array first so Eloquent can use it.
        if (! is_array($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        if (count($ids) > 0) {
            $deletedCount = User::destroy($ids);

            return response()->json([
                'success' => true,
                'message' => $deletedCount.' users deleted successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No users selected.',
        ], 400);
    }

    /**
     * Bulk Update Users
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'select' => 'required',
            'status' => 'required|string|in:active,inactive',
        ]);

        $ids = $request->input('select', []);
        $status = $request->input('status');

        // TRICKY: Same as bulkDelete, convert comma-separated string to array if necessary.
        if (! is_array($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        if (count($ids) > 0) {
            $updatedCount = User::whereIn('id', $ids)->update(['status' => $status]);

            return response()->json([
                'success' => true,
                'message' => $updatedCount.' users updated successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No valid users selected for update.',
        ], 422);
    }
}
