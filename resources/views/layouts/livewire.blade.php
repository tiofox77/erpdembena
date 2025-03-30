<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Sistema de Manutenção</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Toastr CSS e JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- FullCalendar Core -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />

    <!-- Tippy.js -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css"/>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- FullCalendar Locale -->
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/pt-br.global.min.js'></script>

    <!-- Chart.js - MOVIDO AQUI PARA EVITAR DUPLICAÇÃO -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        .sidebar {
            width: 255px;
            height: 100vh;
            position: fixed;
            background-color: #f8f9fa;
            z-index: 10;
            overflow-y: auto;
            box-shadow: 1px 0 5px rgba(0,0,0,0.05);
        }

        .main-content {
            margin-left: 255px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            color: #333;
        }

        .sidebar-header i {
            font-size: 18px;
            margin-right: 10px;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #5a6268;
            transition: all 0.2s;
            cursor: pointer;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu-item:hover {
            background-color: rgba(0,0,0,0.02);
        }

        .sidebar-menu-item.active {
            background-color: #e9ecef;
            color: #4f46e5;
        }

        .sidebar-menu-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: #6c757d;
        }

        .sidebar-submenu-item {
            display: flex;
            align-items: center;
            padding: 10px 16px 10px 46px;
            color: #666;
            transition: all 0.2s;
            cursor: pointer;
            font-size: 14px;
        }

        .sidebar-submenu-item:hover {
            background-color: rgba(0,0,0,0.02);
        }

        .sidebar-submenu-item.active {
            color: #4f46e5;
            font-weight: 500;
            background-color: rgba(79, 70, 229, 0.1);
        }

        .sidebar-submenu-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: #6c757d;
        }

        .sidebar-submenu {
            height: auto;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #fff;
        }

        .sidebar-submenu.open {
            max-height: 1000px; /* Arbitrary large value */
        }

        .dropdown-indicator {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .dropdown-indicator.open {
            transform: rotate(180deg);
        }

        .user-info {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(0,0,0,0.05);
            background-color: #f8f9fa;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef;
            color: #6c757d;
            font-weight: 600;
        }

        .user-details {
            line-height: 1.2;
        }

        .user-name {
            font-weight: 500;
            font-size: 14px;
            color: #333;
        }

        .user-role {
            font-size: 12px;
            color: #6c757d;
        }

        .header {
            height: 65px;
            border-bottom: 1px solid #e5e7eb;
            background-color: white;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ef4444;
            color: white;
            border-radius: 9999px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .metrics-card {
            text-align: center;
            padding: 24px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .metrics-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .metrics-card .icon.blue {
            background-color: #e0f2fe;
            color: #0284c7;
        }

        .metrics-card .icon.yellow {
            background-color: #fef9c3;
            color: #ca8a04;
        }

        .metrics-card .icon.red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .metrics-card .icon.green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .metrics-card .icon.purple {
            background-color: #f3e8ff;
            color: #9333ea;
        }

        .metrics-card .number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .alert-item {
            background-color: #fef2f2;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .overdue-badge {
            background-color: #ef4444;
            color: white;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .action-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .action-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .action-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .action-card .icon.blue {
            background-color: #e0f2fe;
            color: #0284c7;
        }

        .action-card .icon.green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .action-card .icon.purple {
            background-color: #f3e8ff;
            color: #9333ea;
        }

        .action-card .icon.orange {
            background-color: #ffedd5;
            color: #ea580c;
        }

        .action-card .title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .action-card .subtitle {
            font-size: 12px;
            color: #6b7280;
        }

        .sidebar-nested-submenu {
            height: auto;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #f5f5f5;
        }

        .sidebar-nested-submenu.open {
            max-height: 1000px; /* Arbitrary large value */
        }

        .sidebar-nested-submenu-item {
            display: flex;
            align-items: center;
            padding: 10px 16px 10px 56px;
            color: #666;
            transition: all 0.2s;
            cursor: pointer;
            font-size: 13px;
        }

        .sidebar-nested-submenu-item:hover {
            background-color: rgba(0,0,0,0.03);
        }

        .sidebar-nested-submenu-item.active {
            color: #4f46e5;
            font-weight: 500;
            background-color: rgba(79, 70, 229, 0.05);
        }

        .sidebar-nested-submenu-item i {
            margin-right: 12px;
            width: 16px;
            text-align: center;
            color: #6c757d;
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <!-- Notificação de atualização (modal popup) -->
    @livewire('components.update-notification')

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="flex items-center">
                <i class="far fa-clipboard"></i>
                <span>Maintenance MS</span>
            </div>
            <i class="fas fa-angle-left cursor-pointer"></i>
        </div>

        <div class="sidebar-menu-item" id="maintenanceMenu">
            <i class="fas fa-wrench"></i>
            <span>Maintenance</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto"></i>
        </div>

        <div class="sidebar-submenu" id="maintenanceSubmenu">
            <a href="{{ route('maintenance.dashboard') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('maintenance.plan') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.plan') ? 'active' : '' }}">
                <i class="far fa-calendar-alt"></i>
                <span>Maintenance Plan</span>
            </a>

            <a href="{{ route('maintenance.equipment') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.equipment') ? 'active' : '' }}">
                <i class="fas fa-wrench"></i>
                <span>Equipment Management</span>
            </a>

            <a href="{{ route('maintenance.linearea') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.linearea') ? 'active' : '' }}">
                <i class="fas fa-project-diagram"></i>
                <span>Line & Area</span>
            </a>

            <a href="{{ route('maintenance.task') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.task') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>Task Management</span>
            </a>

            <a href="{{ route('maintenance.corrective') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.corrective') ? 'active' : '' }}">
                <i class="fas fa-wrench"></i>
                <span>Corrective Maintenance</span>
            </a>

            <!-- Maintenance Settings Submenu -->
            <div class="sidebar-submenu-item" id="maintenanceSettingsMenu">
                <i class="fas fa-cogs"></i>
                <span>Maintenance Corrective Settings</span>
                <i class="fas fa-chevron-down dropdown-indicator ml-auto"></i>
            </div>

            <div class="sidebar-nested-submenu" id="maintenanceSettingsSubmenu">
                <a href="{{ route('maintenance.failure-modes') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-modes') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Failure Modes</span>
                </a>
                <a href="{{ route('maintenance.failure-mode-categories') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-mode-categories') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span>Failure Mode Categories</span>
                </a>
                <a href="{{ route('maintenance.failure-causes') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-causes') ? 'active' : '' }}">
                    <i class="fas fa-question-circle"></i>
                    <span>Failure Causes</span>
                </a>
                <a href="{{ route('maintenance.failure-cause-categories') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-cause-categories') ? 'active' : '' }}">
                    <i class="fas fa-sitemap"></i>
                    <span>Failure Cause Categories</span>
                </a>
            </div>

            <a href="{{ route('maintenance.reports') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.reports') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reports & History</span>
            </a>

            <a href="{{ route('maintenance.users') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.users') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>User Management</span>
            </a>

            <a href="{{ route('maintenance.roles') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.roles') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Role Permissions</span>
            </a>

            <a href="{{ route('maintenance.holidays') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.holidays') ? 'active' : '' }}">
                <i class="far fa-calendar"></i>
                <span>Holidays</span>
            </a>

            <!-- Replace the existing Reports & History link with a submenu -->
            <div class="sidebar-submenu-item" id="reportsHistoryMenu">
                <i class="fas fa-chart-bar"></i>
                <span>Reports & History</span>
                <i class="fas fa-chevron-down dropdown-indicator ml-auto"></i>
            </div>

            <!-- Reports & History Submenu -->
            <div class="sidebar-nested-submenu" id="reportsHistorySubmenu">
                <!-- Equipment Performance Reports -->
                <a href="{{ route('reports.equipment.availability') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.equipment.availability') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Equipment Availability</span>
                </a>
                <a href="{{ route('reports.equipment.reliability') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.equipment.reliability') ? 'active' : '' }}">
                    <i class="fas fa-heartbeat"></i>
                    <span>Equipment Reliability</span>
                </a>

                <!-- Maintenance Effectiveness Reports -->
                <a href="{{ route('reports.maintenance.types') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.types') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance Types</span>
                </a>
                <a href="{{ route('reports.maintenance.compliance') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.compliance') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Maintenance Compliance</span>
                </a>

                <!-- Cost & Resource Analysis Reports -->
                <!--<a href="{{ route('reports.cost.analysis') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.cost.analysis') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Cost Analysis</span>
                </a>-->
                <a href="{{ route('reports.resource.utilization') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.resource.utilization') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>Resource Utilization</span>
                </a>

                <!-- Failure Analysis Reports -->
                <a href="{{ route('reports.failure.analysis') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.failure.analysis') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Root Cause Analysis</span>
                </a>
                <a href="{{ route('reports.downtime.impact') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.downtime.impact') ? 'active' : '' }}">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Downtime Impact</span>
                </a>

                <!-- History Tracking Components -->
                <a href="{{ route('history.equipment.timeline') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.equipment.timeline') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    <span>Equipment Timeline</span>
                </a>
                <a href="{{ route('history.maintenance.audit') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.maintenance.audit') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Maintenance Audit Log</span>
                </a>
                <a href="{{ route('history.parts.lifecycle') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.parts.lifecycle') ? 'active' : '' }}">
                    <i class="fas fa-cogs"></i>
                    <span>Part/Supply Lifecycle</span>
                </a>
                <a href="{{ route('history.team.performance') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.team.performance') ? 'active' : '' }}">
                    <i class="fas fa-user-clock"></i>
                    <span>Team Performance</span>
                </a>
            </div>

            <a href="{{ route('maintenance.settings') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.settings') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>

        <div class="sidebar-menu-item" id="supplyChainMenu">
            <i class="fas fa-truck"></i>
            <span>Supply Chain</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto"></i>
        </div>

        <div class="sidebar-submenu" id="supplyChainSubmenu">
            <!-- Supply Chain submenu items will go here -->
        </div>

        <!--<div class="user-info">
            <div class="user-avatar">MS</div>
            <div class="user-details">
                <div class="user-name">Maintenance System</div>
                <div class="user-role">Admin</div>
            </div>
            <i class="fas fa-sign-out-alt ml-auto" style="color: #6c757d;"></i>
        </div>-->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header sticky top-0 z-10">
            <div>
                <h1 class="text-xl font-semibold">{{ $title ?? 'Maintenance Dashboard' }}</h1>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Search -->
                <div class="relative">
                    <input type="text" placeholder="Search equipment, tasks..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <!-- Verificador de atualizações -->
                @livewire('components.update-checker')

                <!-- Notification -->
                <div class="relative">
                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i class="far fa-bell text-xl"></i>
                        <span class="badge">3</span>
                    </button>
                </div>

                <!-- User Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center text-gray-700 hover:text-gray-900 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-semibold">
                            C
                        </div>
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                        <div class="py-2 px-4 border-b">
                            <div class="text-sm font-medium">carlosfox1782</div>
                            <div class="text-xs text-gray-500">Maintenance Manager</div>
                        </div>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Meu Perfil
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Configurações
                        </a>
                        <form method="POST" action="#">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <main class="p-6">
            {{ $slot }}
        </main>
    </div>

    <script>
        // Toggle for maintenance menu
        document.getElementById('maintenanceMenu').addEventListener('click', function() {
            const submenu = document.getElementById('maintenanceSubmenu');
            const indicator = this.querySelector('.dropdown-indicator');

            submenu.classList.toggle('open');
            indicator.classList.toggle('open');
        });

        // Toggle for supply chain menu
        document.getElementById('supplyChainMenu').addEventListener('click', function() {
            const submenu = document.getElementById('supplyChainSubmenu');
            const indicator = this.querySelector('.dropdown-indicator');

            submenu.classList.toggle('open');
            indicator.classList.toggle('open');
        });

        // Toggle for maintenance settings submenu
        document.getElementById('maintenanceSettingsMenu').addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que o clique propague para outros elementos
            const submenu = document.getElementById('maintenanceSettingsSubmenu');
            const indicator = this.querySelector('.dropdown-indicator');

            submenu.classList.toggle('open');
            indicator.classList.toggle('open');
        });

        // Open relevant menus by default based on current page
        document.addEventListener('DOMContentLoaded', function() {
            // Always open the main maintenance menu
            const maintenanceSubmenu = document.getElementById('maintenanceSubmenu');
            const maintenanceIndicator = document.querySelector('#maintenanceMenu .dropdown-indicator');
            maintenanceSubmenu.classList.add('open');
            maintenanceIndicator.classList.add('open');

            // Check if we're on a maintenance settings page based on route
            const currentPath = window.location.pathname;
            const currentRouteName = "{{ Route::currentRouteName() }}";

            const isMaintenanceSettingsPage =
                // Check URL path
                [
                    '/maintenance/failure-modes',
                    '/maintenance/failure-mode-categories',
                    '/maintenance/failure-causes',
                    '/maintenance/failure-cause-categories'
                ].some(path => currentPath.includes(path)) ||
                // Check route name
                [
                    'maintenance.failure-modes',
                    'maintenance.failure-mode-categories',
                    'maintenance.failure-causes',
                    'maintenance.failure-cause-categories',
                    'maintenance.corrective'
                ].includes(currentRouteName);

            // Open the maintenance settings submenu if we're on a relevant page
            if (isMaintenanceSettingsPage) {
                const settingsSubmenu = document.getElementById('maintenanceSettingsSubmenu');
                const settingsIndicator = document.querySelector('#maintenanceSettingsMenu .dropdown-indicator');
                settingsSubmenu.classList.add('open');
                settingsIndicator.classList.add('open');
            }
        });

        // Toggle for reports & history submenu
        document.getElementById('reportsHistoryMenu').addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent click from propagating to other elements
            const submenu = document.getElementById('reportsHistorySubmenu');
            const indicator = this.querySelector('.dropdown-indicator');

            submenu.classList.toggle('open');
            indicator.classList.toggle('open');
        });

        // Check if we're on a reports or history page and auto-open the menu
        const isReportsOrHistoryPage =
            window.location.pathname.includes('/reports') ||
            window.location.pathname.includes('/history');

        if (isReportsOrHistoryPage) {
            const reportsHistorySubmenu = document.getElementById('reportsHistorySubmenu');
            const reportsHistoryIndicator = document.querySelector('#reportsHistoryMenu .dropdown-indicator');
            if (reportsHistorySubmenu && reportsHistoryIndicator) {
                reportsHistorySubmenu.classList.add('open');
                reportsHistoryIndicator.classList.add('open');
            }
        }
    </script>

    @livewireScripts
    @stack('scripts')

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Debug: registrar quando o evento de notificação é recebido
            Livewire.on('notify', (params) => {
                console.log('Evento de notificação recebido:', params);

                // Verificar se o toastr está definido
                if (typeof toastr === 'undefined') {
                    console.error('Toastr não está definido!');
                    alert(params.message); // Fallback para alert se toastr não estiver disponível
                    return;
                }

                // Configurar o toastr
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000
                };

                // Exibir a notificação
                if (params.type === 'success') {
                    toastr.success(params.message, params.title || 'Success');
                } else if (params.type === 'error') {
                    toastr.error(params.message, params.title || 'Error');
                } else if (params.type === 'warning') {
                    toastr.warning(params.message, params.title || 'Warning');
                } else {
                    toastr.info(params.message, params.title || 'Information');
                }
            });

            // Monitorar envios de formulários no console
            document.querySelectorAll('form[wire\\:submit]').forEach(form => {
                console.log('Form monitorado:', form);
                form.addEventListener('submit', (e) => {
                    console.log('Formulário enviado:', e.target);
                });
            });
        });
    </script>
</body>
</html>
