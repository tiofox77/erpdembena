<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Access Denied Error ===\n\n";

// Check if user is logged in via session
if (session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')) {
    $userId = session('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
    echo "✅ Utilizador logado - ID: {$userId}\n";
    
    $user = \App\Models\User::find($userId);
    if ($user) {
        echo "👤 Utilizador: {$user->name} ({$user->email})\n";
        echo "🏷️  Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
        echo "📊 Total Permissões: " . $user->getAllPermissions()->count() . "\n\n";
        
        // Check specific pages that might be causing issues
        $pagesToTest = [
            'maintenance.roles' => ['roles.manage', 'maintenance.roles.manage', 'system.roles.view'],
            'maintenance.dashboard' => ['maintenance.dashboard.view'],
            'maintenance.equipment' => ['maintenance.equipment.view'],
            'hr.dashboard' => ['hr.dashboard'],
            'hr.payroll' => ['hr.payroll.view'],
            'hr.shift-management' => ['hr.attendance.view', 'hr.employees.view']
        ];
        
        echo "🔍 TESTE DE ACESSO ÀS PÁGINAS:\n";
        echo str_repeat("-", 50) . "\n";
        
        foreach ($pagesToTest as $page => $permissions) {
            $hasAccess = $user->canAny($permissions);
            $status = $hasAccess ? "✅ ACESSO" : "❌ NEGADO";
            echo sprintf("%-25s %s\n", $page, $status);
            
            if (!$hasAccess) {
                echo "   Permissões necessárias: " . implode(', ', $permissions) . "\n";
                foreach ($permissions as $perm) {
                    $has = $user->can($perm) ? "✅" : "❌";
                    echo "   {$has} {$perm}\n";
                }
            }
            echo "\n";
        }
        
    } else {
        echo "❌ Utilizador não encontrado na base de dados\n";
    }
} else {
    echo "❌ Nenhum utilizador logado\n";
}

// Check middleware configurations
echo str_repeat("=", 60) . "\n";
echo "🔧 VERIFICAÇÃO DE MIDDLEWARE:\n";
echo str_repeat("-", 30) . "\n";

// Read route file to check middleware
$routeFile = file_get_contents(__DIR__ . '/routes/web.php');

// Find problematic middleware patterns
$problematicPatterns = [
    'permission:roles.manage' => 'Middleware muito restritivo',
    'role:super-admin' => 'Apenas super-admin',
    'can:' => 'Verificação can: específica'
];

foreach ($problematicPatterns as $pattern => $description) {
    if (strpos($routeFile, $pattern) !== false) {
        echo "⚠️  Encontrado: {$pattern} - {$description}\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "💡 POSSÍVEIS CAUSAS DO 'ACCESS DENIED':\n";
echo str_repeat("-", 40) . "\n";
echo "1. Middleware muito restritivo nas rotas\n";
echo "2. Verificação @can incorreta nos Livewire components\n";
echo "3. Sessão expirada ou corrompida\n";
echo "4. Permissões não sincronizadas\n";
echo "5. Guard incorreto (web vs api)\n";

echo "\n🛠️  SOLUÇÕES:\n";
echo str_repeat("-", 15) . "\n";
echo "1. Fazer logout/login completo\n";
echo "2. Limpar cache do browser\n";
echo "3. Verificar middleware das rotas específicas\n";
echo "4. Testar em modo incógnito\n";

echo "\n" . str_repeat("=", 60) . "\n";
