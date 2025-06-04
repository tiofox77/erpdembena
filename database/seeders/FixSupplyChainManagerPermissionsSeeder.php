<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class FixSupplyChainManagerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds to fix the Supply Chain Manager role permissions.
     * This will ensure Supply Chain Manager can only see Supply Chain module and not maintenance/HR.
     */
    public function run(): void
    {
        // Get Supply Chain Manager role
        $supplyChainRole = Role::findByName('supply-chain-manager', 'web');
        
        // Remove any non-Supply Chain related permissions
        $nonSupplyChainPermissions = [
            'reports.view', 
            'reports.dashboard',
            'reports.export'
        ];
        
        $supplyChainRole->revokePermissionTo($nonSupplyChainPermissions);
        
        // Find the Supply Chain user
        $supplyChainUser = User::where('email', 'supplychain@erpdembena.com')->first();
        
        if ($supplyChainUser) {
            // Ensure user has Supply Chain Manager role only
            $supplyChainUser->syncRoles(['supply-chain-manager']);
            
            $this->command->info('Supply Chain Manager permissions fixed successfully!');
        } else {
            $this->command->error('Supply Chain Manager user not found!');
        }
    }
}
