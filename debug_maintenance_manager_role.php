<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: MAINTENANCE-MANAGER ROLE PERMISSIONS ===\n\n";

try {
    // Buscar a role maintenance-manager
    $role = \Spatie\Permission\Models\Role::where('name', 'maintenance-manager')->first();
    
    if (!$role) {
        echo "❌ Role 'maintenance-manager' não encontrada!\n";
        
        // Listar todas as roles disponíveis
        echo "\n📋 Roles disponíveis no sistema:\n";
        $allRoles = \Spatie\Permission\Models\Role::all();
        foreach ($allRoles as $r) {
            echo "  - {$r->name} (Guard: {$r->guard_name})\n";
        }
        exit;
    }
    
    echo "✅ Role encontrada: {$role->name}\n";
    echo "   Guard: {$role->guard_name}\n";
    echo "   Criada em: {$role->created_at}\n\n";
    
    // Buscar todas as permissões da role
    $permissions = $role->permissions;
    
    echo "🔑 PERMISSÕES DIRETAS DA ROLE ({$permissions->count()} total):\n";
    echo str_repeat("-", 60) . "\n";
    
    if ($permissions->isEmpty()) {
        echo "   ❌ Nenhuma permissão direta encontrada\n\n";
    } else {
        foreach ($permissions as $permission) {
            echo "   ✓ {$permission->name}\n";
        }
        echo "\n";
    }
    
    // Agrupar permissões por área/módulo
    echo "📂 PERMISSÕES AGRUPADAS POR ÁREA:\n";
    echo str_repeat("-", 60) . "\n";
    
    $groupedPermissions = [];
    foreach ($permissions as $permission) {
        $parts = explode('.', $permission->name);
        $area = $parts[0] ?? 'other';
        
        if (!isset($groupedPermissions[$area])) {
            $groupedPermissions[$area] = [];
        }
        $groupedPermissions[$area][] = $permission->name;
    }
    
    foreach ($groupedPermissions as $area => $perms) {
        echo "\n🏢 {$area}:\n";
        foreach ($perms as $perm) {
            echo "   ✓ {$perm}\n";
        }
    }
    
    // Verificar usuários com esta role
    echo "\n👥 USUÁRIOS COM ESTA ROLE:\n";
    echo str_repeat("-", 60) . "\n";
    
    $users = \App\Models\User::role('maintenance-manager')->get();
    
    if ($users->isEmpty()) {
        echo "   ❌ Nenhum usuário possui esta role\n";
    } else {
        foreach ($users as $user) {
            echo "   👤 {$user->name} ({$user->email})\n";
        }
    }
    
    // Verificar acesso a menus/rotas específicas
    echo "\n🗂️ ANÁLISE DE ACESSO A MENUS PRINCIPAIS:\n";
    echo str_repeat("-", 60) . "\n";
    
    $menuChecks = [
        'maintenance.view' => 'Manutenção (Visualizar)',
        'maintenance.equipment' => 'Equipamentos',
        'maintenance.plan' => 'Planos de Manutenção',
        'maintenance.corrective' => 'Manutenção Corretiva',
        'maintenance.reports' => 'Relatórios de Manutenção',
        'maintenance.dashboard' => 'Dashboard de Manutenção',
        'areas.view' => 'Áreas',
        'lines.view' => 'Linhas de Produção',
        'holidays.view' => 'Feriados'
    ];
    
    foreach ($menuChecks as $permission => $description) {
        try {
            $hasAccess = $role->hasPermissionTo($permission);
            $status = $hasAccess ? "✅ ACESSO" : "❌ SEM ACESSO";
            echo "   {$status} - {$description} ({$permission})\n";
        } catch (Exception $e) {
            echo "   ❓ PERMISSÃO NÃO EXISTE - {$description} ({$permission})\n";
        }
    }
    
    // Verificar permissões relacionadas a manutenção especificamente
    echo "\n🔧 PERMISSÕES ESPECÍFICAS DE MANUTENÇÃO:\n";
    echo str_repeat("-", 60) . "\n";
    
    $maintenancePermissions = \Spatie\Permission\Models\Permission::where('name', 'like', 'maintenance%')->get();
    
    foreach ($maintenancePermissions as $perm) {
        $hasAccess = $role->hasPermissionTo($perm->name);
        $status = $hasAccess ? "✅" : "❌";
        echo "   {$status} {$perm->name}\n";
    }
    
    // Verificar se pode acessar áreas críticas
    echo "\n⚠️ VERIFICAÇÃO DE ÁREAS CRÍTICAS:\n";
    echo str_repeat("-", 60) . "\n";
    
    $criticalAreas = [
        'admin.system' => 'Configurações do Sistema',
        'admin.database' => 'Acesso à Base de Dados',
        'finance' => 'Área Financeira',
        'payroll' => 'Folha de Pagamento',
        'admin.backup' => 'Backup do Sistema'
    ];
    
    foreach ($criticalAreas as $permission => $description) {
        try {
            $hasAccess = $role->hasPermissionTo($permission);
            $status = $hasAccess ? "⚠️ TEM ACESSO" : "✅ SEM ACESSO";
            echo "   {$status} - {$description}\n";
        } catch (Exception $e) {
            echo "   ❓ PERMISSÃO NÃO EXISTE - {$description}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "DEBUG CONCLUÍDO - " . date('Y-m-d H:i:s') . "\n";
    echo str_repeat("=", 60) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
