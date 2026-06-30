<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RoleAndPermissionController extends Controller
{
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
        try {
            $role = Role::create([
                'name' => $request->validated('name'),
                'guard_name' => 'web',
            ]);

            return redirect(route('settings.index') . '#role-settings')
                ->with('success', 'Role created successfully. Select the role to assign permissions.')
                ->with('created_role_id', $role->id);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in RoleAndPermissionController@store: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect(route('settings.index') . '#role-settings')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function edit(Role $role): View
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
        try {
            return view('roles.edit', compact('role'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in RoleAndPermissionController@edit: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
        try {
            $attributes = ['name' => $request->validated('name')];

            $role->update($attributes);
            $role->syncPermissions($request->input('permissions', []));

            return redirect(route('settings.index') . '#role-settings')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in RoleAndPermissionController@update: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect(route('settings.index') . '#role-settings')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
        try {
            if ($role->name === 'Admin') {
                if (request()->expectsJson()) {
                    return response()->json(['message' => 'Cannot delete Admin role.'], 403);
                }

                return redirect(route('settings.index') . '#role-settings')->with('error', 'Cannot delete Admin role.');
            }

            $role->delete();

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Role deleted successfully.']);
            }

            return redirect(route('settings.index') . '#role-settings')->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in RoleAndPermissionController@destroy: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect(route('settings.index') . '#role-settings')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
