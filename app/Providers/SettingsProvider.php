<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class SettingsProvider extends ServiceProvider
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
    public function boot()
    {
        // Fetch the settings from the database
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        // Store the settings in the config
        config(['app.settings' => $settings]);
    }
}
