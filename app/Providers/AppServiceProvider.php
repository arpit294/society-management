<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! app()->runningInConsole() && app()->bound('debugbar')) {
            try {
                $enabled = (\App\Models\Setting::get('enable_debugger', '0') == '1');
                config(['debugbar.enabled' => $enabled]);
                if ($enabled) {
                    app('debugbar')->enable();
                } else {
                    app('debugbar')->disable();
                }
            } catch (\Exception $e) {
                // Ignore if database/table not ready during startup
            }
        }
    }
}
