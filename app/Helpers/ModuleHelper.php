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
        return self::isModuleActive('Finance');
    }

    /**
     * Resolve a model class from either Modules or App namespaces.
     *
     * @param string $shortName e.g. MaintenanceBill, Expense, ExpenseCategory, NameTransferBill, Maintenance, PrepaidMaintenance
     * @return string|null
     */
    public static function getModel(string $shortName): ?string
    {
        $moduleClass = "Modules\\Finance\\Models\\{$shortName}";
        if (class_exists($moduleClass)) {
            return $moduleClass;
        }

        $appClass = "App\\Models\\{$shortName}";
        if (class_exists($appClass)) {
            return $appClass;
        }

        return null;
    }

    /**
     * Check if a model class exists and its underlying database table exists.
     *
     * @param string|null $modelClass Full class name e.g. App\Models\MaintenanceBill
     * @param string|null $table Optional table name. If null, will try to infer from model instance.
     * @return bool
     */
    public static function hasModel(?string $modelClass, ?string $table = null): bool
    {
        if (! $modelClass || ! class_exists($modelClass)) {
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
