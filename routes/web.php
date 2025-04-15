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

// Rota para alterar o idioma
Route::get('/language/{locale}', [\App\Http\Controllers\LanguageController::class, 'changeLocale'])->name('change.locale');

// Rota principal - redireciona para o dashboard se estiver logado ou para login se não estiver
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

// Todas as rotas protegidas pelo middleware auth (usuário deve estar autenticado)
Route::middleware(['auth'])->group(function () {
    // Main dashboard route - redirects based on user role
    Route::get('/dashboard', function() {
        // Check if user has HR permissions
        if (auth()->user()->can('hr.dashboard')) {
            return redirect()->route('hr.dashboard');
        } 
        // Check if user has Supply Chain permissions
        elseif (auth()->user()->can('supplychain.dashboard')) {
            return redirect()->route('supply-chain.dashboard');
        } 
        // Default to maintenance dashboard if has permission
        elseif (auth()->user()->can('equipment.view') || 
                auth()->user()->can('preventive.view') || 
                auth()->user()->can('corrective.view')) {
            return redirect()->route('maintenance.dashboard');
        }
        // If no specific permissions, show a basic dashboard or access denied page
        else {
            return view('dashboard-restricted');
        }
    })->name('dashboard');

    // HR Guide Pages
    Route::get('/hr/payroll-guide', App\Livewire\HR\PayrollGuide::class)->name('hr.payroll-guide');
    
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

        // Gerenciamento de Técnicos - Requer permissão technicians.view
        Route::middleware(['permission:technicians.view'])->group(function() {
            Route::get('/technicians', App\Livewire\Technicians::class)->name('technicians');
        });

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

    // Rotas de Gestão de Estoque - Requer permissão inventory.manage
    Route::middleware(['permission:inventory.manage'])->prefix('stocks')->name('stocks.')->group(function() {
        Route::get('/stock-in', App\Livewire\Stocks\StockIn::class)->name('stockin');
        Route::get('/stock-out', App\Livewire\Stocks\StockOut::class)->name('stockout');
        Route::get('/stock-history', App\Livewire\Stocks\StockHistory::class)->name('history');
        Route::get('/part-requests', App\Livewire\EquipmentPartRequests::class)->name('part-requests');
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
        Route::get('/maintenance-plan-report', App\Livewire\MaintenancePlanReport::class)->name('maintenance.plan');

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

    // Rotas de Configurações - Requer permissão settings.manage
    Route::middleware(['permission:settings.manage'])->prefix('settings')->name('settings.')->group(function () {
        // Configurações do Sistema
        Route::get('/system', App\Livewire\Settings\SystemSettings::class)->name('system');
    });

    // HR Module Routes
    Route::prefix('hr')->name('hr.')->group(function() {
        // Dashboard
        Route::middleware(['permission:hr.dashboard'])->get('/dashboard', App\Livewire\HR\Reports::class)->name('dashboard');
        
        // Employees Management
        Route::middleware(['permission:hr.employees.view'])->get('/employees', App\Livewire\HR\Employees::class)->name('employees');
        
        // Departments Management
        Route::middleware(['permission:hr.departments.view'])->get('/departments', App\Livewire\HR\Departments::class)->name('departments');
        
        // Job Categories Management
        Route::middleware(['permission:hr.positions.view'])->get('/job-categories', App\Livewire\HR\JobCategories::class)->name('job-categories');
        
        // Job Positions Management
        Route::middleware(['permission:hr.positions.view'])->get('/job-positions', App\Livewire\HR\JobPositions::class)->name('job-positions');
        
        // Attendance Management
        Route::middleware(['permission:hr.attendance.view'])->get('/attendance', App\Livewire\HR\Attendance::class)->name('attendance');
        
        // Leave Management
        Route::middleware(['permission:hr.leave.view'])->get('/leave', App\Livewire\HR\Leaves::class)->name('leave');
        
        // Leave Types Management
        Route::middleware(['permission:hr.leave.view'])->get('/leave-types', App\Livewire\HR\LeaveTypes::class)->name('leave-types');
        
        // Shift Management
        Route::middleware(['permission:hr.attendance.view'])->get('/shifts', App\Livewire\HR\ShiftManagement::class)->name('shifts');
        
        // Payroll Management
        Route::middleware(['permission:hr.leave.view'])->get('/payroll', App\Livewire\HR\Payroll::class)->name('payroll');
        
        // Payroll Periods Management
        Route::middleware(['permission:hr.leave.view'])->get('/payroll-periods', App\Livewire\HR\PayrollPeriods::class)->name('payroll-periods');
        
        // Payroll Items Management
        Route::middleware(['permission:hr.leave.view'])->get('/payroll-items', App\Livewire\HR\PayrollItems::class)->name('payroll-items');
        
        // Equipment Management
        Route::middleware(['permission:hr.employees.view'])->get('/equipment', App\Livewire\HR\WorkEquipment::class)->name('equipment');
        
        // Reports
        Route::middleware(['permission:hr.dashboard'])->get('/reports', App\Livewire\HR\Reports::class)->name('reports');
    });
    
    // Supply Chain Module Routes
    Route::prefix('supply-chain')->name('supply-chain.')->group(function() {
        // Dashboard
        Route::middleware(['permission:supplychain.dashboard'])->get('/dashboard', App\Livewire\SupplyChain\Dashboard::class)->name('dashboard');
        
        // Suppliers Management
        Route::middleware(['permission:supplychain.suppliers.view'])->get('/suppliers', App\Livewire\SupplyChain\Suppliers::class)->name('suppliers');
        
        // Product Categories Management
        Route::middleware(['permission:supplychain.products.view'])->get('/product-categories', App\Livewire\SupplyChain\ProductCategories::class)->name('product-categories');
        
        // Products Management
        Route::middleware(['permission:supplychain.products.view'])->get('/products', App\Livewire\SupplyChain\Products::class)->name('products');
        
        // Inventory Locations Management
        Route::middleware(['permission:supplychain.inventory.view'])->get('/inventory-locations', App\Livewire\SupplyChain\InventoryLocations::class)->name('inventory-locations');
        
        // Inventory Management
        Route::middleware(['permission:supplychain.inventory.view'])->get('/inventory', App\Livewire\SupplyChain\Inventory::class)->name('inventory');
        
        // Purchase Orders Management
        Route::middleware(['permission:supplychain.purchase_orders.view'])->get('/purchase-orders', App\Livewire\SupplyChain\PurchaseOrders::class)->name('purchase-orders');
        
        // Goods Receipts Management
        Route::middleware(['permission:supplychain.goods_receipts.view'])->get('/goods-receipts', App\Livewire\SupplyChain\GoodsReceipts::class)->name('goods-receipts');
    });
});
