<?php

namespace App\Providers;

use App\Models\GlobalSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class GlobalSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $globalSettings = Cache::remember('global_settings',1440, function () {
                return GlobalSettings::first();
            });
            $view->with('globalSettings', $globalSettings);
        });
    }
}
