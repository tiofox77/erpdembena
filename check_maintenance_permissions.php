<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICANDO PERMISSÕES DA ROLE MAINTENANCE-MANAGER ===\n\n";

try {
    // Verificar se a role existe
    $role = Role::where('name', 'maintenance-manager')->first();
    
    if (!$role) {
        echo "❌ ERRO: A role 'maintenance-manager' não foi encontrada.\n";
        exit(1);
    }
    
    echo "✅ Role encontrada: {$role->name}\n";
    echo "📊 Total de permissões: " . $role->permissions->count() . "\n\n";
    
    // Listar permissões atuais
    echo "🔑 PERMISSÕES ATUAIS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $currentPermissions = $role->permissions->pluck('name')->toArray();
    sort($currentPermissions);
    
    foreach ($currentPermissions as $perm) {
        echo "  ✓ {$perm}\n";
    }
    
    // Permissões essenciais para ver o menu de maintenance (baseado no layout)
    $requiredForMenu = [
        'equipment.view',
        'preventive.view', 
        'corrective.view',
        'reports.view',
        'parts.view',
        'stock.manage',
        'settings.manage'
    ];
    
    echo "\n🎯 PERMISSÕES ESSENCIAIS PARA VER O MENU MAINTENANCE:\n";
    echo str_repeat("-", 50) . "\n";
    
    $missingPermissions = [];
    foreach ($requiredForMenu as $requiredPerm) {
        if (in_array($requiredPerm, $currentPermissions)) {
            echo "  ✅ {$requiredPerm} - PRESENTE\n";
        } else {
            echo "  ❌ {$requiredPerm} - AUSENTE\n";
            $missingPermissions[] = $requiredPerm;
        }
    }
    
    // Permissões adicionais importantes
    $additionalImportant = [
        'areas.view',
        'technicians.view',
        'roles.view',
        'maintenance.view',
        'maintenance.dashboard',
        'maintenance.*'
    ];
    
    echo "\n🔧 PERMISSÕES ADICIONAIS IMPORTANTES:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($additionalImportant as $additionalPerm) {
        if (in_array($additionalPerm, $currentPermissions)) {
            echo "  ✅ {$additionalPerm} - PRESENTE\n";
        } else {
            echo "  ⚠️  {$additionalPerm} - AUSENTE\n";
            $missingPermissions[] = $additionalPerm;
        }
    }
    
    // Resumo
    echo "\n📋 RESUMO:\n";
    echo str_repeat("=", 50) . "\n";
    
    if (count($missingPermissions) === 0) {
        echo "🎉 TODAS as permissões essenciais estão presentes!\n";
        echo "✅ O menu de maintenance deveria aparecer normalmente.\n";
    } else {
        echo "⚠️  FALTAM " . count($missingPermissions) . " permissões essenciais:\n\n";
        foreach ($missingPermissions as $missing) {
            echo "  - {$missing}\n";
        }
        echo "\n💡 Estas permissões precisam ser adicionadas para que o menu apareça.\n";
    }
    
    // Verificar se pelo menos uma das principais permissões existe
    $mainMenuPermissions = ['equipment.view', 'preventive.view', 'corrective.view', 'reports.view'];
    $hasAnyMainPermission = false;
    
    foreach ($mainMenuPermissions as $mainPerm) {
        if (in_array($mainPerm, $currentPermissions)) {
            $hasAnyMainPermission = true;
            break;
        }
    }
    
    echo "\n🔍 DIAGNÓSTICO DO PROBLEMA:\n";
    echo str_repeat("-", 50) . "\n";
    
    if (!$hasAnyMainPermission) {
        echo "❌ PROBLEMA IDENTIFICADO: A role não tem NENHUMA das permissões principais.\n";
        echo "   O menu de maintenance precisa de pelo menos uma das seguintes:\n";
        foreach ($mainMenuPermissions as $mainPerm) {
            echo "   - {$mainPerm}\n";
        }
        echo "\n   📝 SOLUÇÃO: Adicionar pelo menos uma destas permissões.\n";
    } else {
        echo "✅ A role tem pelo menos uma permissão principal.\n";
        echo "🤔 O problema pode ser:\n";
        echo "   - Cache de permissões\n";
        echo "   - Usuário não tem a role atribuída\n";
        echo "   - Sessão precisa ser renovada\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
