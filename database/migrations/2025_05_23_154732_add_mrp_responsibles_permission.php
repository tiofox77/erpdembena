<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criar permissão para Responsáveis no MRP
        Permission::create(['name' => 'mrp.responsibles.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'mrp.responsibles.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'mrp.responsibles.edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'mrp.responsibles.delete', 'guard_name' => 'web']);
        
        // Adicionar permissões à função de administrador
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'mrp.responsibles.view',
                'mrp.responsibles.create',
                'mrp.responsibles.edit',
                'mrp.responsibles.delete'
            ]);
        }
        
        // Adicionar permissão à função de gerente de MRP, se existir
        $mrpManagerRole = Role::where('name', 'mrp_manager')->first();
        if ($mrpManagerRole) {
            $mrpManagerRole->givePermissionTo([
                'mrp.responsibles.view',
                'mrp.responsibles.create',
                'mrp.responsibles.edit',
                'mrp.responsibles.delete'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover permissões
        $permissions = [
            'mrp.responsibles.view',
            'mrp.responsibles.create',
            'mrp.responsibles.edit',
            'mrp.responsibles.delete'
        ];
        
        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
