<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = config('permissions.modules', []);

        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'permissions' => $request->input('permissions', [])
        ]);

        return redirect(route('settings.index').'#role-settings')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = config('permissions.modules', []);
        $rolePermissions = $role->permissions ?? [];

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update([
            'name' => $request->validated('name'),
            'permissions' => $request->input('permissions', [])
        ]);

        return redirect(route('settings.index').'#role-settings')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect(route('settings.index').'#role-settings')->with('error', 'Cannot delete Super Admin role.');
        }

        $role->delete();

        return redirect(route('settings.index').'#role-settings')->with('success', 'Role deleted successfully.');
    }
}
