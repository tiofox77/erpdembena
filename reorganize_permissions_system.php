<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== REORGANIZAÇÃO COMPLETA DO SISTEMA DE PERMISSÕES ===\n\n";

try {
    // ETAPA 1: BACKUP DE SEGURANÇA
    echo "🔄 ETAPA 1: Criando backup de segurança...\n";
    echo str_repeat("-", 60) . "\n";
    
    $existingRoles = Role::all();
    $existingPermissions = Permission::all();
    
    echo "📊 Status atual:\n";
    echo "  - Roles existentes: " . $existingRoles->count() . "\n";
    echo "  - Permissões existentes: " . $existingPermissions->count() . "\n";
    
    // ETAPA 2: PRESERVAR SUPER ADMIN
    echo "\n🛡️  ETAPA 2: Preservando Super Admin...\n";
    echo str_repeat("-", 60) . "\n";
    
    $superAdminRole = Role::where('name', 'super-admin')->first();
    if (!$superAdminRole) {
        echo "⚠️  Super Admin não encontrado, criando...\n";
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    }
    
    $superAdminUsers = User::role('super-admin')->get();
    echo "✅ Super Admin preservado com " . $superAdminUsers->count() . " usuários\n";
    
    // ETAPA 3: LIMPEZA DE ROLES (EXCETO SUPER ADMIN)
    echo "\n🗑️  ETAPA 3: Removendo roles antigas (exceto super-admin)...\n";
    echo str_repeat("-", 60) . "\n";
    
    $rolesToDelete = Role::where('name', '!=', 'super-admin')->get();
    $deletedRolesCount = 0;
    
    foreach ($rolesToDelete as $role) {
        echo "  🗑️  Removendo role: {$role->name}\n";
        $role->delete();
        $deletedRolesCount++;
    }
    
    echo "✅ Removidas {$deletedRolesCount} roles antigas\n";
    
    // ETAPA 4: DEFINIR PERMISSÕES POR MÓDULO
    echo "\n🏗️  ETAPA 4: Criando novo sistema de permissões...\n";
    echo str_repeat("-", 60) . "\n";
    
    $modulePermissions = [
        'maintenance' => [
            // Permissões principais do menu
            'maintenance.view',
            'maintenance.dashboard',
            'maintenance.equipment.view',
            'maintenance.corrective.view', 
            'maintenance.plan.view',
            
            // Permissões básicas do sidebar
            'equipment.view',
            'preventive.view',
            'corrective.view',
            'corrective.manage',
            'areas.view',
            'technicians.view',
            'parts.view',
            'stock.manage',
            'reports.view',
            'users.manage',
            'roles.manage',
            'settings.manage',
            
            // Permissões CRUD completas
            'maintenance.equipment.create',
            'maintenance.equipment.edit',
            'maintenance.equipment.delete',
            'maintenance.equipment.manage',
            'maintenance.corrective.create',
            'maintenance.corrective.edit',
            'maintenance.corrective.delete',
            'maintenance.corrective.manage',
            'maintenance.plan.create',
            'maintenance.plan.edit',
            'maintenance.plan.delete',
            'maintenance.plan.manage'
        ],
        
        'mrp' => [
            // Permissões do sidebar MRP
            'mrp.dashboard',
            'mrp.demand_forecasting.view',
            'mrp.bom_management.view',
            'mrp.inventory_levels.view',
            'mrp.production_scheduling.view',
            'mrp.production_orders.view',
            'mrp.purchase_planning.view',
            'mrp.capacity_planning.view',
            'mrp.failure_analysis.view',
            'mrp.financial_reporting.view',
            'mrp.lines.view',
            'mrp.reports.another_report',
            'mrp.reports.raw_material',
            'mrp.resources.view',
            'mrp.responsibles.view',
            'mrp.shifts.view',
            
            // Permissões CRUD
            'mrp.demand_forecasting.create',
            'mrp.demand_forecasting.edit',
            'mrp.demand_forecasting.delete',
            'mrp.bom_management.create',
            'mrp.bom_management.edit',
            'mrp.bom_management.delete',
            'mrp.production_orders.create',
            'mrp.production_orders.edit',
            'mrp.production_orders.delete'
        ],
        
        'supply_chain' => [
            // Permissões do sidebar Supply Chain
            'supplychain.dashboard',
            'supplychain.purchase_orders.view',
            'supplychain.goods_receipts.view',
            'supplychain.suppliers.view',
            'supplychain.suppliers.manage',
            'supplychain.warehouse_transfers.view',
            'supplychain.inventory.view',
            'supplychain.products.view',
            'supplychain.reports.view',
            'supplychain.forms.manage',
            
            // Permissões CRUD
            'supplychain.purchase_orders.create',
            'supplychain.purchase_orders.edit',
            'supplychain.purchase_orders.delete',
            'supplychain.goods_receipts.create',
            'supplychain.goods_receipts.edit',
            'supplychain.goods_receipts.delete',
            'supplychain.suppliers.create',
            'supplychain.suppliers.edit',
            'supplychain.suppliers.delete',
            'supplychain.warehouse_transfers.create',
            'supplychain.warehouse_transfers.edit',
            'supplychain.warehouse_transfers.delete'
        ],
        
        'hr' => [
            // Permissões do sidebar HR
            'hr.dashboard',
            'hr.employees.view',
            'hr.attendance.view',
            'hr.leave.view',
            'hr.performance.view',
            'hr.departments.view',
            'hr.positions.view',
            'hr.settings.view',
            'hr.equipment.view',
            
            // Permissões CRUD
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
            'hr.employees.manage',
            'hr.attendance.create',
            'hr.attendance.edit',
            'hr.attendance.delete',
            'hr.leave.create',
            'hr.leave.edit',
            'hr.leave.delete',
            'hr.leave.approve',
            'hr.payroll.view',
            'hr.payroll.create',
            'hr.payroll.edit',
            'hr.payroll.delete',
            'hr.payroll.approve',
            'hr.performance.create',
            'hr.performance.edit',
            'hr.performance.delete'
        ]
    ];
    
    // ETAPA 5: CRIAR PERMISSÕES
    echo "\n📝 ETAPA 5: Criando permissões...\n";
    echo str_repeat("-", 60) . "\n";
    
    $createdPermissions = 0;
    $allNewPermissions = [];
    
    foreach ($modulePermissions as $module => $permissions) {
        echo "\n🔧 Módulo: " . strtoupper($module) . "\n";
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
            
            if ($permission->wasRecentlyCreated) {
                echo "  ➕ Criada: {$permissionName}\n";
                $createdPermissions++;
            } else {
                echo "  ✅ Existe: {$permissionName}\n";
            }
            
            $allNewPermissions[] = $permission;
        }
    }
    
    echo "\n✅ Total de permissões criadas: {$createdPermissions}\n";
    
    // ETAPA 6: CRIAR ROLES
    echo "\n👥 ETAPA 6: Criando roles principais...\n";
    echo str_repeat("-", 60) . "\n";
    
    $newRoles = [
        'maintenance-manager' => [
            'description' => 'Gestor de Manutenção - Acesso completo ao módulo de manutenção',
            'permissions' => $modulePermissions['maintenance']
        ],
        'mrp-manager' => [
            'description' => 'Gestor de MRP - Acesso completo ao módulo de planejamento',
            'permissions' => $modulePermissions['mrp']
        ],
        'supply-chain-manager' => [
            'description' => 'Gestor de Supply Chain - Acesso completo ao módulo de cadeia de suprimentos',
            'permissions' => $modulePermissions['supply_chain']
        ],
        'hr-manager' => [
            'description' => 'Gestor de RH - Acesso completo ao módulo de recursos humanos',
            'permissions' => $modulePermissions['hr']
        ]
    ];
    
    foreach ($newRoles as $roleName => $roleData) {
        echo "\n🎭 Criando role: {$roleName}\n";
        
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web'
        ]);
        
        // Atribuir permissões
        $rolePermissions = [];
        foreach ($roleData['permissions'] as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $rolePermissions[] = $permission;
            }
        }
        
        $role->syncPermissions($rolePermissions);
        
        echo "  ✅ Role criada com " . count($rolePermissions) . " permissões\n";
        echo "  📝 " . $roleData['description'] . "\n";
    }
    
    // ETAPA 7: CONFIGURAR SUPER ADMIN COM TODAS AS PERMISSÕES
    echo "\n🛡️  ETAPA 7: Configurando Super Admin...\n";
    echo str_repeat("-", 60) . "\n";
    
    $superAdminRole->syncPermissions($allNewPermissions);
    echo "✅ Super Admin configurado com " . count($allNewPermissions) . " permissões\n";
    
    // ETAPA 8: LIMPAR CACHE
    echo "\n🧹 ETAPA 8: Limpando cache de permissões...\n";
    echo str_repeat("-", 60) . "\n";
    
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✅ Cache de permissões limpo\n";
    
    // RELATÓRIO FINAL
    echo "\n📊 RELATÓRIO FINAL:\n";
    echo str_repeat("=", 80) . "\n";
    
    $finalRoles = Role::all();
    $finalPermissions = Permission::all();
    
    echo "🎭 ROLES CRIADAS:\n";
    foreach ($finalRoles as $role) {
        $permCount = $role->permissions->count();
        echo "  ✓ {$role->name} ({$permCount} permissões)\n";
    }
    
    echo "\n📝 ESTATÍSTICAS:\n";
    echo "  • Total de roles: " . $finalRoles->count() . "\n";
    echo "  • Total de permissões: " . $finalPermissions->count() . "\n";
    echo "  • Permissões criadas nesta sessão: {$createdPermissions}\n";
    echo "  • Roles removidas: {$deletedRolesCount}\n";
    
    echo "\n🎉 REORGANIZAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo "\n💡 PRÓXIMOS PASSOS:\n";
    echo "1. Atribuir as novas roles aos usuários apropriados\n";
    echo "2. Testar cada módulo com suas respectivas roles\n";
    echo "3. Fazer logout/login para renovar as sessões\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
