<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        $settings = \Illuminate\Support\Facades\Cache::rememberForever('global_settings', function () {
            return self::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function allPermissions(): array
    {
        $permissions = [];
        foreach (config('permissions.modules', []) as $modulePermissions) {
            $permissions = array_merge($permissions, $modulePermissions);
        }
        return $permissions;
    }
}
