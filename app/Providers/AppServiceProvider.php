<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use App\Livewire\Components\UpdateChecker;
use App\Livewire\Components\UpdateNotification;

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
        // Registrar componentes Livewire
        Livewire::component('components.update-checker', UpdateChecker::class);
        Livewire::component('components.update-notification', UpdateNotification::class);

        // Registre outros componentes conforme necessário
    }
}
