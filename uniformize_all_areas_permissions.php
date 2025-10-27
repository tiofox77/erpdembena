<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== UNIFORMIZAR: Todas as Áreas com Padrão Super-Admin ===\n\n";

// Get super-admin role as reference
$superAdminRole = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();

if (!$superAdminRole) {
    echo "❌ Role super-admin não encontrada!\n";
    exit;
}

// Areas to check and update routes/middleware
$areasToCheck = [
    'hr' => [
        'routes' => [
            'hr.dashboard',
            'hr.employees',
            'hr.attendance', 
            'hr.payroll',
            'hr.departments',
            'hr.positions',
            'hr.leave',
            'hr.performance',
            'hr.reports'
        ],
        'permissions' => ['hr.dashboard', 'hr.employees.view', 'hr.attendance.view', 'hr.payroll.view']
    ],
    'supplychain' => [
        'routes' => [
            'supplychain.dashboard',
            'supplychain.purchase_orders',
            'supplychain.goods_receipts',
            'supplychain.products',
            'supplychain.suppliers',
            'supplychain.inventory',
            'supplychain.warehouse_transfers'
        ],
        'permissions' => ['supplychain.dashboard', 'supplychain.purchase_orders.view', 'supplychain.products.view', 'supplychain.suppliers.view']
    ],
    'mrp' => [
        'routes' => [
            'mrp.dashboard',
            'mrp.demand_forecasting',
            'mrp.bom_management',
            'mrp.production_orders',
            'mrp.capacity_planning'
        ],
        'permissions' => ['mrp.dashboard', 'mrp.demand_forecasting.view', 'mrp.bom_management.view', 'mrp.production_orders.view']
    ],
    'system' => [
        'routes' => [
            'system.users',
            'system.roles',
            'system.permissions',
            'system.settings',
            'system.backup'
        ],
        'permissions' => ['system.users.view', 'system.roles.view', 'system.permissions.view', 'system.settings.view']
    ]
];

echo "🔍 ANALISANDO PADRÃO SUPER-ADMIN:\n";
echo str_repeat("-", 50) . "\n";

$superAdminPermissions = $superAdminRole->permissions->pluck('name')->toArray();
echo "✅ Super-admin tem " . count($superAdminPermissions) . " permissões\n";

// Check what permissions each area should have based on super-admin
foreach ($areasToCheck as $area => $config) {
    echo "\n📋 ÁREA: " . strtoupper($area) . "\n";
    echo str_repeat("-", 30) . "\n";
    
    // Get all permissions for this area from super-admin
    $areaPermissions = array_filter($superAdminPermissions, function($perm) use ($area) {
        return strpos($perm, $area . '.') === 0;
    });
    
    echo "🔑 Permissões disponíveis para {$area}: " . count($areaPermissions) . "\n";
    foreach ($areaPermissions as $perm) {
        echo "   ✅ {$perm}\n";
    }
    
    // Suggest middleware pattern
    $suggestedMiddleware = implode('|', array_slice($areaPermissions, 0, 4));
    echo "\n💡 Middleware sugerido para {$area}:\n";
    echo "   Route::middleware(['can:{$suggestedMiddleware}'])\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📝 RECOMENDAÇÕES PARA UNIFORMIZAÇÃO:\n";
echo str_repeat("-", 50) . "\n";

echo "1. 🔧 MAINTENANCE: ✅ Já corrigido\n";
echo "   - Role maintenance-manager agora tem 89 permissões\n";
echo "   - Página /maintenance/roles aceita múltiplas permissões\n\n";

echo "2. 👥 HR: Precisa de uniformização\n";
echo "   - Criar role hr-manager com todas as permissões hr.*\n";
echo "   - Atualizar rotas para aceitar múltiplas permissões\n\n";

echo "3. 📦 SUPPLY CHAIN: Precisa de uniformização\n";
echo "   - Criar role supplychain-manager com todas as permissões supplychain.*\n";
echo "   - Atualizar rotas para aceitar múltiplas permissões\n\n";

echo "4. 🏭 MRP: Precisa de uniformização\n";
echo "   - Criar role mrp-manager com todas as permissões mrp.*\n";
echo "   - Atualizar rotas para aceitar múltiplas permissões\n\n";

echo "5. ⚙️ SYSTEM: Precisa de uniformização\n";
echo "   - Criar role system-admin com todas as permissões system.*\n";
echo "   - Atualizar rotas para aceitar múltiplas permissões\n\n";

echo "✅ PRÓXIMOS PASSOS:\n";
echo "1. Criar roles específicas para cada área\n";
echo "2. Atualizar middleware das rotas\n";
echo "3. Atualizar verificações @can nos layouts\n";
echo "4. Testar acesso com cada role\n";

echo "\n" . str_repeat("=", 60) . "\n";
