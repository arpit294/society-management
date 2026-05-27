<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\DataTables\UsersDataTable;

class UserController extends Controller
{
    /**
     * Display the users listing page with DataTable.
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('users.index');
    }

    /**
     * Fetch user data for DataTables using AJAX.
     */
    public function data()
    {
        return DataTables::eloquent(User::query())

            // Add automatic serial number column
            ->addIndexColumn()

            // Add custom ID column
            ->addColumn('id', function (User $user) {
                return $user->id;
            })

            // Generate edit URL for each user
            ->addColumn('edit_url', function (User $user) {
                return route('users.edit', $user);
            })

            // Generate delete URL for each user
            ->addColumn('delete_url', function (User $user) {
                return route('users.destroy', $user);
            })

            // Format role name properly
            ->editColumn('role', function (User $user) {
                return $user->role
                    ? ucfirst(str_replace('_', ' ', $user->role))
                    : '-';
            })

            // Show phone number or default value
            ->editColumn('phone', function (User $user) {
                return $user->phone ?: '-';
            })

            // Capitalize status text
            ->editColumn('status', function (User $user) {
                return ucfirst($user->status);
            })

            // Return JSON response
            ->make(true);
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        // Return modal form if request is AJAX
        if (request()->ajax()) {
            return view('users.create', [
                'user' => null,
                'action' => route('users.store'),
            ]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Store new user data into database.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => [
                'required',
                Rule::in([
                    'owner',
                    'rental',
                    'security',
                    'committee_member'
                ])
            ],
            'password' => 'required|string|min:6',
            'aadhar_id' => 'required|string|max:20',
            'status' => [
                'required',
                Rule::in(['active', 'inactive'])
            ],
        ]);

        // Create new user
        $user = User::create($validated);

        // Return JSON response for AJAX request
        if ($request->ajax()) {
            return response()->json([
                'message' => 'User created successfully.',
            ]);
        }

        // Redirect with success message
        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show edit form for selected user.
     */
    public function edit(User $user)
    {
        // Return edit modal form if request is AJAX
        if (request()->ajax()) {
            return view('users.edit', [
                'user' => $user,
                'action' => route('users.update', $user),
            ]);
        }

        return redirect()->route('users.index');
    }

    /**
     * Update existing user data.
     */
    public function update(Request $request, User $user)
    {
        // Validate updated data
        $validated = $request->validate([
            'name' => 'required|string|max:255',

            // Ignore current user email while updating
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id)
            ],

            'phone' => 'required|string|max:20',

            'role' => [
                'required',
                Rule::in([
                    'owner',
                    'rental',
                    'security',
                    'committee_member'
                ])
            ],

            'password' => 'required|string|min:6',
            'aadhar_id' => 'required|string|max:20',

            'status' => [
                'required',
                Rule::in(['active', 'inactive'])
            ],
        ]);

        // Update user data
        $user->update($validated);

        // Return JSON response for AJAX request
        if ($request->ajax()) {
            return response()->json([
                'message' => 'User updated successfully.',
            ]);
        }

        // Redirect with success message
        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete selected user from database.
     */
    public function destroy(Request $request, User $user)
    {
        // Store user ID before delete
        $id = $user->id;

        // Delete user
        $user->delete();

        // Return JSON response for AJAX request
        if ($request->ajax()) {
            return response()->json([
                'message' => 'User deleted successfully.',
                'id' => $id,
            ]);
        }

        // Redirect with success message
        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
