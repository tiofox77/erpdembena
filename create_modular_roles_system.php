<?php

declare(strict_types=1);

/**
 * Script para Criar Roles Modulares do Sistema ERP
 * Cria roles específicas para cada módulo: Maintenance, MRP, Supply Chain, HR e System
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 CRIAÇÃO DE ROLES MODULARES DO SISTEMA ERP\n";
echo "==============================================\n\n";

try {
    // Definição das roles modulares com suas permissões
    $modularRoles = [
        'maintenance-manager' => [
            'description' => 'Gestor do módulo de Manutenção - acesso completo às funcionalidades de manutenção',
            'permissions' => [
                // Dashboard e visão geral
                'maintenance.view', 'maintenance.dashboard',
                
                // Equipment Management
                'equipment.view', 'equipment.create', 'equipment.edit', 'equipment.delete', 'equipment.manage',
                
                // Preventive Maintenance
                'preventive.view', 'preventive.create', 'preventive.edit', 'preventive.delete', 'preventive.manage',
                
                // Corrective Maintenance  
                'corrective.view', 'corrective.create', 'corrective.edit', 'corrective.delete', 'corrective.manage',
                
                // Parts & Stock Management
                'parts.view', 'parts.create', 'parts.edit', 'parts.delete', 'parts.manage',
                'stocks.view', 'stocks.stockin', 'stocks.stockout', 'stocks.manage',
                
                // Areas & Lines
                'areas.view', 'areas.create', 'areas.edit', 'areas.delete', 'areas.manage',
                'lines.view', 'lines.create', 'lines.edit', 'lines.delete', 'lines.manage',
                
                // Tasks & Technicians
                'task.view', 'task.create', 'task.edit', 'task.delete', 'task.manage',
                'technicians.view', 'technicians.create', 'technicians.edit', 'technicians.delete', 'technicians.manage',
                
                // Holidays & Settings
                'holidays.view', 'holidays.create', 'holidays.edit', 'holidays.delete', 'holidays.manage',
                
                // Reports & History
                'reports.equipment.view', 'reports.maintenance.view', 'reports.cost.view', 'reports.downtime.view',
                'reports.failure.view', 'reports.resource.view',
                'history.equipment.view', 'history.maintenance.view', 'history.parts.view'
            ]
        ],
        
        'mrp-manager' => [
            'description' => 'Gestor do módulo MRP - acesso completo às funcionalidades de produção',
            'permissions' => [
                // Dashboard MRP
                'mrp.dashboard', 'mrp.view',
                
                // BOM Management
                'mrp.bom_management.view', 'mrp.bom_management.create', 'mrp.bom_management.edit', 'mrp.bom_management.delete',
                
                // Production Management
                'mrp.production_scheduling.view', 'mrp.production_scheduling.create', 'mrp.production_scheduling.edit',
                'mrp.production_orders.view', 'mrp.production_orders.create', 'mrp.production_orders.edit', 'mrp.production_orders.delete',
                
                // Inventory & Levels
                'mrp.inventory_levels.view', 'mrp.inventory_levels.manage',
                
                // Capacity Planning
                'mrp.capacity_planning.view', 'mrp.capacity_planning.manage',
                
                // Shifts & Lines
                'mrp.shifts.view', 'mrp.shifts.create', 'mrp.shifts.edit', 'mrp.shifts.delete',
                'mrp.lines.view', 'mrp.lines.create', 'mrp.lines.edit', 'mrp.lines.delete',
                
                // Financial Reporting
                'mrp.financial_reporting.view',
                
                // Failure Analysis
                'mrp.failure_analysis.view', 'mrp.failure_analysis.manage',
                
                // Responsibles
                'mrp.responsibles.view', 'mrp.responsibles.create', 'mrp.responsibles.edit', 'mrp.responsibles.delete',
                
                // Reports
                'mrp.reports.raw_material', 'mrp.reports.production', 'mrp.reports.capacity'
            ]
        ],
        
        'supplychain-manager' => [
            'description' => 'Gestor do módulo Supply Chain - acesso completo às funcionalidades da cadeia de abastecimento',
            'permissions' => [
                // Dashboard Supply Chain
                'supplychain.dashboard', 'supplychain.view',
                
                // Suppliers Management
                'supplychain.suppliers.view', 'supplychain.suppliers.create', 'supplychain.suppliers.edit', 'supplychain.suppliers.delete', 'supplychain.suppliers.manage',
                
                // Products Management
                'supplychain.products.view', 'supplychain.products.create', 'supplychain.products.edit', 'supplychain.products.delete', 'supplychain.products.manage',
                
                // Inventory Management
                'supplychain.inventory.view', 'supplychain.inventory.create', 'supplychain.inventory.edit', 'supplychain.inventory.delete', 'supplychain.inventory.manage',
                'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.manage',
                
                // Purchase Orders
                'supplychain.purchase_orders.view', 'supplychain.purchase_orders.create', 'supplychain.purchase_orders.edit', 'supplychain.purchase_orders.delete',
                'purchase.view', 'purchase.create', 'purchase.edit', 'purchase.delete',
                
                // Goods Receipts
                'supplychain.goods_receipts.view', 'supplychain.goods_receipts.create', 'supplychain.goods_receipts.edit', 'supplychain.goods_receipts.delete',
                'goods.view', 'goods.create', 'goods.edit', 'goods.delete',
                
                // Warehouse Management
                'supplychain.warehouse_transfers.view', 'supplychain.warehouse_transfers.create', 'supplychain.warehouse_transfers.edit', 'supplychain.warehouse_transfers.delete',
                'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                
                // Custom Forms
                'supplychain.forms.manage', 'supplychain.forms.create', 'supplychain.forms.edit', 'supplychain.forms.delete',
                
                // Reports
                'supplychain.reports.view', 'supplychain.reports.inventory', 'supplychain.reports.stock_movement', 'supplychain.reports.raw_material'
            ]
        ],
        
        'hr-manager' => [
            'description' => 'Gestor do módulo HR - acesso completo às funcionalidades de recursos humanos',
            'permissions' => [
                // Dashboard HR
                'hr.dashboard', 'hr.view',
                
                // Employee Management
                'hr.employees.view', 'hr.employees.create', 'hr.employees.edit', 'hr.employees.delete', 'hr.employees.manage',
                'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
                
                // Departments & Positions
                'hr.departments.view', 'hr.departments.create', 'hr.departments.edit', 'hr.departments.delete',
                'hr.positions.view', 'hr.positions.create', 'hr.positions.edit', 'hr.positions.delete',
                'department.view', 'department.create', 'department.edit', 'department.delete',
                'position.view', 'position.create', 'position.edit', 'position.delete',
                
                // Attendance Management
                'hr.attendance.view', 'hr.attendance.create', 'hr.attendance.edit', 'hr.attendance.delete', 'hr.attendance.manage',
                'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
                
                // Leave Management
                'hr.leave.view', 'hr.leave.create', 'hr.leave.edit', 'hr.leave.delete', 'hr.leave.manage',
                'leave.view', 'leave.create', 'leave.edit', 'leave.delete',
                
                // Payroll Management
                'hr.payroll.view', 'hr.payroll.create', 'hr.payroll.edit', 'hr.payroll.delete', 'hr.payroll.manage',
                'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.delete',
                
                // Performance Management
                'hr.performance.view', 'hr.performance.create', 'hr.performance.edit', 'hr.performance.delete', 'hr.performance.manage',
                'performance.view', 'performance.create', 'performance.edit', 'performance.delete',
                
                // Training & Development
                'hr.training.view', 'hr.training.create', 'hr.training.edit', 'hr.training.delete',
                'training.view', 'training.create', 'training.edit', 'training.delete',
                
                // Contracts Management
                'hr.contracts.view', 'hr.contracts.create', 'hr.contracts.edit', 'hr.contracts.delete',
                'contracts.view', 'contracts.create', 'contracts.edit', 'contracts.delete',
                
                // Equipment Management (HR)
                'hr.equipment.view', 'hr.equipment.create', 'hr.equipment.edit', 'hr.equipment.delete',
                
                // Settings HR
                'hr.settings.view', 'hr.settings.edit', 'hr.settings.manage',
                
                // Reports HR
                'hr.reports.view', 'hr.reports.payroll', 'hr.reports.attendance', 'hr.reports.performance'
            ]
        ],
        
        'system-admin' => [
            'description' => 'Administrador do Sistema - acesso completo às funcionalidades administrativas',
            'permissions' => [
                // System Management
                'system.view', 'system.manage', 'admin.view', 'admin.manage',
                
                // User Management
                'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage',
                
                // Roles & Permissions
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage',
                'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete', 'permissions.manage',
                
                // System Settings
                'settings.view', 'settings.create', 'settings.edit', 'settings.delete', 'settings.manage',
                'config.view', 'config.edit', 'config.manage',
                
                // History & Audit
                'history.team.view', 'history.team.manage'
            ]
        ]
    ];

    echo "📋 Roles a serem criadas:\n";
    foreach ($modularRoles as $roleName => $roleData) {
        echo "  • {$roleName} - " . count($roleData['permissions']) . " permissões\n";
    }
    echo "\n";

    $createdRoles = 0;
    $assignedPermissions = 0;
    $skippedRoles = 0;

    foreach ($modularRoles as $roleName => $roleData) {
        echo "🔧 Processando role: {$roleName}\n";
        
        // Verificar se role já existe
        $existingRole = Role::where('name', $roleName)->first();
        
        if ($existingRole) {
            echo "   ⚠️  Role '{$roleName}' já existe. Atualizando permissões...\n";
            $role = $existingRole;
            $skippedRoles++;
        } else {
            // Criar nova role
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            echo "   ✅ Role '{$roleName}' criada com sucesso!\n";
            $createdRoles++;
        }

        // Buscar permissões existentes
        $validPermissions = [];
        $missingPermissions = [];

        foreach ($roleData['permissions'] as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $validPermissions[] = $permission;
            } else {
                $missingPermissions[] = $permissionName;
            }
        }

        // Atribuir permissões válidas
        if (!empty($validPermissions)) {
            $role->syncPermissions($validPermissions);
            $assignedPermissions += count($validPermissions);
            echo "   🔑 " . count($validPermissions) . " permissões atribuídas\n";
        }

        // Reportar permissões em falta
        if (!empty($missingPermissions)) {
            echo "   ⚠️  " . count($missingPermissions) . " permissões não encontradas:\n";
            foreach ($missingPermissions as $missing) {
                echo "       - {$missing}\n";
            }
        }

        echo "\n";
    }

    echo "📊 RESUMO DA EXECUÇÃO\n";
    echo "====================\n";
    echo "✅ Roles criadas: {$createdRoles}\n";
    echo "🔄 Roles atualizadas: {$skippedRoles}\n";
    echo "🔑 Total de permissões atribuídas: {$assignedPermissions}\n";
    echo "📋 Total de roles modulares: " . count($modularRoles) . "\n\n";

    // Verificar roles ativas
    $activeRoles = Role::all();
    echo "🎯 ROLES ATIVAS NO SISTEMA (" . $activeRoles->count() . "):\n";
    foreach ($activeRoles as $role) {
        $permissionCount = $role->permissions->count();
        echo "  • {$role->name} - {$permissionCount} permissões\n";
    }

    echo "\n✨ Script executado com sucesso! Roles modulares criadas/atualizadas.\n";
    echo "💡 Use o Gerenciador de Permissões em /admin/permissions-manager para gerir as roles.\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    if ($e->getPrevious()) {
        echo "🔍 Erro anterior: " . $e->getPrevious()->getMessage() . "\n";
    }
    
    exit(1);
}
