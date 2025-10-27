<?php

require_once 'vendor/autoload.php';

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICAÇÃO DE ROLES EXISTENTES ===\n\n";

try {
    $roles = Role::all();
    echo "📊 Total de roles encontradas: " . $roles->count() . "\n\n";
    
    foreach ($roles as $role) {
        $userCount = \App\Models\User::role($role->name)->count();
        $permCount = $role->permissions->count();
        echo "🎭 {$role->name} (ID: {$role->id})\n";
        echo "   - Usuários: {$userCount}\n";
        echo "   - Permissões: {$permCount}\n\n";
    }
    
    $permissions = Permission::all();
    echo "📝 Total de permissões: " . $permissions->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
