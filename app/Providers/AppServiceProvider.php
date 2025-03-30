<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use App\Livewire\Components\UpdateChecker;
use App\Livewire\Components\UpdateNotification;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;

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
        // Set default locale to English
        app()->setLocale('en');

        // Set timezone to UTC as default
        date_default_timezone_set('UTC');

        // Update app version from database
        try {
            $dbVersion = Setting::get('app_version');
            if (!empty($dbVersion)) {
                Config::set('app.version', $dbVersion);
            }
        } catch (\Exception $e) {
            // If database is not yet available (during installation), just use the default version
        }

        // Registrar componentes Livewire
        Livewire::component('components.update-checker', UpdateChecker::class);
        Livewire::component('components.update-notification', UpdateNotification::class);

        // Registre outros componentes conforme necessário
    }
}
