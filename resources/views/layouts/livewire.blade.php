<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Dembena ERP') }} - Maintenance System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ asset('css/figtree.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS -->
    <script src="{{ asset('js/tailwind.min.js') }}"></script>

    <!-- Toastr CSS and JS -->
    <link rel="stylesheet" href="{{ asset('css/toastr.min.css') }}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>

    <!-- Custom Toastr Styling -->
    <style>
        /* Make error notifications more visible */
        .toast-error {
            background-color: #e53e3e !important; /* Bright red */
            opacity: 1 !important;
        }
        .toast-info {
            background-color: #3498db !important; /* Brighter blue */
            opacity: 1 !important;
        }
        .toast-success {
            background-color: #10b981 !important; /* Brighter green */
            opacity: 1 !important;
        }
        .toast-warning {
            background-color: #f59e0b !important; /* Brighter orange */
            opacity: 1 !important;
        }
        .toast-message {
            font-size: 14px !important;
            font-weight: 500 !important;
        }
        .toast-title {
            font-weight: 600 !important;
        }
    </style>

    <!-- Tippy.js -->
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/tippy.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/tippy-light-border.css') }}"/>

    <!-- Alpine.js - Necessário para os dropdowns customizados -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>

    <!-- FullCalendar Locale -->
    <script src="{{ asset('js/fullcalendar-pt-br.min.js') }}"></script>

    <!-- Chart.js - MOVED HERE TO AVOID DUPLICATION -->
    <script src="{{ asset('js/chart.min.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }

        .sidebar {
            width: 255px;
            height: 100vh;
            position: fixed;
            background-color: #ffffff;
            z-index: 10;
            overflow-y: auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .main-content {
            margin-left: 255px;
            background-color: #f9fafb;
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
            color: #4b5563;
            transition: all 0.2s;
            cursor: pointer;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu-item:hover {
            background-color: #f3f4f6;
            color: #4f46e5;
        }

        .sidebar-menu-item.active {
            background-color: #f3f4f6;
            color: #4f46e5;
            border-left: 3px solid #4f46e5;
        }

        .sidebar-menu-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .sidebar-submenu-item {
            display: flex;
            align-items: center;
            padding: 10px 16px 10px 46px;
            color: #4b5563;
            transition: all 0.2s;
            cursor: pointer;
            font-size: 14px;
        }

        .sidebar-submenu-item:hover {
            background-color: #f3f4f6;
            color: #4f46e5;
        }

        .sidebar-submenu-item.active {
            color: #4f46e5;
            font-weight: 500;
            background-color: #eff6ff;
            border-left: 3px solid #4f46e5;
        }

        .sidebar-submenu-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .sidebar-submenu {
            height: auto;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #fff;
        }

        .sidebar-submenu.open {
            max-height: 1500px; /* Increased to accommodate all items */
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
            background-color: #f9fafb;
        }

        .sidebar-nested-submenu.open {
            max-height: 1500px; /* Increased to accommodate all items */
        }

        .sidebar-nested-submenu-item {
            display: flex;
            align-items: center;
            padding: 8px 16px 8px 60px;
            color: #4b5563;
            transition: all 0.2s;
            cursor: pointer;
            font-size: 13px;
        }

        .sidebar-nested-submenu-item:hover {
            background-color: #f3f4f6;
            color: #4f46e5;
        }

        .sidebar-nested-submenu-item.active {
            color: #4f46e5;
            font-weight: 500;
            background-color: #eff6ff;
            border-left: 3px solid #4f46e5;
        }

        .sidebar-nested-submenu-item i {
            margin-right: 12px;
            width: 16px;
            text-align: center;
        }
        
        /* Estilos para o dropdown customizado */
        .custom-dropdown {
            position: relative;
        }
        
        .custom-dropdown-menu {
            position: absolute;
            z-index: 1000;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 0.875rem;
            color: #212529;
            text-align: left;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
        }
        
        .custom-dropdown-item {
            display: block;
            width: 100%;
            padding: 0.25rem 1.5rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
            cursor: pointer;
        }
        
        .custom-dropdown-item:hover {
            color: #16181b;
            text-decoration: none;
            background-color: #f8f9fa;
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <!-- Update notification (modal popup) -->
    @livewire('components.update-notification')

    <!-- Sidebar -->
    <div class="sidebar bg-white shadow-md">
        <div class="sidebar-header">
            <div class="flex items-center">
                <i class="fas fa-cogs text-indigo-600"></i>
                <span class="ml-2 font-semibold text-gray-800">Maintenance MS</span>
            </div>
            <i class="fas fa-angle-left cursor-pointer text-gray-500 hover:text-indigo-600 transition duration-200"></i>
        </div>

        <div class="sidebar-menu-item hover:bg-gray-50 transition duration-200" id="maintenanceMenu">
            <i class="fas fa-wrench text-indigo-500"></i>
            <span>Maintenance</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
        </div>

        <div class="sidebar-submenu" id="maintenanceSubmenu">
            <a href="{{ route('maintenance.dashboard') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.dashboard') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-th text-gray-500"></i>
                <span>Dashboard</span>
            </a>

            @can('preventive.view')
            <a href="{{ route('maintenance.plan') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.plan') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="far fa-calendar-alt text-gray-500"></i>
                <span>Maintenance Plan</span>
            </a>
            @endcan

            @can('equipment.view')
            <a href="{{ route('maintenance.equipment') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.equipment') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-wrench text-gray-500"></i>
                <span>Equipment Management</span>
            </a>

            <!-- Parts & Stock Submenu -->
            <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="partsMenu">
                <i class="fas fa-tools text-gray-500"></i>
                <span>Equipment Parts</span>
                <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
            </div>

            <div class="sidebar-nested-submenu" id="partsSubmenu">
                <a href="{{ route('equipment.parts') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('equipment.parts') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-cogs text-gray-500"></i>
                    <span>Parts List</span>
                </a>
                <a href="{{ route('stocks.stockout') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('stocks.stockout') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-arrow-circle-down text-gray-500"></i>
                    <span>Stock Out</span>
                </a>
            </div>
            @endcan

            @can('areas.view')
            <a href="{{ route('maintenance.linearea') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.linearea') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-project-diagram text-gray-500"></i>
                <span>Line & Area</span>
            </a>
            @endcan

            @can('preventive.view')
            <a href="{{ route('maintenance.task') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.task') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tasks text-gray-500"></i>
                <span>Task Management</span>
            </a>
            @endcan

            @can('corrective.view')
            <a href="{{ route('maintenance.corrective') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.corrective') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-wrench text-gray-500"></i>
                <span>Corrective Maintenance</span>
            </a>

            @can('corrective.manage')
            <!-- Maintenance Settings Submenu -->
            <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="maintenanceSettingsMenu">
                <i class="fas fa-cogs text-gray-500"></i>
                <span>Maintenance Corrective Settings</span>
                <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
            </div>

            <div class="sidebar-nested-submenu" id="maintenanceSettingsSubmenu">
                <a href="{{ route('maintenance.failure-modes') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-modes') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-exclamation-triangle text-gray-500"></i>
                    <span>Failure Modes</span>
                </a>
                <a href="{{ route('maintenance.failure-mode-categories') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-mode-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-tags text-gray-500"></i>
                    <span>Failure Mode Categories</span>
                </a>
                <a href="{{ route('maintenance.failure-causes') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-causes') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-question-circle text-gray-500"></i>
                    <span>Failure Causes</span>
                </a>
                <a href="{{ route('maintenance.failure-cause-categories') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-cause-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-sitemap text-gray-500"></i>
                    <span>Failure Cause Categories</span>
                </a>
            </div>
            @endcan
            @endcan

            @can('users.manage')
            <a href="{{ route('maintenance.users') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.users') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-users text-gray-500"></i>
                <span>User Management</span>
            </a>
            @endcan

            @can('roles.manage')
            <a href="{{ route('maintenance.roles') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.roles') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-shield-alt text-gray-500"></i>
                <span>Role Permissions</span>
            </a>
            @endcan

            @can('settings.manage')
            <a href="{{ route('maintenance.holidays') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.holidays') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="far fa-calendar text-gray-500"></i>
                <span>Holidays</span>
            </a>
            @endcan

            @can('reports.view')
            <!-- Replace the existing Reports & History link with a submenu -->
            <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="reportsHistoryMenu">
                <i class="fas fa-chart-bar text-gray-500"></i>
                <span>Reports & History</span>
                <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
            </div>

            <!-- Reports & History Submenu -->
            <div class="sidebar-nested-submenu" id="reportsHistorySubmenu">
                <!-- Equipment Performance Reports -->
                <a href="{{ route('reports.equipment.availability') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.equipment.availability') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-chart-line text-gray-500"></i>
                    <span>Equipment Availability</span>
                </a>
                <a href="{{ route('reports.equipment.reliability') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.equipment.reliability') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-heartbeat text-gray-500"></i>
                    <span>Equipment Reliability</span>
                </a>

                <!-- Maintenance Effectiveness Reports -->
                <a href="{{ route('reports.maintenance.types') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.types') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-tools text-gray-500"></i>
                    <span>Maintenance Types</span>
                </a>
                <a href="{{ route('reports.maintenance.compliance') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.compliance') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-clipboard-check text-gray-500"></i>
                    <span>Maintenance Compliance</span>
                </a>
                <a href="{{ route('reports.maintenance.plan') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.plan') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-file-alt text-gray-500"></i>
                    <span>Maintenance Plan Report</span>
                </a>

                <!-- Cost & Resource Analysis Reports -->
                <a href="{{ route('reports.resource.utilization') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.resource.utilization') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-users-cog text-gray-500"></i>
                    <span>Resource Utilization</span>
                </a>

                <!-- Failure Analysis Reports -->
                <a href="{{ route('reports.failure.analysis') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.failure.analysis') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-exclamation-triangle text-gray-500"></i>
                    <span>Root Cause Analysis</span>
                </a>
                <a href="{{ route('reports.downtime.impact') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.downtime.impact') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-hourglass-half text-gray-500"></i>
                    <span>Downtime Impact</span>
                </a>

                <!-- History Tracking Components -->
                <a href="{{ route('history.equipment.timeline') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.equipment.timeline') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-history text-gray-500"></i>
                    <span>Equipment Timeline</span>
                </a>
                <a href="{{ route('history.maintenance.audit') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.maintenance.audit') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-clipboard-list text-gray-500"></i>
                    <span>Maintenance Audit Log</span>
                </a>
                <a href="{{ route('history.parts.lifecycle') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.parts.lifecycle') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-cogs text-gray-500"></i>
                    <span>Part/Supply Lifecycle</span>
                </a>
                <a href="{{ route('history.team.performance') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.team.performance') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-user-clock text-gray-500"></i>
                    <span>Team Performance</span>
                </a>
            </div>
            @endcan

            @can('settings.manage')
            <a href="{{ route('maintenance.settings') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.settings') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-cog text-gray-500"></i>
                <span>Settings</span>
            </a>
            @endcan
        </div>

        <div class="sidebar-menu-item hover:bg-gray-50 transition duration-200" id="supplyChainMenu">
            <i class="fas fa-truck text-indigo-500"></i>
            <span>Supply Chain</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
        </div>

        <div class="sidebar-submenu" id="supplyChainSubmenu">
            <!-- Supply Chain submenu items will go here -->
        </div>

        <div class="sidebar-menu-item hover:bg-gray-50 transition duration-200" id="stocksMenu">
            <i class="fas fa-boxes text-indigo-500"></i>
            <span>Stocks</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
        </div>

        <div class="sidebar-submenu" id="stocksSubmenu">
            <a href="{{ route('equipment.parts') }}" class="sidebar-submenu-item {{ request()->routeIs('stocks.parts') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-cogs text-gray-500"></i>
                <span>Equipment Parts</span>
            </a>
            <a href="{{ route('stocks.stockout') }}" class="sidebar-submenu-item {{ request()->routeIs('stocks.stockout') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-arrow-circle-down text-gray-500"></i>
                <span>Stock Out</span>
            </a>
        </div>

        <!-- Moved user info to footer of sidebar
        <div class="absolute bottom-0 w-full border-t border-gray-100">
            <div class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 transition duration-200">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold shadow-sm">
                    {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}
                </div>
                <div class="ml-3 truncate">
                    <div class="text-sm font-medium text-gray-800">{{ auth()->user()->first_name ?? 'User' }} {{ auth()->user()->last_name ?? '' }}</div>
                    <div class="text-xs text-gray-500">{{ auth()->user()->roles->first()->name ?? 'No role' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-500 transition duration-200">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>-->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header sticky top-0 z-10 shadow-sm">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">{{ $title ?? 'Maintenance Dashboard' }}</h1>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Search -->
                <div class="relative">
                    <input type="text" placeholder="Search equipment, tasks..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm bg-gray-50 hover:bg-white transition duration-200">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <!-- Update checker -->
                @livewire('components.update-checker')

                <!-- Notification -->
                <div class="relative" x-data="{ showNotifications: false }">
                    <button @click="showNotifications = !showNotifications" class="relative p-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-full transition duration-200 focus:outline-none">
                        <i class="far fa-bell text-lg"></i>
                        @if(true) <!-- Replace with actual notification count condition -->
                        <span class="absolute top-0 right-0 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center font-bold">3</span>
                        @endif
                    </button>

                    <!-- Notifications dropdown -->
                    <div x-show="showNotifications" @click.away="showNotifications = false" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-50 border border-gray-200 overflow-hidden">
                        <div class="py-2 px-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-sm font-semibold text-gray-700">Notifications</h3>
                            <span class="text-xs text-indigo-600 hover:text-indigo-800 cursor-pointer">Mark all as read</span>
                        </div>

                        <div class="max-h-72 overflow-y-auto">
                            <!-- Notification items go here -->
                            <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition duration-150">
                                <p class="text-sm text-gray-800 font-medium">Maintenance task due</p>
                                <p class="text-xs text-gray-500 mt-1">Equipment #123 requires scheduled maintenance</p>
                                <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                            </div>
                            <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition duration-150">
                                <p class="text-sm text-gray-800 font-medium">New corrective task assigned</p>
                                <p class="text-xs text-gray-500 mt-1">You've been assigned to a new urgent task</p>
                                <p class="text-xs text-gray-400 mt-1">Yesterday</p>
                            </div>
                            <div class="px-4 py-3 hover:bg-gray-50 transition duration-150">
                                <p class="text-sm text-gray-800 font-medium">System update complete</p>
                                <p class="text-xs text-gray-500 mt-1">The system has been updated to version 2.3.0</p>
                                <p class="text-xs text-gray-400 mt-1">3 days ago</p>
                            </div>
                        </div>

                        <div class="py-2 px-3 bg-gray-50 border-t border-gray-200 text-center">
                            <a href="#" class="text-xs text-indigo-600 hover:text-indigo-800">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="relative flex items-center" x-data="{ open: false }">
                    <!-- User info visible in header -->
                    <div class="flex items-center mr-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-sm shadow-sm">
                            {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}
                        </div>
                        <div class="ml-2 hidden md:block">
                            <div class="text-sm font-medium text-gray-800">{{ auth()->user()->first_name ?? 'User' }} {{ auth()->user()->last_name ?? '' }}</div>
                            <div class="text-xs text-gray-500">{{ auth()->user()->roles->first()->name ?? 'No role' }}</div>
                        </div>
                    </div>

                    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none p-1 rounded-full hover:bg-gray-100 transition duration-200">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-40 w-56 bg-white rounded-md shadow-lg z-50 border border-gray-200 overflow-hidden">
                        <div class="py-3 border-b border-gray-100 md:hidden">
                            <div class="px-4">
                                <div class="text-sm font-medium text-gray-800">{{ auth()->user()->first_name ?? 'User' }} {{ auth()->user()->last_name ?? '' }}</div>
                                <div class="text-xs text-gray-500">{{ auth()->user()->roles->first()->name ?? 'Not defined' }}</div>
                            </div>
                        </div>
                        <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                            <i class="fas fa-user-circle mr-3 text-gray-500"></i> My Profile
                        </a>
                        <a href="{{ route('settings.system') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150">
                            <i class="fas fa-cog mr-3 text-gray-500"></i> Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                            @csrf
                            <button type="submit" class="flex items-center w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-gray-50 transition-colors duration-150">
                                <i class="fas fa-sign-out-alt mr-3 text-red-500"></i> Log Out
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
        // Função para verificar e abrir submenus com base na URL atual
        function checkAndOpenMenus() {
            const currentPath = window.location.pathname;
            const currentRouteName = "{{ Route::currentRouteName() }}";
            
            console.log('Verificando menus para:', currentPath, currentRouteName);
            
            // 1. Sempre manter o menu de manutenção aberto
            const maintenanceSubmenu = document.getElementById('maintenanceSubmenu');
            const maintenanceIndicator = document.querySelector('#maintenanceMenu .dropdown-indicator');
            if (maintenanceSubmenu && maintenanceIndicator) {
                maintenanceSubmenu.classList.add('open');
                maintenanceIndicator.classList.add('open');
            }
            
            // 2. Verificar se estamos na página de Equipment Parts
            const isEquipmentPartsPage = 
                ['/equipment/parts', '/stocks/stockout'].some(path => currentPath.includes(path)) ||
                ['equipment.parts', 'stocks.stockout'].includes(currentRouteName);
                
            if (isEquipmentPartsPage) {
                console.log('Abrindo menu de Equipment Parts');
                const partsSubmenu = document.getElementById('partsSubmenu');
                const partsIndicator = document.querySelector('#partsMenu .dropdown-indicator');
                if (partsSubmenu && partsIndicator) {
                    partsSubmenu.classList.add('open');
                    partsIndicator.classList.add('open');
                }
            }
            
            // 3. Verificar se estamos na página de configurações de manutenção
            const isMaintenanceSettingsPage =
                [
                    '/maintenance/failure-modes',
                    '/maintenance/failure-mode-categories',
                    '/maintenance/failure-causes',
                    '/maintenance/failure-cause-categories'
                ].some(path => currentPath.includes(path)) ||
                [
                    'maintenance.failure-modes',
                    'maintenance.failure-mode-categories',
                    'maintenance.failure-causes',
                    'maintenance.failure-cause-categories',
                    'maintenance.corrective'
                ].includes(currentRouteName);
                
            if (isMaintenanceSettingsPage) {
                console.log('Abrindo menu de configurações de manutenção');
                const settingsSubmenu = document.getElementById('maintenanceSettingsSubmenu');
                const settingsIndicator = document.querySelector('#maintenanceSettingsMenu .dropdown-indicator');
                if (settingsSubmenu && settingsIndicator) {
                    settingsSubmenu.classList.add('open');
                    settingsIndicator.classList.add('open');
                }
            }
            
            // 4. Verificar se estamos na página de relatórios ou histórico
            const isReportsHistoryPage = currentRouteName && (
                currentRouteName.startsWith('reports.') ||
                currentRouteName.startsWith('history.')
            );
            
            if (isReportsHistoryPage) {
                console.log('Abrindo menu de relatórios e histórico');
                const reportsHistorySubmenu = document.getElementById('reportsHistorySubmenu');
                const reportsHistoryIndicator = document.querySelector('#reportsHistoryMenu .dropdown-indicator');
                if (reportsHistorySubmenu && reportsHistoryIndicator) {
                    reportsHistorySubmenu.classList.add('open');
                    reportsHistoryIndicator.classList.add('open');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Setup menu toggle functionality com evento para manter menus abertos
            const setupMenuToggle = (menuId, submenuId) => {
                const menuElement = document.getElementById(menuId);
                if (!menuElement) return;
                
                menuElement.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const submenu = document.getElementById(submenuId);
                    const indicator = this.querySelector('.dropdown-indicator');
                    
                    if (submenu && indicator) {
                        submenu.classList.toggle('open');
                        indicator.classList.toggle('open');
                    }
                    
                    // Verificar e reabrir menus relacionados à página atual
                    setTimeout(checkAndOpenMenus, 100);
                });
            };
            
            // Configurar todos os menus
            setupMenuToggle('maintenanceMenu', 'maintenanceSubmenu');
            setupMenuToggle('supplyChainMenu', 'supplyChainSubmenu');
            setupMenuToggle('maintenanceSettingsMenu', 'maintenanceSettingsSubmenu');
            setupMenuToggle('reportsHistoryMenu', 'reportsHistorySubmenu');
            setupMenuToggle('stocksMenu', 'stocksSubmenu');
            setupMenuToggle('partsMenu', 'partsSubmenu');
            
            // Executar a verificação inicial
            checkAndOpenMenus();
        });
        
        // Manter os menus abertos durante a navegação SPA do Livewire
        document.addEventListener('livewire:navigated', function() {
            console.log('Navegação Livewire detectada, reabrindo menus');
            setTimeout(checkAndOpenMenus, 200);
        });
    </script>

    @livewireScripts
    @stack('scripts')

    <script>
        // Check for flash messages when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Check for error message in session
            @if(session('error'))
                if (typeof toastr !== 'undefined') {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 8000,
                        extendedTimeOut: 2000,
                        preventDuplicates: true,
                        newestOnTop: true
                    };
                    toastr.error("{{ session('error') }}", 'Access Denied');
                } else {
                    alert("{{ session('error') }}");
                }
            @endif
        });

        document.addEventListener('livewire:initialized', () => {
            // Listen for toast events
            Livewire.on('toast', (params) => {
                console.log('Toast event received:', params);

                // Check if toastr is defined
                if (typeof toastr === 'undefined') {
                    console.error('Toastr is not defined!');
                    alert(params.message || 'An notification occurred');
                    return;
                }

                // Configure toastr options
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: params.type === 'error' ? 8000 : 5000,
                    preventDuplicates: true,
                    newestOnTop: true,
                    showEasing: 'swing',
                    hideEasing: 'linear',
                    showMethod: 'fadeIn',
                    hideMethod: 'fadeOut'
                };

                // Display toast notification based on type
                if (params.type === 'success') {
                    toastr.success(params.message, params.title || 'Success');
                } else if (params.type === 'error') {
                    toastr.error(params.message, params.title || 'Error');
                } else if (params.type === 'warning') {
                    toastr.warning(params.message, params.title || 'Warning');
                } else if (params.type === 'info') {
                    toastr.info(params.message, params.title || 'Information');
                } else {
                    // Default to info if type is not recognized
                    toastr.info(params.message, params.title || 'Information');
                }
            });

            // Debug: log when notification event is received (legacy support)
            Livewire.on('notify', (params) => {
                console.log('Notification event received:', params);

                // Check if toastr is defined
                if (typeof toastr === 'undefined') {
                    console.error('Toastr is not defined!');
                    alert(params.message || 'An error occurred'); // Add fallback message
                    return;
                }

                // Make sure we have a message
                if (!params.message) {
                    // Default messages based on type
                    if (params.type === 'error') {
                        params.message = 'You do not have permission to perform this action.';
                    } else if (params.type === 'success') {
                        params.message = 'Operation completed successfully.';
                    } else if (params.type === 'warning') {
                        params.message = 'Warning: Please check your input.';
                    } else {
                        params.message = 'Information notice';
                    }
                }

                // Configure toastr with specific settings for error messages
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: params.type === 'error' ? 8000 : 5000, // Longer display for errors
                    preventDuplicates: true,
                    newestOnTop: true,
                    showEasing: 'swing',
                    hideEasing: 'linear',
                    showMethod: 'fadeIn',
                    hideMethod: 'fadeOut'
                };

                // Display notification
                if (params.type === 'success') {
                    toastr.success(params.message, params.title || 'Success');
                } else if (params.type === 'error') {
                    toastr.error(params.message, params.title || 'Error');
                } else if (params.type === 'warning') {
                    toastr.warning(params.message, params.title || 'Warning');
                } else {
                    // If no type is specified, default to error for permission issues
                    if (params.message.toLowerCase().includes('permission')) {
                        toastr.error(params.message, params.title || 'Permission Denied');
                    } else {
                        toastr.info(params.message, params.title || 'Information');
                    }
                }
            });

            // Monitor form submissions in console
            document.querySelectorAll('form[wire\\:submit]').forEach(form => {
                console.log('Form monitored:', form);
                form.addEventListener('submit', (e) => {
                    console.log('Form submitted:', e.target);
                });
            });
        });
    </script>
</body>
</html>
