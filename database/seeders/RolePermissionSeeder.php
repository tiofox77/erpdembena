<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões por módulo
        // Permissões para Equipamentos
        $equipmentPermissions = [
            'equipment.view',
            'equipment.create',
            'equipment.edit',
            'equipment.delete',
            'equipment.import',
            'equipment.export',
        ];

        // Permissões para Manutenção Preventiva
        $preventivePermissions = [
            'preventive.view',
            'preventive.create',
            'preventive.edit',
            'preventive.delete',
            'preventive.schedule',
            'preventive.complete',
        ];

        // Permissões para Manutenção Corretiva
        $correctivePermissions = [
            'corrective.view',
            'corrective.create',
            'corrective.edit',
            'corrective.delete',
            'corrective.complete',
        ];

        // Permissões para Relatórios
        $reportPermissions = [
            'reports.view',
            'reports.export',
            'reports.dashboard',
        ];

        // Permissões para Usuários e Perfis
        $userPermissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
        ];

        // Permissões para Configurações
        $settingsPermissions = [
            'settings.view',
            'settings.edit',
        ];

        // Permissões para Áreas e Linhas
        $areaLinePermissions = [
            'areas.view',
            'areas.create',
            'areas.edit',
            'areas.delete',
            'lines.view',
            'lines.create',
            'lines.edit',
            'lines.delete',
        ];

        // Juntar todas as permissões
        $allPermissions = array_merge(
            $equipmentPermissions,
            $preventivePermissions,
            $correctivePermissions,
            $reportPermissions,
            $userPermissions,
            $settingsPermissions,
            $areaLinePermissions
        );

        // Criar permissões no banco de dados
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Criar roles
        // Super Admin - tem todas as permissões
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - pode gerenciar quase tudo, exceto permissões de super admin
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(array_diff($allPermissions, ['users.delete']));

        // Manager - pode ver tudo e gerenciar algumas coisas
        $managerRole = Role::create(['name' => 'manager']);
        $managerPermissions = array_merge(
            ['equipment.view', 'equipment.edit'],
            ['preventive.view', 'preventive.edit', 'preventive.schedule', 'preventive.complete'],
            ['corrective.view', 'corrective.edit', 'corrective.complete'],
            $reportPermissions,
            ['users.view'],
            ['settings.view'],
            ['areas.view', 'lines.view']
        );
        $managerRole->givePermissionTo($managerPermissions);

        // Technician - foco operacional
        $technicianRole = Role::create(['name' => 'technician']);
        $technicianPermissions = [
            'equipment.view',
            'preventive.view', 'preventive.complete',
            'corrective.view', 'corrective.create', 'corrective.complete',
            'reports.view',
            'areas.view', 'lines.view'
        ];
        $technicianRole->givePermissionTo($technicianPermissions);

        // User - acesso básico de visualização
        $userRole = Role::create(['name' => 'user']);
        $userPermissions = [
            'equipment.view',
            'preventive.view',
            'corrective.view', 'corrective.create',
            'areas.view', 'lines.view'
        ];
        $userRole->givePermissionTo($userPermissions);

        // Report - apenas para visualização de relatórios
        $reportRole = Role::create(['name' => 'report']);
        $reportRole->givePermissionTo($reportPermissions);

        // Atribuir role super-admin para um usuário (se existir)
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->assignRole('super-admin');
        }
    }
}
