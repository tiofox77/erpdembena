<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\MaintenanceDashboard;
use App\Livewire\MaintenanceEquipment;
use App\Livewire\MaintenanceTask;
use App\Livewire\MaintenanceScheduling;
use App\Livewire\MaintenanceLineArea;

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
        Livewire::component('maintenance-dashboard', MaintenanceDashboard::class);
        Livewire::component('maintenance-equipment', MaintenanceEquipment::class);
        Livewire::component('maintenance-task', MaintenanceTask::class);
        Livewire::component('maintenance-scheduling', MaintenanceScheduling::class);
        Livewire::component('maintenance-line-area', MaintenanceLineArea::class);
    }
}
