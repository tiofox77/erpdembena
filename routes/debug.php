<?php

use Illuminate\Support\Facades\Route;

// Rota temporária para debug de sessão
Route::get('/debug-session', function () {
    $user = auth()->user();
    
    $response = [
        'logged_in' => !!$user,
        'user_info' => null,
        'maintenance_permissions' => [],
        'can_see_maintenance_menu' => false
    ];
    
    if ($user) {
        $response['user_info'] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray()
        ];
        
        // Test maintenance permissions
        $maintenancePermissions = [
            'maintenance.dashboard.view',
            'maintenance.equipment.view', 
            'maintenance.plan.view',
            'maintenance.corrective.view',
            'areas.view',
            'lines.view',
            'maintenance.technicians.view',
            'holidays.view',
            'maintenance.reports'
        ];
        
        foreach ($maintenancePermissions as $perm) {
            $response['maintenance_permissions'][$perm] = $user->can($perm);
        }
        
        $response['can_see_maintenance_menu'] = $user->canAny([
            'maintenance.dashboard.view', 
            'maintenance.equipment.view', 
            'maintenance.plan.view', 
            'maintenance.corrective.view', 
            'areas.view', 
            'lines.view', 
            'maintenance.technicians.view', 
            'holidays.view'
        ]);
    }
    
    return response()->json($response, 200, [], JSON_PRETTY_PRINT);
});

// Rota para limpar cache e testar
Route::get('/debug-clear-cache', function () {
    // Clear all caches
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    
    // Clear permission cache
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    return response()->json([
        'message' => 'Cache limpo com sucesso',
        'timestamp' => now()->toDateTimeString()
    ], 200, [], JSON_PRETTY_PRINT);
});

// Rota para testar renderização do menu
Route::get('/debug-menu-render', function () {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not logged in'], 401);
    }
    
    // Test all menu conditions from layout
    $menuTests = [
        'maintenance_main' => $user->canAny([
            'maintenance.dashboard.view', 
            'maintenance.equipment.view', 
            'maintenance.plan.view', 
            'maintenance.corrective.view', 
            'areas.view', 
            'lines.view', 
            'maintenance.technicians.view', 
            'holidays.view'
        ]),
        'maintenance_dashboard' => $user->canAny([
            'maintenance.equipment.view', 
            'maintenance.plan.view', 
            'maintenance.corrective.view', 
            'maintenance.reports'
        ]),
        'maintenance_plan' => $user->can('maintenance.plan.view'),
        'maintenance_equipment' => $user->can('maintenance.equipment.view'),
        'maintenance_corrective' => $user->can('maintenance.corrective.view'),
        'maintenance_technicians' => $user->can('maintenance.technicians.view'),
        'areas' => $user->can('areas.view'),
        'lines' => $user->can('lines.view'),
        'holidays' => $user->can('holidays.view'),
        'maintenance_reports' => $user->can('maintenance.reports')
    ];
    
    // Check route access
    $routeTests = [
        'maintenance.dashboard' => true,
        'maintenance.plan' => true,
        'maintenance.equipment' => true,
        'maintenance.corrective' => true,
        'maintenance.technicians' => true,
        'areas.index' => true,
        'lines.index' => true,
        'maintenance.holidays' => true
    ];
    
    foreach ($routeTests as $route => $expected) {
        try {
            $routeTests[$route] = \Illuminate\Support\Facades\Route::has($route);
        } catch (Exception $e) {
            $routeTests[$route] = false;
        }
    }
    
    return response()->json([
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')
        ],
        'menu_visibility' => $menuTests,
        'route_exists' => $routeTests,
        'all_permissions' => $user->getAllPermissions()->pluck('name')->sort()->values()
    ], 200, [], JSON_PRETTY_PRINT);
});

// Rota para testar @canany específico
Route::get('/debug-canany-test', function () {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not logged in'], 401);
    }
    
    // Test the exact @canany from layout
    $mainMenuCanAny = $user->canAny([
        'maintenance.dashboard.view', 
        'maintenance.equipment.view', 
        'maintenance.plan.view', 
        'maintenance.corrective.view', 
        'areas.view', 
        'lines.view', 
        'maintenance.technicians.view', 
        'holidays.view'
    ]);
    
    $dashboardCanAny = $user->canAny([
        'maintenance.equipment.view', 
        'maintenance.plan.view', 
        'maintenance.corrective.view', 
        'maintenance.reports'
    ]);
    
    // Test individual permissions
    $individualTests = [];
    $permissions = [
        'maintenance.dashboard.view', 
        'maintenance.equipment.view', 
        'maintenance.plan.view', 
        'maintenance.corrective.view', 
        'areas.view', 
        'lines.view', 
        'maintenance.technicians.view', 
        'holidays.view',
        'maintenance.reports'
    ];
    
    foreach ($permissions as $perm) {
        $individualTests[$perm] = $user->can($perm);
    }
    
    // Test if user has role
    $hasMaintenanceRole = $user->hasRole('maintenance-manager');
    
    return response()->json([
        'user_info' => [
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id
        ],
        'role_check' => [
            'has_maintenance_manager' => $hasMaintenanceRole,
            'all_roles' => $user->roles->pluck('name')
        ],
        'canany_results' => [
            'main_menu_canany' => $mainMenuCanAny,
            'dashboard_canany' => $dashboardCanAny
        ],
        'individual_permissions' => $individualTests,
        'blade_simulation' => [
            'main_menu_should_show' => $mainMenuCanAny,
            'dashboard_should_show' => $dashboardCanAny,
            'plan_should_show' => $user->can('maintenance.plan.view'),
            'equipment_should_show' => $user->can('maintenance.equipment.view'),
            'areas_should_show' => $user->can('areas.view'),
            'holidays_should_show' => $user->can('holidays.view')
        ]
    ], 200, [], JSON_PRETTY_PRINT);
});

// Rota para verificar todas as permissões de manutenção
Route::get('/debug-maintenance-complete', function () {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not logged in'], 401);
    }
    
    // Todas as permissões relacionadas com manutenção no sistema
    $allMaintenancePermissions = [
        // Dashboard
        'maintenance.dashboard.view',
        'maintenance.dashboard.manage',
        
        // Equipment
        'maintenance.equipment.view',
        'maintenance.equipment.create',
        'maintenance.equipment.edit',
        'maintenance.equipment.delete',
        'maintenance.equipment.manage',
        'equipment.view',
        'equipment.create',
        'equipment.edit',
        'equipment.delete',
        'equipment.manage',
        'equipment.export',
        'equipment.import',
        'equipment.parts.view',
        'equipment.parts.manage',
        
        // Plans
        'maintenance.plan.view',
        'maintenance.plan.create',
        'maintenance.plan.edit',
        'maintenance.plan.delete',
        'maintenance.plan.manage',
        'preventive.view',
        'preventive.create',
        'preventive.edit',
        'preventive.delete',
        'preventive.manage',
        'preventive.schedule',
        'preventive.complete',
        
        // Corrective
        'maintenance.corrective.view',
        'maintenance.corrective.create',
        'maintenance.corrective.edit',
        'maintenance.corrective.delete',
        'maintenance.corrective.manage',
        'corrective.view',
        'corrective.create',
        'corrective.edit',
        'corrective.delete',
        'corrective.manage',
        'corrective.complete',
        
        // Technicians
        'maintenance.technicians.view',
        'maintenance.technicians.manage',
        'technicians.view',
        'technicians.create',
        'technicians.edit',
        'technicians.delete',
        'technicians.manage',
        
        // Tasks
        'maintenance.task.view',
        'maintenance.task.create',
        'maintenance.task.edit',
        'maintenance.task.delete',
        'maintenance.task.manage',
        'task.view',
        'task.create',
        'task.edit',
        'task.delete',
        'task.manage',
        
        // Areas & Lines
        'areas.view',
        'areas.create',
        'areas.edit',
        'areas.delete',
        'areas.manage',
        'lines.view',
        'lines.create',
        'lines.edit',
        'lines.delete',
        'lines.manage',
        'maintenance.linearea.view',
        'maintenance.linearea.manage',
        
        // Parts & Stock
        'parts.view',
        'parts.create',
        'parts.edit',
        'parts.delete',
        'parts.manage',
        'parts.request',
        'stock.view',
        'stock.manage',
        'stock.in',
        'stock.out',
        'stock.history',
        'stocks.stockin',
        'stocks.stockout',
        'stocks.history',
        'stocks.part-requests',
        
        // Holidays
        'holidays.view',
        'holidays.create',
        'holidays.edit',
        'holidays.delete',
        'holidays.manage',
        'maintenance.holidays.view',
        'maintenance.holidays.manage',
        
        // Reports & History
        'maintenance.reports',
        'reports.view',
        'history.maintenance.audit',
        'history.equipment.timeline',
        'history.parts.lifecycle',
        'history.team.performance',
        
        // Settings & Configuration
        'maintenance.settings',
        'maintenance.failure-modes',
        'maintenance.failure-mode-categories',
        'maintenance.failure-causes',
        'maintenance.failure-cause-categories',
        'maintenance.calendar',
        'maintenance.schedule',
        
        // Roles & Users
        'maintenance.roles.view',
        'maintenance.roles.manage',
        'maintenance.users.view',
        'maintenance.users.manage',
        
        // General
        'maintenance.view',
        'maintenance.create',
        'maintenance.edit',
        'maintenance.delete',
        'maintenance.manage',
        'maintenance.export'
    ];
    
    // Verificar quais o utilizador tem
    $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
    $hasPermissions = [];
    $missingPermissions = [];
    
    foreach ($allMaintenancePermissions as $perm) {
        if (in_array($perm, $userPermissions)) {
            $hasPermissions[] = $perm;
        } else {
            // Verificar se a permissão existe na BD
            $exists = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
            $missingPermissions[] = [
                'permission' => $perm,
                'exists_in_db' => $exists
            ];
        }
    }
    
    return response()->json([
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'total_permissions' => count($userPermissions)
        ],
        'maintenance_analysis' => [
            'total_maintenance_permissions_checked' => count($allMaintenancePermissions),
            'user_has_count' => count($hasPermissions),
            'user_missing_count' => count($missingPermissions),
            'coverage_percentage' => round((count($hasPermissions) / count($allMaintenancePermissions)) * 100, 1)
        ],
        'permissions_user_has' => $hasPermissions,
        'permissions_missing' => $missingPermissions,
        'summary' => [
            'can_work_fully' => count($missingPermissions) === 0,
            'missing_critical' => array_filter($missingPermissions, function($item) {
                return in_array($item['permission'], [
                    'maintenance.dashboard.view',
                    'maintenance.equipment.view',
                    'maintenance.plan.view',
                    'maintenance.corrective.view',
                    'maintenance.technicians.view'
                ]);
            })
        ]
    ], 200, [], JSON_PRETTY_PRINT);
});
