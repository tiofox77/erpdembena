<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\MaintenanceDashboard;
use App\Livewire\MaintenanceEquipment;
use App\Livewire\MaintenanceTaskComponent;
use App\Livewire\MaintenancePlan;
use App\Livewire\MaintenanceCategory;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Maintenance Management Routes
Route::prefix('maintenance')->name('maintenance.')->group(function() {
    // Rotas para componentes Livewire
    Route::get('/dashboard', MaintenanceDashboard::class)->name('dashboard');
    Route::get('/equipment', App\Livewire\MaintenanceEquipmentController::class)->name('equipment');
    Route::get('/task', App\Livewire\MaintenanceTaskComponent::class)->name('task');
    Route::get('/plan', MaintenancePlan::class)->name('plan');
    Route::get('/category', MaintenanceCategory::class)->name('category');
    Route::get('/linearea', App\Livewire\Components\MaintenanceLineArea::class)->name('linearea');
    Route::get('/reports', function() {
        return redirect()->route('reports.dashboard');
    })->name('reports');
    Route::get('/users', App\Livewire\UserManagement::class)->name('users');
    Route::get('/roles', App\Livewire\RolePermissions::class)->name('roles');
    Route::get('/holidays', App\Livewire\HolidayManagement::class)->name('holidays');
    Route::get('/settings', function() {
        return redirect()->route('settings.system');
    })->name('settings');

    // Novas rotas
    Route::get('/corrective', App\Livewire\Maintenance\CorrectiveMaintenance::class)->name('corrective');

    // API routes
    // Route::post('/api/notification/mark-as-read', [MaintenanceController::class, 'markNotificationAsRead'])->name('api.notification.mark-as-read');

    // Failure Modes
    Route::get('/failure-modes', App\Livewire\Maintenance\FailureModes::class)->name('failure-modes');
    Route::get('/failure-mode-categories', App\Livewire\Maintenance\FailureModeCategories::class)->name('failure-mode-categories');

    // Failure Causes
    Route::get('/failure-causes', App\Livewire\Maintenance\FailureCauses::class)->name('failure-causes');
    Route::get('/failure-cause-categories', App\Livewire\Maintenance\FailureCauseCategories::class)->name('failure-cause-categories');
});

// Rotas de Relatórios
Route::prefix('reports')->name('reports.')->group(function () {
    // Histórico de Manutenção
    Route::get('/history', App\Livewire\Reports\MaintenanceHistory::class)->name('history');

    // Dashboard de Relatórios
    Route::get('/dashboard', App\Livewire\Reports\ReportsDashboard::class)->name('dashboard');
});

// Rota de Gerenciamento de Usuários
Route::get('/users', function() {
    return redirect()->route('maintenance.users');
})->name('users.management');

// Rota de Permissões de Acesso
Route::get('/permissions', function() {
    return redirect()->route('maintenance.roles');
})->name('permissions.management');

// Rota de Gerenciamento de Feriados
Route::get('/holidays', function() {
    return redirect()->route('maintenance.holidays');
})->name('holidays.management');

// Rotas de Configurações
Route::prefix('settings')->name('settings.')->group(function () {
    // Configurações do Sistema
    Route::get('/system', App\Livewire\Settings\SystemSettings::class)->name('system');
});
