<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANÁLISE COMPLETA DO SIDEBAR E PERMISSÕES ===\n\n";

// Ler o arquivo do layout
$layoutPath = 'resources/views/layouts/livewire.blade.php';
if (!file_exists($layoutPath)) {
    echo "❌ Arquivo de layout não encontrado\n";
    exit(1);
}

$content = file_get_contents($layoutPath);

// Extrair todas as permissões mencionadas no sidebar
preg_match_all('/@can\(?any?\)?\s*\(\s*\[(.*?)\]\s*\)/s', $content, $matches);
preg_match_all('/@can\s*\(\s*[\'\"](.*?)[\'\"]\s*\)/s', $content, $singleMatches);

$allPermissions = [];
$modulePermissions = [
    'maintenance' => [],
    'mrp' => [],
    'supply_chain' => [],
    'hr' => [],
    'general' => []
];

// Processar @canany
foreach ($matches[1] as $match) {
    preg_match_all('/[\'\"](.*?)[\'\"]/', $match, $permMatches);
    foreach ($permMatches[1] as $perm) {
        $perm = trim($perm);
        if (!empty($perm)) {
            $allPermissions[] = $perm;
        }
    }
}

// Processar @can individuais
foreach ($singleMatches[1] as $perm) {
    $perm = trim($perm);
    if (!empty($perm)) {
        $allPermissions[] = $perm;
    }
}

// Remover duplicatas
$allPermissions = array_unique($allPermissions);
sort($allPermissions);

echo "🔍 TODAS AS PERMISSÕES ENCONTRADAS NO SIDEBAR:\n";
echo str_repeat("=", 80) . "\n";
foreach ($allPermissions as $perm) {
    echo "  - {$perm}\n";
}

echo "\n📊 TOTAL DE PERMISSÕES ÚNICAS: " . count($allPermissions) . "\n\n";

// Categorizar por módulo
foreach ($allPermissions as $perm) {
    if (strpos($perm, 'maintenance.') === 0 || 
        in_array($perm, ['equipment.view', 'preventive.view', 'corrective.view', 'areas.view', 'technicians.view'])) {
        $modulePermissions['maintenance'][] = $perm;
    } elseif (strpos($perm, 'mrp.') === 0) {
        $modulePermissions['mrp'][] = $perm;
    } elseif (strpos($perm, 'supplychain.') === 0 || 
              strpos($perm, 'purchase') !== false ||
              strpos($perm, 'supplier') !== false ||
              strpos($perm, 'goods_receipt') !== false) {
        $modulePermissions['supply_chain'][] = $perm;
    } elseif (strpos($perm, 'hr.') === 0 || 
              strpos($perm, 'payroll') !== false ||
              strpos($perm, 'employee') !== false ||
              strpos($perm, 'attendance') !== false) {
        $modulePermissions['hr'][] = $perm;
    } else {
        $modulePermissions['general'][] = $perm;
    }
}

echo "📋 PERMISSÕES POR MÓDULO:\n";
echo str_repeat("=", 80) . "\n";

foreach ($modulePermissions as $module => $perms) {
    if (!empty($perms)) {
        echo "\n🔧 " . strtoupper(str_replace('_', ' ', $module)) . " (" . count($perms) . " permissões):\n";
        echo str_repeat("-", 50) . "\n";
        foreach ($perms as $perm) {
            echo "  ✓ {$perm}\n";
        }
    }
}

// Analisar estrutura do menu
echo "\n\n📱 ESTRUTURA DO MENU SIDEBAR:\n";
echo str_repeat("=", 80) . "\n";

$menuStructure = [
    'MAINTENANCE' => [
        'main_permissions' => ['maintenance.view', 'equipment.view', 'preventive.view', 'corrective.view'],
        'submenus' => [
            'Dashboard' => ['equipment.view', 'preventive.view', 'corrective.view', 'reports.view'],
            'Maintenance Plan' => ['preventive.view'],
            'Equipment Management' => ['equipment.view'],
            'Equipment Parts' => ['equipment.view'],
            'Line and Area' => ['areas.view'],
            'Task Management' => ['preventive.view'],
            'Corrective Maintenance' => ['corrective.view'],
            'User Management' => ['users.manage'],
            'Roles & Permissions' => ['roles.manage'],
            'Settings/Holidays' => ['settings.manage'],
            'Reports & History' => ['reports.view']
        ]
    ],
    'MRP' => [
        'main_permissions' => ['mrp.dashboard', 'mrp.demand_forecasting.view', 'mrp.bom_management.view'],
        'submenus' => [
            'Dashboard' => ['mrp.dashboard'],
            'Demand Forecasting' => ['mrp.demand_forecasting.view'],
            'BOM Management' => ['mrp.bom_management.view'],
            'Inventory Levels' => ['mrp.inventory_levels.view'],
            'Production Scheduling' => ['mrp.production_scheduling.view'],
            'Production Orders' => ['mrp.production_orders.view'],
            'Purchase Planning' => ['mrp.purchase_planning.view']
        ]
    ],
    'SUPPLY CHAIN' => [
        'main_permissions' => ['supplychain.dashboard', 'supplychain.purchase_orders.view'],
        'submenus' => [
            'Dashboard' => ['supplychain.dashboard'],
            'Purchase Orders' => ['supplychain.purchase_orders.view'],
            'Goods Receipts' => ['supplychain.goods_receipts.view'],
            'Suppliers' => ['supplychain.suppliers.view'],
            'Warehouse Transfers' => ['supplychain.warehouse_transfers.view']
        ]
    ],
    'HR' => [
        'main_permissions' => ['hr.dashboard', 'hr.employees.view', 'hr.attendance.view'],
        'submenus' => [
            'Dashboard' => ['hr.dashboard'],
            'Employee Management' => ['hr.employees.view'],
            'Attendance' => ['hr.attendance.view'],
            'Payroll' => ['hr.payroll.view'],
            'Leave Management' => ['hr.leave.view'],
            'Performance' => ['hr.performance.view']
        ]
    ]
];

foreach ($menuStructure as $module => $data) {
    echo "\n🔧 {$module}:\n";
    echo "  📌 Permissões principais: " . implode(', ', $data['main_permissions']) . "\n";
    echo "  📋 Submenus:\n";
    foreach ($data['submenus'] as $submenu => $perms) {
        echo "    ├─ {$submenu}: " . implode(', ', $perms) . "\n";
    }
}

echo "\n\n💡 RECOMENDAÇÕES PARA REORGANIZAÇÃO:\n";
echo str_repeat("=", 80) . "\n";
echo "1. 🗑️  LIMPAR: Remover todas as roles existentes (exceto super-admin)\n";
echo "2. 🧹 LIMPAR: Remover todas as permissões não utilizadas\n";
echo "3. 🏗️  CRIAR: 4 roles principais:\n";
echo "   - maintenance-manager (acesso completo ao módulo Maintenance)\n";
echo "   - mrp-manager (acesso completo ao módulo MRP)\n";
echo "   - supply-chain-manager (acesso completo ao módulo Supply Chain)\n";
echo "   - hr-manager (acesso completo ao módulo HR)\n";
echo "4. ✅ VERIFICAR: Cada menu tem suas permissões corretamente definidas\n";

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "1. Executar limpeza das roles/permissões existentes\n";
echo "2. Criar as 4 novas roles com permissões específicas\n";
echo "3. Testar cada módulo com suas respectivas roles\n";

echo "\n✅ ANÁLISE CONCLUÍDA!\n";
