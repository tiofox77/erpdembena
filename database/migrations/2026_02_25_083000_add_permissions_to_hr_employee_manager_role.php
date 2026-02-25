<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adicionar permissões de leave, overtime, salary advances e salary discounts
     * à role hr-employee-manager
     */
    public function up(): void
    {
        $permissions = [
            'hr.leave.view',
            'hr.leave.create',
            'hr.leave.request',
            'hr.leave.approve',
            'hr.leave.export',
            'hr.payroll.view',
        ];

        // Garantir que as permissões existem
        foreach ($permissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName],
                ['guard_name' => 'web']
            );
        }

        // Atribuir permissões à role hr-employee-manager
        $role = Role::where('name', 'hr-employee-manager')->first();
        if ($role) {
            foreach ($permissions as $permName) {
                if (!$role->hasPermissionTo($permName)) {
                    $role->givePermissionTo($permName);
                }
            }
        }

        // Limpar cache de permissões
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'hr.leave.view',
            'hr.leave.create',
            'hr.leave.request',
            'hr.leave.approve',
            'hr.leave.export',
            'hr.payroll.view',
        ];

        $role = Role::where('name', 'hr-employee-manager')->first();
        if ($role) {
            foreach ($permissions as $permName) {
                $role->revokePermissionTo($permName);
            }
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
