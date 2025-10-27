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
        // Check if user has set a language preference in session
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        } else {
            // Set default locale to English
            app()->setLocale('en');
        }

        // Definir timezone baseado nas settings ou usar UTC como padrão
        try {
            $timezone = Setting::get('app_timezone', 'UTC');
            date_default_timezone_set($timezone);
            Config::set('app.timezone', $timezone);
        } catch (\Exception $e) {
            // Se as settings não estiverem disponíveis, usar UTC como padrão
            date_default_timezone_set('UTC');
        }

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
