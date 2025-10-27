<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * Service responsável por determinar o dashboard apropriado para o usuário
 * com base nas suas permissões organizadas por módulos
 */
class UserDashboardService
{
    /**
     * Determina o dashboard apropriado para o usuário baseado nas suas permissões
     * Ordem de prioridade: HR > Supply Chain > MRP > Maintenance
     *
     * @param User $user
     * @return string
     */
    public static function determineUserDashboard(User $user): string
    {
        // Verificar permissões de HR primeiro
        if (self::hasHrPermissions($user)) {
            return route('hr.dashboard');
        }
        
        // Verificar permissões de Supply Chain
        if (self::hasSupplyChainPermissions($user)) {
            return route('supply-chain.dashboard');
        }
        
        // Verificar permissões de MRP
        if (self::hasMrpPermissions($user)) {
            return route('mrp.dashboard');
        }
        
        // Verificar permissões de Maintenance (mais comum)
        if (self::hasMaintenancePermissions($user)) {
            return route('maintenance.dashboard');
        }
        
        // Se não tem permissões específicas, redirecionar para dashboard restrito
        return route('dashboard');
    }

    /**
     * Verifica se o usuário tem permissões de HR
     *
     * @param User $user
     * @return bool
     */
    public static function hasHrPermissions(User $user): bool
    {
        $hrPermissions = [
            'hr.dashboard',
            'hr.employees.view',
            'hr.payroll.view',
            'hr.departments.view',
            'hr.holidays.view',
            'hr.training.view'
        ];

        return self::hasAnyPermission($user, $hrPermissions);
    }

    /**
     * Verifica se o usuário tem permissões de Supply Chain
     *
     * @param User $user
     * @return bool
     */
    public static function hasSupplyChainPermissions(User $user): bool
    {
        $supplyChainPermissions = [
            'supplychain.dashboard',
            'supplychain.purchase_orders.view',
            'supplychain.goods_receipts.view',
            'supplychain.products.view',
            'supplychain.suppliers.view',
            'supplychain.inventory.view',
            'inventory.view', // Legacy permission
            'stock.view',     // Legacy permission
            'parts.view'      // Legacy permission
        ];

        return self::hasAnyPermission($user, $supplyChainPermissions);
    }

    /**
     * Verifica se o usuário tem permissões de MRP
     *
     * @param User $user
     * @return bool
     */
    public static function hasMrpPermissions(User $user): bool
    {
        $mrpPermissions = [
            'mrp.dashboard',
            'mrp.bom.view',
            'mrp.production.view',
            'mrp.planning.view',
            'mrp.work_orders.view',
            'mrp.routing.view'
        ];

        return self::hasAnyPermission($user, $mrpPermissions);
    }

    /**
     * Verifica se o usuário tem permissões de Maintenance
     *
     * @param User $user
     * @return bool
     */
    public static function hasMaintenancePermissions(User $user): bool
    {
        $maintenancePermissions = [
            'equipment.view',
            'preventive.view',
            'corrective.view',
            'reports.view',
            'areas.view',
            'lines.view',
            'task.view',
            'technicians.view',
            'stocks.view',
            'settings.view',
            'users.view',
            'roles.view',
            'holidays.view',
            'history.view'
        ];

        return self::hasAnyPermission($user, $maintenancePermissions);
    }

    /**
     * Verifica se o usuário tem pelo menos uma das permissões fornecidas
     *
     * @param User $user
     * @param array $permissions
     * @return bool
     */
    private static function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtém o módulo principal do usuário baseado nas suas permissões
     *
     * @param User $user
     * @return string
     */
    public static function getUserPrimaryModule(User $user): string
    {
        if (self::hasHrPermissions($user)) {
            return 'hr';
        }
        
        if (self::hasSupplyChainPermissions($user)) {
            return 'supplychain';
        }
        
        if (self::hasMrpPermissions($user)) {
            return 'mrp';
        }
        
        if (self::hasMaintenancePermissions($user)) {
            return 'maintenance';
        }
        
        return 'none';
    }

    /**
     * Obtém todos os módulos que o usuário tem acesso
     *
     * @param User $user
     * @return array
     */
    public static function getUserModules(User $user): array
    {
        $modules = [];

        if (self::hasHrPermissions($user)) {
            $modules[] = 'hr';
        }
        
        if (self::hasSupplyChainPermissions($user)) {
            $modules[] = 'supplychain';
        }
        
        if (self::hasMrpPermissions($user)) {
            $modules[] = 'mrp';
        }
        
        if (self::hasMaintenancePermissions($user)) {
            $modules[] = 'maintenance';
        }

        return $modules;
    }
}
