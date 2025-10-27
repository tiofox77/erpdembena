<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Acesso aos Menus de Manutenção ===\n\n";

// Get maintenance user
$maintenanceUser = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if (!$maintenanceUser) {
    echo "❌ Utilizador maintenance@dembena-group.com não encontrado\n";
    exit;
}

echo "👤 Utilizador: {$maintenanceUser->name} ({$maintenanceUser->email})\n";
echo "🏷️  Role: " . $maintenanceUser->roles->pluck('name')->join(', ') . "\n\n";

// Extrair todas as permissões do menu de manutenção do layout
$layoutFile = file_get_contents(__DIR__ . '/resources/views/layouts/livewire.blade.php');

// Encontrar seção de manutenção
preg_match('/<!-- Maintenance Menu -->(.*?)@endcanany/s', $layoutFile, $maintenanceSection);

if (empty($maintenanceSection)) {
    echo "❌ Seção de manutenção não encontrada no layout\n";
    exit;
}

$maintenanceMenuContent = $maintenanceSection[0];

// Extrair todas as permissões da seção de manutenção
$patterns = [
    '/@can\([\'"]([^\'"]+)[\'"]\)/',
    '/@canany\(\[([^\]]+)\]\)/'
];

$maintenancePermissions = [];

foreach ($patterns as $pattern) {
    preg_match_all($pattern, $maintenanceMenuContent, $matches);
    
    if ($pattern === '/@canany\(\[([^\]]+)\]\)/') {
        foreach ($matches[1] as $match) {
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $match, $arrayMatches);
            foreach ($arrayMatches[1] as $perm) {
                $maintenancePermissions[] = trim($perm);
            }
        }
    } else {
        foreach ($matches[1] as $perm) {
            $maintenancePermissions[] = trim($perm);
        }
    }
}

$maintenancePermissions = array_unique($maintenancePermissions);
sort($maintenancePermissions);

echo "🔍 PERMISSÕES NECESSÁRIAS PARA MENUS DE MANUTENÇÃO:\n";
echo str_repeat("-", 50) . "\n";

foreach ($maintenancePermissions as $perm) {
    $has = $maintenanceUser->can($perm) ? "✅" : "❌";
    echo sprintf("%-35s %s\n", $perm, $has);
}

// Verificar acesso ao menu principal
$mainMenuPermissions = [
    'maintenance.dashboard.view', 
    'maintenance.equipment.view', 
    'maintenance.plan.view', 
    'maintenance.corrective.view', 
    'areas.view', 
    'lines.view', 
    'maintenance.technicians.view', 
    'holidays.view'
];

$canSeeMainMenu = $maintenanceUser->canAny($mainMenuPermissions);

echo "\n📋 ACESSO AO MENU PRINCIPAL:\n";
echo str_repeat("-", 30) . "\n";
echo "Pode ver menu Maintenance: " . ($canSeeMainMenu ? "✅ SIM" : "❌ NÃO") . "\n";

// Verificar cada submenu individualmente
$submenus = [
    'Dashboard' => ['maintenance.equipment.view', 'maintenance.plan.view', 'maintenance.corrective.view', 'maintenance.reports'],
    'Plano de Manutenção' => ['maintenance.plan.view'],
    'Equipamentos' => ['maintenance.equipment.view'],
    'Manutenção Corretiva' => ['maintenance.corrective.view'],
    'Técnicos' => ['maintenance.technicians.view'],
    'Áreas' => ['areas.view'],
    'Linhas' => ['lines.view'],
    'Feriados' => ['holidays.view'],
    'Relatórios' => ['maintenance.reports']
];

echo "\n🔍 ACESSO POR SUBMENU:\n";
echo str_repeat("-", 40) . "\n";

foreach ($submenus as $menuName => $permissions) {
    $hasAccess = $maintenanceUser->canAny($permissions);
    $status = $hasAccess ? "✅ SIM" : "❌ NÃO";
    echo sprintf("%-25s %s\n", $menuName, $status);
    
    if (!$hasAccess) {
        echo "   Permissões necessárias:\n";
        foreach ($permissions as $perm) {
            $has = $maintenanceUser->can($perm) ? "✅" : "❌";
            echo "      {$has} {$perm}\n";
        }
    }
}

// Verificar permissões em falta
$missingPermissions = [];
foreach ($maintenancePermissions as $perm) {
    if (!$maintenanceUser->can($perm)) {
        $missingPermissions[] = $perm;
    }
}

if (!empty($missingPermissions)) {
    echo "\n❌ PERMISSÕES EM FALTA:\n";
    echo str_repeat("-", 25) . "\n";
    foreach ($missingPermissions as $perm) {
        echo "   ❌ {$perm}\n";
        
        // Verificar se a permissão existe na BD
        $exists = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
        if (!$exists) {
            echo "      ⚠️  Permissão não existe na base de dados\n";
        }
    }
    
    echo "\n🛠️  CORREÇÃO NECESSÁRIA:\n";
    echo str_repeat("-", 20) . "\n";
    
    $role = $maintenanceUser->roles->first();
    if ($role) {
        echo "Adicionar permissões à role '{$role->name}':\n";
        foreach ($missingPermissions as $perm) {
            $exists = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
            if ($exists) {
                echo "   \$role->givePermissionTo('{$perm}');\n";
            } else {
                echo "   // Criar primeiro: Permission::create(['name' => '{$perm}', 'guard_name' => 'web']);\n";
            }
        }
    }
} else {
    echo "\n✅ Utilizador tem todas as permissões necessárias para os menus de manutenção\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
