<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class FixHrManagerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds to fix the HR Manager role permissions.
     * This will ensure HR Manager can only see HR module and not maintenance/supply chain.
     */
    public function run(): void
    {
        // Get HR Manager role
        $hrRole = Role::findByName('hr-manager', 'web');
        
        // Remove any non-HR related permissions
        $nonHrPermissions = [
            'reports.view', 
            'reports.dashboard',
            'reports.export'
        ];
        
        $hrRole->revokePermissionTo($nonHrPermissions);
        
        // Find the HR user
        $hrUser = User::where('email', 'hr@erpdembena.com')->first();
        
        if ($hrUser) {
            // Ensure user has HR Manager role only
            $hrUser->syncRoles(['hr-manager']);
            
            $this->command->info('HR Manager permissions fixed successfully!');
        } else {
            $this->command->error('HR Manager user not found!');
        }
    }
}
