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

        // Create permissions by module
        // Equipment permissions
        $equipmentPermissions = [
            'equipment.view',
            'equipment.create',
            'equipment.edit',
            'equipment.delete',
            'equipment.import',
            'equipment.export',
        ];

        // Preventive Maintenance permissions
        $preventivePermissions = [
            'preventive.view',
            'preventive.create',
            'preventive.edit',
            'preventive.delete',
            'preventive.schedule',
            'preventive.complete',
        ];

        // Corrective Maintenance permissions
        $correctivePermissions = [
            'corrective.view',
            'corrective.create',
            'corrective.edit',
            'corrective.delete',
            'corrective.complete',
            'corrective.manage',
        ];

        // Report permissions
        $reportPermissions = [
            'reports.view',
            'reports.export',
            'reports.dashboard',
        ];

        // User and Role permissions
        $userPermissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage',
            'roles.manage',
        ];

        // Settings permissions
        $settingsPermissions = [
            'settings.view',
            'settings.edit',
            'settings.manage',
        ];

        // Areas and Lines permissions
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

        // Merge all permissions
        $allPermissions = array_merge(
            $equipmentPermissions,
            $preventivePermissions,
            $correctivePermissions,
            $reportPermissions,
            $userPermissions,
            $settingsPermissions,
            $areaLinePermissions
        );

        // Create permissions in database (using firstOrCreate to avoid duplicates)
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['guard_name' => 'web']);
        }

        // Create roles (only if they don't exist)
        // Super Admin - has all permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdminRole->syncPermissions(Permission::all());

        // Admin - can manage almost everything, except super admin permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(array_diff($allPermissions, ['users.delete']));

        // Manager - can view everything and manage some things
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerPermissions = array_merge(
            ['equipment.view', 'equipment.edit'],
            ['preventive.view', 'preventive.edit', 'preventive.schedule', 'preventive.complete'],
            ['corrective.view', 'corrective.edit', 'corrective.complete'],
            $reportPermissions,
            ['users.view'],
            ['settings.view'],
            ['areas.view', 'lines.view']
        );
        $managerRole->syncPermissions($managerPermissions);

        // Technician - operational focus
        $technicianRole = Role::firstOrCreate(['name' => 'technician']);
        $technicianPermissions = [
            'equipment.view',
            'preventive.view', 'preventive.complete',
            'corrective.view', 'corrective.create', 'corrective.complete',
            'reports.view',
            'areas.view', 'lines.view'
        ];
        $technicianRole->syncPermissions($technicianPermissions);

        // User - basic view access
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userPermissions = [
            'equipment.view',
            'preventive.view',
            'corrective.view',
            'areas.view',
            'lines.view',
            'reports.view'
        ];
        $userRole->syncPermissions($userPermissions);

        // Report - for report viewing only
        $reportRole = Role::firstOrCreate(['name' => 'report']);
        $reportRole->syncPermissions($reportPermissions);

        // Assign super-admin role to a user (if exists)
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->assignRole('super-admin');
        }
    }
}
