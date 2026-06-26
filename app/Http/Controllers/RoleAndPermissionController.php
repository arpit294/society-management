<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleAndPermissionController extends Controller
{
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        return redirect(route('settings.index') . '#role-settings')
            ->with('success', 'Role created successfully. Select the role to assign permissions.')
            ->with('created_role_id', $role->id);
    }

    public function edit(Role $role): View
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
        return view('roles.edit', compact('role'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
        $attributes = ['name' => $request->validated('name')];

        $role->update($attributes);
        $role->syncPermissions($request->input('permissions', []));

        return redirect(route('settings.index') . '#role-settings')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        abort_if(! auth()->user()->can('setting_edit'), 403);
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
    }
}
