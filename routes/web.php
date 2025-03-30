<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\MaintenanceDashboard;
use App\Livewire\MaintenanceEquipment;
use App\Livewire\MaintenanceTaskComponent;
use App\Livewire\MaintenancePlan;
use App\Livewire\MaintenanceCategory;
use App\Livewire\EquipmentParts;

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

// Rotas de autenticação
Auth::routes();

// Rota principal - redireciona para o dashboard se estiver logado ou para login se não estiver
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

// Todas as rotas protegidas pelo middleware auth (usuário deve estar autenticado)
Route::middleware(['auth'])->group(function () {
    // Main dashboard route - alias to maintenance.dashboard
    Route::get('/dashboard', MaintenanceDashboard::class)->name('dashboard');

    // Maintenance Management Routes
    Route::prefix('maintenance')->name('maintenance.')->group(function() {
        // Rotas para componentes Livewire
        Route::get('/dashboard', MaintenanceDashboard::class)->name('dashboard');

        // Rotas de Equipamentos - Requer permissão equipment.view
        Route::middleware(['permission:equipment.view'])->group(function() {
            Route::get('/equipment', App\Livewire\MaintenanceEquipmentController::class)->name('equipment');
        });

        // Rotas de Tarefas - Requer permissão preventive.view
        Route::middleware(['permission:preventive.view'])->group(function() {
            Route::get('/task', App\Livewire\MaintenanceTaskComponent::class)->name('task');
        });

        // Rotas de Planos de Manutenção - Requer permissão preventive.view
        Route::middleware(['permission:preventive.view'])->group(function() {
            Route::get('/plan', MaintenancePlan::class)->name('plan');
            Route::get('/category', MaintenanceCategory::class)->name('category');
        });

        // Rotas de Linhas e Áreas - Requer permissão areas.view
        Route::middleware(['permission:areas.view'])->group(function() {
            Route::get('/linearea', App\Livewire\Components\MaintenanceLineArea::class)->name('linearea');
        });

        // Rota de Redirecionamento para Relatórios
        Route::get('/reports', function() {
            return redirect()->route('reports.dashboard');
        })->name('reports');

        // Rotas de Usuários - Requer permissão users.manage
        Route::middleware(['permission:users.manage'])->group(function() {
            Route::get('/users', App\Livewire\UserManagement::class)->name('users');
        });

        // Rotas de Funções e Permissões - Requer permissão roles.manage
        Route::middleware(['permission:roles.manage'])->group(function() {
            Route::get('/roles', App\Livewire\RolePermissions::class)->name('roles');
        });

        // Rotas de Feriados - Requer permissão settings.manage
        Route::middleware(['permission:settings.manage'])->group(function() {
            Route::get('/holidays', App\Livewire\HolidayManagement::class)->name('holidays');
        });

        // Configurações - Redireciona para configurações do sistema
        Route::get('/settings', function() {
            return redirect()->route('settings.system');
        })->name('settings');

        // Manutenção Corretiva - Requer permissão corrective.view
        Route::middleware(['permission:corrective.view'])->group(function() {
            Route::get('/corrective', App\Livewire\Maintenance\CorrectiveMaintenance::class)->name('corrective');
        });

        // API routes
        // Route::post('/api/notification/mark-as-read', [MaintenanceController::class, 'markNotificationAsRead'])->name('api.notification.mark-as-read');

        // Configurações de Manutenção Corretiva - Requer permissão corrective.manage
        Route::middleware(['permission:corrective.manage'])->group(function() {
            // Failure Modes
            Route::get('/failure-modes', App\Livewire\Maintenance\FailureModes::class)->name('failure-modes');
            Route::get('/failure-mode-categories', App\Livewire\Maintenance\FailureModeCategories::class)->name('failure-mode-categories');

            // Failure Causes
            Route::get('/failure-causes', App\Livewire\Maintenance\FailureCauses::class)->name('failure-causes');
            Route::get('/failure-cause-categories', App\Livewire\Maintenance\FailureCauseCategories::class)->name('failure-cause-categories');
        });
    });

    // Equipment Parts Management - Requer permissão equipment.view
    Route::middleware(['permission:equipment.view'])->group(function() {
        Route::get('/equipment/parts', EquipmentParts::class)->name('equipment.parts');
        Route::get('/equipment/parts/{equipmentId}', EquipmentParts::class)->name('equipment.parts.filtered');
    });

    // Rotas de Relatórios - Requer permissão reports.view
    Route::middleware(['permission:reports.view'])->prefix('reports')->name('reports.')->group(function () {
        // Dashboard de Relatórios
        Route::get('/dashboard', App\Livewire\Reports\ReportsDashboard::class)->name('dashboard');

        // Equipment Performance Reports
        Route::get('/equipment-availability', App\Livewire\Reports\EquipmentAvailability::class)->name('equipment.availability');
        Route::get('/equipment-reliability', App\Livewire\Reports\EquipmentReliability::class)->name('equipment.reliability');

        // Maintenance Effectiveness Reports
        Route::get('/maintenance-types', App\Livewire\Reports\MaintenanceTypes::class)->name('maintenance.types');
        Route::get('/maintenance-compliance', App\Livewire\Reports\MaintenanceCompliance::class)->name('maintenance.compliance');

        // Cost & Resource Analysis Reports
        Route::get('/cost-analysis', App\Livewire\Reports\CostAnalysis::class)->name('cost.analysis');
        Route::get('/resource-utilization', App\Livewire\Reports\ResourceUtilization::class)->name('resource.utilization');

        // Failure Analysis Reports
        Route::get('/failure-analysis', App\Livewire\Reports\FailureAnalysis::class)->name('failure.analysis');
        Route::get('/downtime-impact', App\Livewire\Reports\DowntimeImpact::class)->name('downtime.impact');

        // Histórico de Manutenção (legacy route)
        Route::get('/history', App\Livewire\Reports\MaintenanceHistory::class)->name('history');
    });

    // History Tracking Routes - Requer permissão reports.view
    Route::middleware(['permission:reports.view'])->prefix('history')->name('history.')->group(function () {
        // Equipment History Timeline
        Route::get('/equipment-timeline', App\Livewire\History\EquipmentTimeline::class)->name('equipment.timeline');

        // Maintenance Audit Log
        Route::get('/maintenance-audit', App\Livewire\History\MaintenanceAudit::class)->name('maintenance.audit');

        // Part/Supply Lifecycle Tracking
        Route::get('/parts-lifecycle', App\Livewire\History\PartsLifecycle::class)->name('parts.lifecycle');

        // Team Performance History
        Route::get('/team-performance', App\Livewire\History\TeamPerformance::class)->name('team.performance');
    });

    // Rota de Gerenciamento de Usuários
    Route::middleware(['permission:users.manage'])->get('/users', function() {
        return redirect()->route('maintenance.users');
    })->name('users.management');

    // Rota de Permissões de Acesso
    Route::middleware(['permission:roles.manage'])->get('/permissions', function() {
        return redirect()->route('maintenance.roles');
    })->name('permissions.management');

    // Rota de Gerenciamento de Feriados
    Route::middleware(['permission:settings.manage'])->get('/holidays', function() {
        return redirect()->route('maintenance.holidays');
    })->name('holidays.management');

    // Stocks Management Routes
    Route::prefix('stocks')->name('stocks.')->group(function() {
        Route::get('/stockout', App\Livewire\Stocks\StockOut::class)->name('stockout');
    });

    // Rotas de Configurações - Requer permissão settings.manage
    Route::middleware(['permission:settings.manage'])->prefix('settings')->name('settings.')->group(function () {
        // Configurações do Sistema
        Route::get('/system', App\Livewire\Settings\SystemSettings::class)->name('system');
    });
});
