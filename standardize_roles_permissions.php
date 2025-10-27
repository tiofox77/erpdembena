<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PADRONIZAR: Permissões das Roles ===\n\n";

// Extrair permissões do layout (as que realmente são usadas)
$layoutFile = file_get_contents(__DIR__ . '/resources/views/layouts/livewire.blade.php');

// Regex patterns para encontrar permissões
$patterns = [
    '/@can\([\'"]([^\'"]+)[\'"]\)/',
    '/@canany\(\[([^\]]+)\]\)/',
    '/@cannot\([\'"]([^\'"]+)[\'"]\)/'
];

$layoutPermissions = [];

foreach ($patterns as $pattern) {
    preg_match_all($pattern, $layoutFile, $matches);
    
    if ($pattern === '/@canany\(\[([^\]]+)\]\)/') {
        // Handle @canany arrays
        foreach ($matches[1] as $match) {
            // Extract individual permissions from array
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $match, $arrayMatches);
            foreach ($arrayMatches[1] as $perm) {
                $layoutPermissions[] = trim($perm);
            }
        }
    } else {
        // Handle @can and @cannot
        foreach ($matches[1] as $perm) {
            $layoutPermissions[] = trim($perm);
        }
    }
}

$layoutPermissions = array_unique($layoutPermissions);
sort($layoutPermissions);

echo "🔍 PERMISSÕES USADAS NO LAYOUT: " . count($layoutPermissions) . "\n";
foreach ($layoutPermissions as $perm) {
    echo "   ✅ {$perm}\n";
}

// Agrupar permissões por módulo
$permissionsByModule = [];
foreach ($layoutPermissions as $perm) {
    $parts = explode('.', $perm);
    $module = $parts[0] ?? 'other';
    $permissionsByModule[$module][] = $perm;
}

echo "\n📊 PERMISSÕES POR MÓDULO:\n";
echo str_repeat("-", 30) . "\n";
foreach ($permissionsByModule as $module => $perms) {
    echo sprintf("%-15s %d permissões\n", $module . ":", count($perms));
}

// Definir quais permissões cada role deve ter baseado no layout
$rolePermissionMap = [
    'maintenance-manager' => [
        'areas.view',
        'holidays.view', 
        'lines.view',
        'maintenance.dashboard.view',
        'maintenance.equipment.view',
        'maintenance.plan.view',
        'maintenance.corrective.view',
        'maintenance.technicians.view',
        'maintenance.reports',
        'reports.view'
    ],
    'hr-manager' => [
        'hr.dashboard',
        'hr.employees.view',
        'hr.attendance.view',
        'hr.payroll.view',
        'hr.leave.view',
        'hr.departments.view',
        'hr.positions.view',
        'hr.contracts.view',
        'hr.training.view'
    ],
    'mrp-manager' => [
        'mrp.dashboard',
        'mrp.demand_forecasting.view',
        'mrp.bom_management.view',
        'mrp.inventory_levels.view',
        'mrp.production_scheduling.view',
        'mrp.production_orders.view',
        'mrp.purchase_planning.view',
        'mrp.capacity_planning.view',
        'mrp.financial_reporting.view',
        'mrp.shifts.view',
        'mrp.lines.view',
        'mrp.failure_analysis.view',
        'mrp.responsibles.view',
        'mrp.reports.raw_material'
    ],
    'supplychain-manager' => [
        'supplychain.dashboard',
        'supplychain.suppliers.view',
        'supplychain.products.view',
        'supplychain.purchase_orders.view',
        'supplychain.goods_receipts.view',
        'supplychain.inventory.view',
        'supplychain.warehouse_transfers.view',
        'supplychain.reports.view',
        'supplychain.analytics.view',
        'supplychain.settings.view'
    ],
    'system-admin' => [
        'users.view',
        'roles.view',
        'settings.view'
    ]
];

echo "\n🛠️  PADRONIZANDO ROLES:\n";
echo str_repeat("=", 40) . "\n";

foreach ($rolePermissionMap as $roleName => $requiredPermissions) {
    echo "\n🏷️  ROLE: {$roleName}\n";
    echo str_repeat("-", 25) . "\n";
    
    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
    
    if (!$role) {
        echo "❌ Role não encontrada\n";
        continue;
    }
    
    $currentPermissions = $role->permissions->pluck('name')->toArray();
    
    // Verificar quais permissões do layout esta role deveria ter
    $shouldHave = array_intersect($requiredPermissions, $layoutPermissions);
    $missing = array_diff($shouldHave, $currentPermissions);
    $extra = array_diff($currentPermissions, $shouldHave);
    
    echo "   📋 Deveria ter: " . count($shouldHave) . " permissões\n";
    echo "   ✅ Tem: " . count($currentPermissions) . " permissões\n";
    echo "   ❌ Em falta: " . count($missing) . " permissões\n";
    
    if (!empty($missing)) {
        echo "\n   🔧 ADICIONANDO PERMISSÕES:\n";
        foreach ($missing as $perm) {
            // Verificar se a permissão existe
            $permissionExists = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
            if ($permissionExists) {
                try {
                    $role->givePermissionTo($perm);
                    echo "      ✅ {$perm}\n";
                } catch (Exception $e) {
                    echo "      ❌ {$perm} - Erro: " . $e->getMessage() . "\n";
                }
            } else {
                echo "      ⚠️  {$perm} - Permissão não existe na BD\n";
            }
        }
    }
    
    // Manter permissões extras que não estão no layout mas podem ser úteis
    if (!empty($extra)) {
        echo "\n   📝 PERMISSÕES EXTRAS (mantidas):\n";
        foreach (array_slice($extra, 0, 5) as $perm) {
            echo "      📄 {$perm}\n";
        }
        if (count($extra) > 5) {
            echo "      ... e mais " . (count($extra) - 5) . " permissões\n";
        }
    }
}

// Garantir que super-admin tem todas as permissões do layout
echo "\n🔧 VERIFICANDO SUPER-ADMIN:\n";
echo str_repeat("-", 25) . "\n";

$superAdmin = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
if ($superAdmin) {
    $superAdminPerms = $superAdmin->permissions->pluck('name')->toArray();
    $missingSuperAdmin = array_diff($layoutPermissions, $superAdminPerms);
    
    if (!empty($missingSuperAdmin)) {
        echo "🔧 Adicionando permissões em falta ao super-admin:\n";
        foreach ($missingSuperAdmin as $perm) {
            $permissionExists = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
            if ($permissionExists) {
                try {
                    $superAdmin->givePermissionTo($perm);
                    echo "   ✅ {$perm}\n";
                } catch (Exception $e) {
                    echo "   ❌ {$perm} - Erro: " . $e->getMessage() . "\n";
                }
            }
        }
    } else {
        echo "✅ Super-admin já tem todas as permissões do layout\n";
    }
}

// Limpar cache de permissões
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "\n✅ Cache de permissões limpo\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESULTADO FINAL:\n";
echo str_repeat("-", 20) . "\n";

// Verificar cobertura final
$roles = \Spatie\Permission\Models\Role::with('permissions')->get();
foreach ($roles as $role) {
    $rolePermissions = $role->permissions->pluck('name')->toArray();
    $hasLayoutPerms = array_intersect($rolePermissions, $layoutPermissions);
    $coverage = count($layoutPermissions) > 0 ? (count($hasLayoutPerms) / count($layoutPermissions)) * 100 : 0;
    
    echo sprintf("%-20s %d/%d (%.1f%%)\n", 
        $role->name . ":", 
        count($hasLayoutPerms), 
        count($layoutPermissions), 
        $coverage
    );
}

echo "\n✅ PADRONIZAÇÃO CONCLUÍDA\n";
echo str_repeat("=", 50) . "\n";
