<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Get a setting value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        $settings = Cache::rememberForever('global_settings', function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (! function_exists('all_permissions')) {
    /**
     * @return list<string>
     */
    function all_permissions(): array
    {
        $permissions = [];

        foreach (config('permissions.modules', []) as $modulePermissions) {
            $permissions = array_merge($permissions, $modulePermissions);
        }

        return $permissions;
    }
}
