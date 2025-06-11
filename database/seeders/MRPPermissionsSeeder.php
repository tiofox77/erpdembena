<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MRPPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // MRP Module Permissions
        $mrpPermissions = [
            // Production Scheduling
            'mrp.scheduling.view',
            'mrp.scheduling.create',
            'mrp.scheduling.edit',
            'mrp.scheduling.delete',
            'mrp.scheduling.approve',
            
            // Resources Management
            'mrp.resources.view',
            'mrp.resources.manage',
            
            // Shifts Management
            'mrp.shifts.view',
            'mrp.shifts.manage',
            
            // Lines Management
            'mrp.lines.view',
            'mrp.lines.manage',
            
            // Inventory Levels
            'mrp.inventory.view',
            'mrp.inventory.manage',
            
            // Reports
            'mrp.reports.view',
            'mrp.reports.export',
            
            // Settings
            'mrp.settings.manage',
        ];


        // Create permissions in database
        foreach ($mrpPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['guard_name' => 'web']);
        }

        // Assign permissions to roles
        $superAdmin = Role::where('name', 'super-admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $manager = Role::where('name', 'manager')->first();
        $planner = Role::where('name', 'production-planner')->first();
        $supervisor = Role::where('name', 'production-supervisor')->first();
        $operator = Role::where('name', 'operator')->first();
        $viewer = Role::where('name', 'viewer')->first();

        // Create roles if they don't exist
        if (!$planner) {
            $planner = Role::create(['name' => 'production-planner']);
        }
        
        if (!$supervisor) {
            $supervisor = Role::create(['name' => 'production-supervisor']);
        }
        
        if (!$operator) {
            $operator = Role::create(['name' => 'operator']);
        }
        
        if (!$viewer) {
            $viewer = Role::create(['name' => 'viewer']);
        }

        // Assign all MRP permissions to super-admin
        if ($superAdmin) {
            $superAdmin->givePermissionTo($mrpPermissions);
        }

        // Admin gets all MRP permissions except settings
        if ($admin) {
            $adminPermissions = array_filter($mrpPermissions, function($permission) {
                return $permission !== 'mrp.settings.manage';
            });
            $admin->givePermissionTo($adminPermissions);
        }

        // Production Planner permissions
        $plannerPermissions = [
            'mrp.scheduling.view',
            'mrp.scheduling.create',
            'mrp.scheduling.edit',
            'mrp.resources.view',
            'mrp.shifts.view',
            'mrp.lines.view',
            'mrp.inventory.view',
            'mrp.reports.view',
        ];
        $planner->syncPermissions($plannerPermissions);

        // Production Supervisor permissions
        $supervisorPermissions = [
            'mrp.scheduling.view',
            'mrp.scheduling.edit',
            'mrp.resources.view',
            'mrp.shifts.view',
            'mrp.lines.view',
            'mrp.inventory.view',
            'mrp.reports.view',
        ];
        $supervisor->syncPermissions($supervisorPermissions);

        // Operator permissions
        $operatorPermissions = [
            'mrp.scheduling.view',
            'mrp.resources.view',
            'mrp.shifts.view',
            'mrp.lines.view',
            'mrp.inventory.view',
        ];
        $operator->syncPermissions($operatorPermissions);

        // Viewer permissions (read-only)
        $viewerPermissions = [
            'mrp.scheduling.view',
            'mrp.resources.view',
            'mrp.shifts.view',
            'mrp.lines.view',
            'mrp.inventory.view',
            'mrp.reports.view',
        ];
        $viewer->syncPermissions($viewerPermissions);
    }
}
