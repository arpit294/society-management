<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Helpers\ModuleHelper;
use Spatie\Permission\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreSettingsRequest;
use App\Models\PropertyType;
use \Illuminate\Support\Facades\Auth;
// (Auth facade is used only for IDE typing; runtime continues to use the existing abort_if logic.)

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        abort_if(! Auth::user()->can('setting_view'), 403);
        try {
            // Fetch all settings and merge over defaults so the UI is never blank
            $settings = array_merge(Setting::defaults(), Setting::getAll());

            // Fetch roles (excluding Admin) along with eager loaded permissions
            $roles = Role::whereNotIn('name', ['Admin'])->with('permissions')->get();

            // Fetch user counts grouped by role in a single query to eliminate N+1 queries
            $userCounts = User::selectRaw('role, count(*) as count')->groupBy('role')->pluck('count', 'role');

            // Map over roles to add the count from the pre-fetched array
            $roles->map(function ($role) use ($userCounts) {
                $role->setAttribute('users_count', $userCounts[$role->name] ?? 0);
                return $role;
            });


            // Fetch all permissions grouped by their module name from the config
            $permissionsByModule = config('permissions.modules', []);
            $isFinanceActive = ModuleHelper::isFinanceActive();

            if (!$isFinanceActive) {
                unset(
                    $permissionsByModule['Maintenance Bills'],
                    $permissionsByModule['Expense Categories'],
                    $permissionsByModule['Expenses'],
                    $permissionsByModule['Name Transfer Bills']
                );
            }

            $propertyTypes = PropertyType::orderBy('id')->get();

            return view('settings.index', compact('settings', 'roles', 'permissionsByModule', 'propertyTypes', 'isFinanceActive'));
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
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
            $activeModule = $request->input('active_module');
            $data = $request->except(['_token', '_method', 'active_module']);


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
            Setting::clearCache();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Settings updated successfully.', 'active_module' => !empty($activeModule) ? ltrim($activeModule, '#') : null]);
            }

            $redirect = redirect()->back();
            if (!empty($activeModule)) {
                $cleanModule = ltrim($activeModule, '#');
                $url = preg_replace('/#.*$/', '', $redirect->getTargetUrl()) . '#' . $cleanModule;
                $redirect->setTargetUrl($url);
            }

            return $redirect->with('success', 'Settings updated successfully.')->with('active_module', !empty($activeModule) ? ltrim($activeModule, '#') : null);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException || $e instanceof HttpExceptionInterface) {
                throw $e;
            }
            Log::error('Error in SettingController@store: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

            $activeModule = $request->input('active_module');
            $redirect = redirect()->back();
            if (!empty($activeModule)) {
                $cleanModule = ltrim($activeModule, '#');
                $url = preg_replace('/#.*$/', '', $redirect->getTargetUrl()) . '#' . $cleanModule;
                $redirect->setTargetUrl($url);
            }

            return $redirect->with('error', 'An error occurred updating settings: ' . $e->getMessage())->with('active_module', !empty($activeModule) ? ltrim($activeModule, '#') : null);
        }
    }

    public function databaseBackup()
    {
        abort_if(\Illuminate\Support\Facades\Gate::denies('setting_edit'), 403, 'Unauthorized access.');

        try {
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '3306');

            $dump = new \Ifsnop\Mysqldump\Mysqldump(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password
            );

            $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = storage_path('app/' . $fileName);

            $dump->start($filePath);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}
