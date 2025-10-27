<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CRIAÇÃO COMPLETA DO SISTEMA MODULAR ===\n\n";

try {
    // ETAPA 1: DEFINIR ESTRUTURA COMPLETA DAS PERMISSÕES
    echo "🏗️  ETAPA 1: Definindo estrutura completa de permissões...\n";
    echo str_repeat("-", 80) . "\n";

    $moduleStructure = [
        'maintenance' => [
            'description' => 'Módulo de Manutenção - Gestão completa de equipamentos e manutenção',
            'permissions' => [
                // Dashboard e views principais
                'maintenance.view', 'maintenance.dashboard',
                
                // Equipment management
                'equipment.view', 'equipment.create', 'equipment.edit', 'equipment.delete', 'equipment.manage',
                'maintenance.equipment.view', 'maintenance.equipment.create', 'maintenance.equipment.edit', 
                'maintenance.equipment.delete', 'maintenance.equipment.manage',
                
                // Parts management
                'parts.view', 'parts.create', 'parts.edit', 'parts.delete', 'parts.manage',
                'equipment.parts.view', 'equipment.parts.manage',
                
                // Stock management
                'stock.view', 'stock.manage', 'stocks.stockin', 'stocks.stockout', 
                'stocks.history', 'stocks.part-requests',
                
                // Preventive maintenance
                'preventive.view', 'preventive.create', 'preventive.edit', 'preventive.delete', 'preventive.manage',
                'maintenance.plan.view', 'maintenance.plan.create', 'maintenance.plan.edit', 
                'maintenance.plan.delete', 'maintenance.plan.manage',
                
                // Corrective maintenance
                'corrective.view', 'corrective.create', 'corrective.edit', 'corrective.delete', 
                'corrective.manage', 'maintenance.corrective.view', 'maintenance.corrective.create',
                'maintenance.corrective.edit', 'maintenance.corrective.delete', 'maintenance.corrective.manage',
                
                // Areas and lines
                'areas.view', 'areas.create', 'areas.edit', 'areas.delete', 'areas.manage',
                'lines.view', 'lines.create', 'lines.edit', 'lines.delete', 'lines.manage',
                'maintenance.linearea.view', 'maintenance.linearea.manage',
                
                // Tasks
                'task.view', 'task.create', 'task.edit', 'task.delete', 'task.manage',
                'maintenance.task.view', 'maintenance.task.create', 'maintenance.task.edit',
                'maintenance.task.delete', 'maintenance.task.manage',
                
                // Technicians
                'technicians.view', 'technicians.create', 'technicians.edit', 'technicians.delete', 'technicians.manage',
                'maintenance.technicians.view', 'maintenance.technicians.manage',
                
                // Users and roles
                'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage',
                'maintenance.users.view', 'maintenance.users.manage',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage',
                'maintenance.roles.view', 'maintenance.roles.manage',
                
                // Settings and holidays
                'settings.view', 'settings.edit', 'settings.manage',
                'holidays.view', 'holidays.create', 'holidays.edit', 'holidays.delete', 'holidays.manage',
                'maintenance.holidays.view', 'maintenance.holidays.manage',
                'maintenance.settings',
                
                // Reports and history
                'reports.view', 'reports.generate', 'reports.export', 'reports.dashboard',
                'reports.equipment.availability', 'reports.equipment.reliability',
                'reports.maintenance.types', 'reports.maintenance.compliance', 'reports.maintenance.plan',
                'reports.resource.utilization', 'reports.failure.analysis', 'reports.downtime.impact',
                'history.equipment.timeline', 'history.maintenance.audit', 
                'history.parts.lifecycle', 'history.team.performance',
                
                // Failure management
                'maintenance.failure-modes', 'maintenance.failure-mode-categories',
                'maintenance.failure-causes', 'maintenance.failure-cause-categories'
            ]
        ],
        
        'mrp' => [
            'description' => 'Módulo MRP - Planejamento completo de recursos materiais e produção',
            'permissions' => [
                // Dashboard
                'mrp.dashboard',
                
                // Demand forecasting
                'mrp.demand_forecasting.view', 'mrp.demand_forecasting.create', 
                'mrp.demand_forecasting.edit', 'mrp.demand_forecasting.delete',
                
                // BOM Management
                'mrp.bom_management.view', 'mrp.bom_management.create',
                'mrp.bom_management.edit', 'mrp.bom_management.delete',
                
                // Inventory levels
                'mrp.inventory_levels.view', 'mrp.inventory_levels.manage',
                
                // Production scheduling
                'mrp.production_scheduling.view', 'mrp.production_scheduling.create',
                'mrp.production_scheduling.edit', 'mrp.production_scheduling.delete',
                
                // Production orders
                'mrp.production_orders.view', 'mrp.production_orders.create',
                'mrp.production_orders.edit', 'mrp.production_orders.delete',
                
                // Purchase planning
                'mrp.purchase_planning.view', 'mrp.purchase_planning.create',
                'mrp.purchase_planning.edit', 'mrp.purchase_planning.delete',
                
                // Capacity planning
                'mrp.capacity_planning.view', 'mrp.capacity_planning.manage',
                
                // Financial reporting
                'mrp.financial_reporting.view', 'mrp.financial_reporting.generate',
                
                // Failure analysis
                'mrp.failure_analysis.view', 'mrp.failure_analysis.manage',
                
                // Lines and shifts
                'mrp.lines.view', 'mrp.lines.create', 'mrp.lines.edit', 'mrp.lines.delete',
                'mrp.shifts.view', 'mrp.shifts.create', 'mrp.shifts.edit', 'mrp.shifts.delete',
                
                // Resources
                'mrp.resources.view', 'mrp.resources.manage',
                
                // Responsibles
                'mrp.responsibles.view', 'mrp.responsibles.create', 
                'mrp.responsibles.edit', 'mrp.responsibles.delete',
                
                // Reports
                'mrp.reports.raw_material', 'mrp.reports.another_report'
            ]
        ],
        
        'supply_chain' => [
            'description' => 'Módulo Supply Chain - Gestão completa de cadeia de suprimentos',
            'permissions' => [
                // Dashboard
                'supplychain.dashboard',
                
                // Purchase orders
                'supplychain.purchase_orders.view', 'supplychain.purchase_orders.create',
                'supplychain.purchase_orders.edit', 'supplychain.purchase_orders.delete',
                'supplychain.purchase_orders.export',
                
                // Goods receipts
                'supplychain.goods_receipts.view', 'supplychain.goods_receipts.create',
                'supplychain.goods_receipts.edit', 'supplychain.goods_receipts.delete',
                'supplychain.goods_receipts.export',
                
                // Products
                'supplychain.products.view', 'supplychain.products.create',
                'supplychain.products.edit', 'supplychain.products.delete',
                'supplychain.products.import', 'supplychain.products.export',
                
                // Suppliers
                'supplychain.suppliers.view', 'supplychain.suppliers.create',
                'supplychain.suppliers.edit', 'supplychain.suppliers.delete', 'supplychain.suppliers.manage',
                
                // Inventory
                'supplychain.inventory.view', 'supplychain.inventory.adjust', 'supplychain.inventory.export',
                
                // Warehouse transfers
                'supplychain.warehouse_transfers.view', 'supplychain.warehouse_transfers.create',
                'supplychain.warehouse_transfers.edit', 'supplychain.warehouse_transfers.delete',
                
                // Reports
                'supplychain.reports.view', 'supplychain.reports.generate',
                
                // Forms
                'supplychain.forms.manage'
            ]
        ],
        
        'hr' => [
            'description' => 'Módulo HR - Gestão completa de recursos humanos',
            'permissions' => [
                // Dashboard
                'hr.dashboard',
                
                // Employees
                'hr.employees.view', 'hr.employees.create', 'hr.employees.edit', 
                'hr.employees.delete', 'hr.employees.manage', 'hr.employees.import', 'hr.employees.export',
                
                // Departments
                'hr.departments.view', 'hr.departments.create', 'hr.departments.edit', 
                'hr.departments.delete', 'hr.departments.manage',
                
                // Positions
                'hr.positions.view', 'hr.positions.create', 'hr.positions.edit', 'hr.positions.delete',
                'hr.positions.manage',
                
                // Attendance
                'hr.attendance.view', 'hr.attendance.create', 'hr.attendance.record',
                'hr.attendance.edit', 'hr.attendance.delete', 'hr.attendance.export', 'hr.attendance.manage',
                
                // Leave
                'hr.leave.view', 'hr.leave.create', 'hr.leave.request', 'hr.leave.edit',
                'hr.leave.delete', 'hr.leave.approve', 'hr.leave.export',
                
                // Performance
                'hr.performance.view', 'hr.performance.create', 'hr.performance.edit', 'hr.performance.delete',
                
                // Payroll
                'hr.payroll.view', 'hr.payroll.create', 'hr.payroll.edit', 'hr.payroll.delete',
                'hr.payroll.approve', 'hr.payroll.process',
                
                // Equipment
                'hr.equipment.view', 'hr.equipment.manage',
                
                // Settings
                'hr.settings.view', 'hr.settings.edit',
                
                // Training
                'hr.training.view', 'hr.training.manage',
                
                // Contracts
                'hr.contracts.view', 'hr.contracts.manage',
                
                // Reports
                'hr.reports.view', 'hr.reports.export'
            ]
        ]
    ];

    // ETAPA 2: CRIAR/VERIFICAR TODAS AS PERMISSÕES
    echo "\n📝 ETAPA 2: Criando/verificando permissões...\n";
    echo str_repeat("-", 80) . "\n";

    $totalPermissionsCreated = 0;
    $allModulePermissions = [];

    foreach ($moduleStructure as $module => $data) {
        echo "\n🔧 Módulo: " . strtoupper($module) . "\n";
        $modulePermissions = [];
        
        foreach ($data['permissions'] as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
            
            if ($permission->wasRecentlyCreated) {
                echo "  ➕ Criada: {$permissionName}\n";
                $totalPermissionsCreated++;
            }
            
            $modulePermissions[] = $permission;
            $allModulePermissions[] = $permission;
        }
        
        echo "  ✅ Total: " . count($modulePermissions) . " permissões\n";
    }

    echo "\n✅ Total de permissões criadas: {$totalPermissionsCreated}\n";

    // ETAPA 3: CRIAR ROLES MODULARES
    echo "\n🎭 ETAPA 3: Criando roles modulares...\n";
    echo str_repeat("-", 80) . "\n";

    foreach ($moduleStructure as $module => $data) {
        $roleName = $module . '-manager';
        echo "\n📋 Criando role: {$roleName}\n";
        
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web'
        ]);
        
        // Buscar permissões do módulo
        $modulePermissionNames = $data['permissions'];
        $modulePermissions = Permission::whereIn('name', $modulePermissionNames)->get();
        
        $role->syncPermissions($modulePermissions);
        
        echo "  ✅ Role criada com " . $modulePermissions->count() . " permissões\n";
        echo "  📝 " . $data['description'] . "\n";
    }

    // ETAPA 4: CONFIGURAR SUPER ADMIN
    echo "\n🛡️  ETAPA 4: Configurando Super Admin...\n";
    echo str_repeat("-", 80) . "\n";

    $superAdminRole = Role::where('name', 'super-admin')->first();
    if ($superAdminRole) {
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
        echo "✅ Super Admin configurado com " . $allPermissions->count() . " permissões\n";
    }

    // ETAPA 5: VERIFICAR COBERTURA
    echo "\n📊 ETAPA 5: Verificando cobertura modular...\n";
    echo str_repeat("-", 80) . "\n";

    $allPermissions = Permission::all();
    $coveredPermissions = [];
    
    foreach ($moduleStructure as $module => $data) {
        $coveredPermissions = array_merge($coveredPermissions, $data['permissions']);
    }
    
    $uncoveredPermissions = [];
    foreach ($allPermissions as $permission) {
        if (!in_array($permission->name, $coveredPermissions)) {
            $uncoveredPermissions[] = $permission->name;
        }
    }
    
    $coverage = round((count($coveredPermissions) / $allPermissions->count()) * 100, 2);
    echo "📈 Cobertura modular: {$coverage}%\n";
    echo "📊 Permissões cobertas: " . count($coveredPermissions) . "/" . $allPermissions->count() . "\n";
    
    if (!empty($uncoveredPermissions)) {
        echo "\n⚠️  PERMISSÕES NÃO COBERTAS:\n";
        foreach ($uncoveredPermissions as $perm) {
            echo "   - {$perm}\n";
        }
    } else {
        echo "\n🎉 100% DAS PERMISSÕES ESTÃO COBERTAS!\n";
    }

    // ETAPA 6: LIMPAR CACHE
    echo "\n🧹 ETAPA 6: Limpando cache...\n";
    echo str_repeat("-", 80) . "\n";
    
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✅ Cache de permissões limpo\n";

    // RELATÓRIO FINAL
    echo "\n📊 RELATÓRIO FINAL:\n";
    echo str_repeat("=", 80) . "\n";
    
    $finalRoles = Role::all();
    foreach ($finalRoles as $role) {
        $permCount = $role->permissions->count();
        echo "🎭 {$role->name}: {$permCount} permissões\n";
    }
    
    echo "\n📈 ESTATÍSTICAS:\n";
    echo "• Total de roles: " . $finalRoles->count() . "\n";
    echo "• Total de permissões: " . $allPermissions->count() . "\n";
    echo "• Cobertura modular: {$coverage}%\n";
    echo "• Permissões criadas nesta sessão: {$totalPermissionsCreated}\n";

    echo "\n🎉 SISTEMA MODULAR COMPLETO CRIADO COM SUCESSO!\n";
    echo "\n💡 PRÓXIMOS PASSOS:\n";
    echo "1. Atribuir as roles aos usuários apropriados\n";
    echo "2. Testar acesso de cada módulo\n";
    echo "3. Fazer logout/login para renovar sessões\n";

} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
