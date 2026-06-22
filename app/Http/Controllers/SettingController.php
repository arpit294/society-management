<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Class SettingController
 *
 * Manages global application settings (such as Penalty and Discount rates).
 * Values are stored as key-value pairs in the database.
 */
class SettingController extends Controller
{
    /**
     * Display a listing of all settings.
     *
     * @return View
     */
    public function index()
    {
        // Fetch all settings and convert to a flat key-value array for easy view binding
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Fetch roles (excluding Admin)
        $roles = Role::whereNotIn('name', ['Admin'])->get();

        // Map over roles to add the count from the User model's role column
        $roles->map(function ($role) {
            $role->users_count = User::where('role', $role->name)->count();

            return $role;
        });

        // Fetch all permissions grouped by their module name from the config
        $permissionsByModule = config('permissions.modules', []);

        return view('settings.index', compact('settings', 'roles', 'permissionsByModule'));
    }

    /**
     * Store or update settings in the database.
     * Iterates through the request and upserts each setting key.
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        // Update or create settings based on the provided data
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Clear the global settings cache so the new values apply immediately across the app
        Cache::forget('global_settings');

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
