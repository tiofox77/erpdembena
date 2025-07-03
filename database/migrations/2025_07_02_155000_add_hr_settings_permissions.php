<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criar a nova permissão para configurações de RH
        $permission = Permission::firstOrCreate(
            ['name' => 'hr.settings.view'],
            ['guard_name' => 'web']
        );
        
        // Atribuir a permissão ao papel de hr-manager
        $hrManagerRole = Role::where('name', 'hr-manager')->first();
        if ($hrManagerRole) {
            $hrManagerRole->givePermissionTo($permission);
        }
        
        // Atribuir a permissão ao papel de super-admin
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover a permissão
        $permission = Permission::where('name', 'hr.settings.view')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
