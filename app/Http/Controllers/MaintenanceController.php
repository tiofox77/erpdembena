<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceCategory;
use App\Models\MaintenanceDepartment;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceSupply;
use App\Models\MaintenanceFile;
use App\Models\MaintenanceHoliday;
use App\Models\MaintenanceWorkingDay;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the dashboard view.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $path = $request->path();

        if (strpos($path, 'maintenance/dashboard') !== false) {
            return view('maintenance.dashboard');
        } else if (strpos($path, 'maintenance/equipment') !== false) {
            return view('maintenance.equipment');
        } else if (strpos($path, 'maintenance/task') !== false) {
            return view('maintenance.task');
        } else if (strpos($path, 'maintenance/scheduling') !== false) {
            return view('maintenance.scheduling');
        } else if (strpos($path, 'maintenance/category') !== false) {
            return view('maintenance.category');
        }

        // Fallback para o dashboard
        return view('maintenance.dashboard');
    }

    /**
     * Display the equipment index.
     *
     * @return \Illuminate\View\View
     */
    public function equipment()
    {
        return view('maintenance.equipment.index');
    }

    /**
     * Display the equipment create form.
     *
     * @return \Illuminate\View\View
     */
    public function equipmentCreate()
    {
        return view('maintenance.equipment-create');
    }

    /**
     * Display the tasks index.
     *
     * @return \Illuminate\View\View
     */
    public function task()
    {
        return view('maintenance.task.index');
    }

    /**
     * Display the maintenance scheduling view.
     *
     * @return \Illuminate\View\View
     */
    public function scheduling()
    {
        return view('maintenance.scheduling.index');
    }

    /**
     * Display the maintenance scheduling create form.
     *
     * @return \Illuminate\View\View
     */
    public function schedulingCreate()
    {
        return view('maintenance.scheduling-create');
    }

    /**
     * Display the line-area management view.
     *
     * @return \Illuminate\View\View
     */
    public function lineArea()
    {
        return view('maintenance.linearea');
    }

    /**
     * Display the corrective maintenance view.
     *
     * @return \Illuminate\View\View
     */
    public function corrective()
    {
        return view('maintenance.corrective');
    }

    /**
     * Display the reports overview.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function reports()
    {
        return view('maintenance.reports');
    }

    /**
     * Display report details view.
     *
     * @return \Illuminate\View\View
     */
    public function reportView()
    {
        return view('maintenance.report-view');
    }

    /**
     * Display the user management view.
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        return view('maintenance.users');
    }

    /**
     * Display the role management view.
     *
     * @return \Illuminate\View\View
     */
    public function roles()
    {
        return view('maintenance.roles');
    }

    /**
     * Display the holidays management view.
     *
     * @return \Illuminate\View\View
     */
    public function holidays()
    {
        return view('maintenance.holidays');
    }

    /**
     * Display the settings view.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        return view('maintenance.settings');
    }

    /**
     * Display the supply chain view.
     *
     * @return \Illuminate\View\View
     */
    public function supplyChain()
    {
        return view('maintenance.supply-chain');
    }

    /**
     * Display the category create form.
     *
     * @return \Illuminate\View\View
     */
    public function categoryCreate()
    {
        return view('maintenance.category-create');
    }

    /**
     * Display the department create form.
     *
     * @return \Illuminate\View\View
     */
    public function departmentCreate()
    {
        return view('maintenance.department-create');
    }

    /**
     * Get overdue tasks for notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOverdueTasks()
    {
        // Aqui você implementaria a lógica para buscar tarefas atrasadas
        // Este é um exemplo simples; em produção, você buscaria do banco de dados
        return response()->json([
            'tasks' => [
                [
                    'id' => 1,
                    'title' => 'Manutenção da Bomba #3',
                    'equipment' => 'Bomba de Alimentação EDTA',
                    'due_date' => '2023-03-20',
                    'days_overdue' => 4,
                    'priority' => 'high'
                ],
                [
                    'id' => 2,
                    'title' => 'Verificação do Sistema de Refrigeração',
                    'equipment' => 'Reator Principal',
                    'due_date' => '2023-03-22',
                    'days_overdue' => 2,
                    'priority' => 'medium'
                ]
            ]
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationAsRead(Request $request)
    {
        // Aqui você implementaria a lógica para marcar uma notificação como lida
        return response()->json(['success' => true]);
    }
}
