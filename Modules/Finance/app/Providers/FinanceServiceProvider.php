<?php

namespace Modules\Finance\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class FinanceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Finance';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'finance';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        parent::boot();

        // Register the module views directory directly in View finder
        // so view('maintenance_bills.index') works seamlessly across any core project
        $moduleViews = module_path($this->name, 'resources/views');
        if (is_dir($moduleViews) && isset($this->app['view'])) {
            $this->app['view']->addLocation($moduleViews);
        }
    }
}
