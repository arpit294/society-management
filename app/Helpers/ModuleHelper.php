<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;

class ModuleHelper
{
    /**
     * Check if a specific Nwidart module is installed and enabled.
     *
     * @param string $name
     * @return bool
     */
    public static function isModuleActive(string $name): bool
    {
        try {
            if (class_exists(Module::class)) {
                return Module::has($name) && Module::isEnabled($name);
            }
        } catch (\Throwable $e) {
            // Fallback gracefully if module system is not configured
        }

        return false;
    }

    /**
     * Check if the Finance module is active.
     * Checks Nwidart status as well as availability of key routes/classes.
     *
     * @return bool
     */
    public static function isFinanceActive(): bool
    {
        if (self::isModuleActive('Finance')) {
            return true;
        }

        // Secondary check: if financial routes are registered in the application
        if (Route::has('maintenance-bills.index') || Route::has('expenses.index')) {
            return true;
        }

        return false;
    }

    /**
     * Check if a model class exists and its underlying database table exists.
     *
     * @param string $modelClass Full class name e.g. App\Models\MaintenanceBill
     * @param string|null $table Optional table name. If null, will try to infer from model instance.
     * @return bool
     */
    public static function hasModel(string $modelClass, ?string $table = null): bool
    {
        if (! class_exists($modelClass)) {
            return false;
        }

        try {
            if (! $table) {
                $instance = new $modelClass;
                $table = $instance->getTable();
            }

            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
