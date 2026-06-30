<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use Spatie\Permission\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreSettingsRequest;


// (Auth facade is used only for IDE typing; runtime continues to use the existing abort_if logic.)

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
        abort_if(! \Auth::user()->can('setting_view'), 403);
        try {
            // Fetch all settings and merge over defaults so the UI is never blank
            $settings = array_merge(Setting::defaults(), Setting::all()->pluck('value', 'key')->toArray());

            // Fetch roles (excluding Admin)
            $roles = Role::whereNotIn('name', ['Admin'])->get();

            // Map over roles to add the count from the User model's role column
            $roles->map(function ($role) {
                $role->setAttribute('users_count', User::where('role', $role->name)->count());
                return $role;
            });


            // Fetch all permissions grouped by their module name from the config
            $permissionsByModule = config('permissions.modules', []);

            return view('settings.index', compact('settings', 'roles', 'permissionsByModule'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in SettingController@index: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store or update settings in the database.
     * Iterates through the request and upserts each setting key.
     *
     * @return RedirectResponse
     */
    public function store(StoreSettingsRequest $request)
    {

        abort_if(! auth()->user()->can('setting_edit'), 403);
        try {
            $data = $request->except(['_token', '_method']);


            if (isset($data['currency'])) {
                $currencies = CurrencyHelper::getAvailableCurrencies();
                if (! isset($currencies[$data['currency']])) {
                    $data['currency'] = 'INR';
                }
                $data['currency_symbol'] = $currencies[$data['currency']]['symbol'] ?? $currencies['INR']['symbol'];
            }

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
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in SettingController@store: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'An error occurred updating settings: ' . $e->getMessage());
        }
    }
}
