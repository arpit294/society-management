<?php

namespace Modules\Finance;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(__DIR__ . '/../config/finance.php')) {
            $this->mergeConfigFrom(
                __DIR__ . '/../config/finance.php', 'finance'
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load module migrations
        if (is_dir(__DIR__ . '/../database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        // Load views with 'finance' namespace and also add to default view search paths
        if (is_dir(__DIR__ . '/../resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'finance');
            View::addLocation(__DIR__ . '/../resources/views');
        }

        // Register Web routes
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            Route::middleware('web')
                ->group(__DIR__ . '/../routes/web.php');
        }

        // Register API routes if present
        if (file_exists(__DIR__ . '/../routes/api.php')) {
            Route::middleware(['api'])
                ->prefix('api/finance')
                ->name('api.finance.')
                ->group(__DIR__ . '/../routes/api.php');
        }
    }
}
