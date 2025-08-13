<!DOCTYPE html>
{{-- Define o layout como um componente Blade com props --}}
@props(['title' => config('app.name', 'Dembena ERP')])
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $companyName = \App\Models\Setting::get('company_name', config('app.name', 'Dembena ERP'));
    @endphp
    <title>{{ $companyName }} - {{ $title ?? config('app.name', 'Dembena ERP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ asset('css/figtree.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS -->
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    
    <!-- Alpine.js é carregado automaticamente pelo Livewire, não precisamos carregá-lo manualmente -->
    
    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/css/flag-icons.min.css" integrity="sha512-uvXdJud8WaOlQFjlz9B15Yy2Au/bMNgI1pWHkhPd1WU4HJjiP5QppLrHdEjHZZX3U1S8Q5HpN5LlwnI+d5srDQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Toastr CSS and JS -->
    <link rel="stylesheet" href="{{ asset('css/toastr.min.css') }}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    
    <!-- Script para memória de paginação -->
    <script src="{{ asset('js/pagination-memory.js?v=3.0') }}"></script>
    
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

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

    {{-- Alpine.js is loaded by @livewireScripts, don't include it here --}}

    {{-- Removed FullCalendar reference that was causing 404 errors --}}
    {{-- Chart.js is loaded per component to avoid conflicts --}}

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
        
        .logo-container {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 4px;
            background-color: #f9fafb;
            border-left: 3px solid #4f46e5;
            transition: all 0.2s ease;
            max-width: 200px;
        }
        
        .logo-container:hover {
            background-color: #f3f4f6;
        }
        
        .company-name {
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        
        .logo-image {
            width: 28px;
            height: 28px;
            object-fit: contain;
            border-radius: 3px;
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

        .sidebar-submenu.active {
            max-height: 1500px; /* Increased to accommodate all items */
        }

        .sidebar-nested-submenu {
            height: auto;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #f9fafb;
        }

        .sidebar-nested-submenu.active {
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

        /* Sidebar collapse animation */
        .sidebar {
            width: 240px;
            transition: width 0.3s ease-in-out;
        }

        .sidebar.collapsed {
            width: 64px;
        }

        .sidebar.collapsed .sidebar-header span, 
        .sidebar.collapsed .sidebar-menu-item span,
        .sidebar.collapsed .dropdown-indicator {
            display: none;
        }

        /* Removi .sidebar-submenu daqui para não esconder submenus ativos */

        .sidebar.collapsed .sidebar-submenu:not(.active) {
            display: none;
        }

        /* Comportamento específico para submenus quando a sidebar está recolhida */
        .sidebar.collapsed .sidebar-submenu.active {
            position: absolute;
            left: 64px;
            background: white;
            width: 220px;
            border-radius: 0 4px 4px 0;
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .sidebar.collapsed .sidebar-nested-submenu.active {
            position: absolute;
            left: 220px;
            background: white;
            width: 220px;
            border-radius: 0 4px 4px 0;
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
            padding: 16px 0;
        }

        .main-content {
            transition: margin-left 0.3s ease-in-out;
            margin-left: 240px;
        }

        .main-content.expanded {
            margin-left: 64px;
        }

        /* Ensure icons stay centered when collapsed */
        .sidebar.collapsed .sidebar-menu-item {
            justify-content: center;
            padding: 12px 0;
        }

        .sidebar.collapsed .sidebar-menu-item i:first-child {
            margin-right: 0;
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
                @php
                    $logoPath = \App\Models\Setting::get('company_logo');
                    $companyName = \App\Models\Setting::get('company_name', config('app.name', 'Dembena ERP'));
                @endphp
                
                <div class="logo-container">
                    @if($logoPath)
                        <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $companyName }} Logo" class="logo-image">
                    @else
                        <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="logo-image">
                    @endif
                    <span class="ml-3 company-name">{{ $companyName }}</span>
                </div>
            </div>
            <button id="sidebar-toggle" class="focus:outline-none">
                <i class="fas fa-chevron-left cursor-pointer text-gray-500 hover:text-indigo-600 transition duration-200"></i>
            </button>
        </div>

        <!-- Maintenance Menu - Add permission check -->
        @canany(['equipment.view', 'preventive.view', 'corrective.view', 'reports.view', 'parts.view', 'stock.manage', 'settings.manage'])
        <div class="sidebar-menu-item hover:bg-gray-50 transition duration-200" id="maintenanceMenu">
            <i class="fas fa-wrench text-indigo-500"></i>
            <span>{{ trans('messages.maintenance') }}</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
        </div>

        <div class="sidebar-submenu" id="maintenanceSubmenu">
            @canany(['equipment.view', 'preventive.view', 'corrective.view', 'reports.view'])
            <a href="{{ route('maintenance.dashboard') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.dashboard') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-th text-gray-500"></i>
                <span>{{ trans('messages.dashboard') }}</span>
            </a>
            @endcanany

            @can('preventive.view')
                <a href="{{ route('maintenance.plan') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.plan') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="far fa-calendar-alt text-gray-500"></i>
                    <span>{{ trans('messages.maintenance_plan') }}</span>
                </a>
            @endcan

            @can('equipment.view')
                <a href="{{ route('maintenance.equipment') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.equipment') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-wrench text-gray-500"></i>
                    <span>{{ trans('messages.equipment_management') }}</span>
                </a>

                <!-- Parts & Stock Submenu -->
                <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="partsMenu">
                    <i class="fas fa-tools text-gray-500"></i>
                    <span class="font-semibold">{{ trans('messages.equipment_parts') }}</span>
                    <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400 {{ request()->routeIs('equipment.parts') || request()->routeIs('stocks.stockin') || request()->routeIs('stocks.stockout') || request()->routeIs('stocks.history') || request()->routeIs('stocks.part-requests') ? 'active' : '' }}"></i>
                </div>

                <div class="sidebar-nested-submenu {{ request()->routeIs('equipment.parts') || request()->routeIs('equipment.types') || request()->routeIs('stocks.stockin') || request()->routeIs('stocks.stockout') || request()->routeIs('stocks.history') || request()->routeIs('stocks.part-requests') ? 'active' : '' }}" id="partsSubmenu">
                    <a href="{{ route('equipment.parts') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('equipment.parts') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-cogs text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.parts_list') }}</span>
                    </a>
                    <a href="{{ route('equipment.types') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('equipment.types') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-tags text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.equipment_types') }}</span>
                    </a>
                    <a href="{{ route('stocks.part-requests') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('stocks.part-requests') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-clipboard-list text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.request_part') }}</span>
                    </a>
                    <a href="{{ route('stocks.stockin') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('stocks.stockin') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-arrow-circle-up text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.stock_in') }}</span>
                    </a>
                    <a href="{{ route('stocks.stockout') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('stocks.stockout') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-arrow-circle-down text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.stock_out') }}</span>
                    </a>
                    <a href="{{ route('stocks.history') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('stocks.history') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-history text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.stock_history') }}</span>
                    </a>
                </div>
            @endcan

            @can('areas.view')
                <a href="{{ route('maintenance.linearea') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.linearea') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-project-diagram text-gray-500"></i>
                    <span>{{ trans('messages.line_and_area') }}</span>
                </a>
            @endcan

            @can('preventive.view')
                <a href="{{ route('maintenance.task') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.task') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-tasks text-gray-500"></i>
                    <span>{{ trans('messages.task_management') }}</span>
                </a>
            @endcan

            @can('corrective.view')
                <a href="{{ route('maintenance.corrective') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.corrective') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-wrench text-gray-500"></i>
                    <span>{{ trans('messages.corrective_maintenance') }}</span>
                </a>

                @can('corrective.manage')
                    <!-- Maintenance Settings Submenu -->
                    <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="maintenanceSettingsMenu">
                        <i class="fas fa-cogs text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.maintenance_corrective_settings') }}</span>
                        <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400 {{ request()->routeIs('maintenance.failure-modes') || request()->routeIs('maintenance.failure-mode-categories') || request()->routeIs('maintenance.failure-causes') || request()->routeIs('maintenance.failure-cause-categories') ? 'active' : '' }}"></i>
                    </div>

                    <div class="sidebar-nested-submenu {{ request()->routeIs('maintenance.failure-modes') || request()->routeIs('maintenance.failure-mode-categories') || request()->routeIs('maintenance.failure-causes') || request()->routeIs('maintenance.failure-cause-categories') ? 'active' : '' }}" id="maintenanceSettingsSubmenu">
                        <a href="{{ route('maintenance.failure-modes') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-modes') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-exclamation-triangle text-gray-500"></i>
                            <span class="font-semibold">{{ trans('messages.failure_modes') }}</span>
                        </a>
                        <a href="{{ route('maintenance.failure-mode-categories') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-mode-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-tags text-gray-500"></i>
                            <span class="font-semibold">{{ trans('messages.failure_mode_categories') }}</span>
                        </a>
                        <a href="{{ route('maintenance.failure-causes') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-causes') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-question-circle text-gray-500"></i>
                            <span class="font-semibold">{{ trans('messages.failure_causes') }}</span>
                        </a>
                        <a href="{{ route('maintenance.failure-cause-categories') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.failure-cause-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-sitemap text-gray-500"></i>
                            <span class="font-semibold">{{ trans('messages.failure_cause_categories') }}</span>
                        </a>
                    </div>
                @endcan
            @endcan

            @can('users.manage')
                <!-- User Management Submenu -->
                <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="userManagementMenu">
                    <i class="fas fa-users text-gray-500"></i>
                    <span class="font-semibold">{{ trans('messages.user_management') }}</span>
                    <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400 {{ request()->routeIs('maintenance.users') || request()->routeIs('maintenance.technicians') ? 'active' : '' }}"></i>
                </div>

                <div class="sidebar-nested-submenu {{ request()->routeIs('maintenance.users') || request()->routeIs('maintenance.technicians') ? 'active' : '' }}" id="userManagementSubmenu">
                    <a href="{{ route('maintenance.users') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.users') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-user-shield text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.users') }}</span>
                    </a>
                    @can('technicians.view')
                        <a href="{{ route('maintenance.technicians') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.technicians') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-user-cog text-blue-500"></i>
                            <span class="font-semibold">{{ trans('messages.technicians') }}</span>
                        </a>
                    @endcan
                </div>
            @endcan

            @can('roles.manage')
                <a href="{{ route('maintenance.roles') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.roles') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-shield-alt text-gray-500"></i>
                    <span>{{ trans('messages.role_permissions') }}</span>
                </a>
            @endcan

            @can('settings.manage')
                <a href="{{ route('maintenance.holidays') }}" class="sidebar-submenu-item {{ request()->routeIs('maintenance.holidays') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="far fa-calendar text-gray-500"></i>
                    <span>{{ trans('messages.holidays') }}</span>
                </a>
            @endcan

            @can('reports.view')
                <!-- Replace the existing Reports & History link with a submenu -->
                <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="reportsHistoryMenu">
                    <i class="fas fa-chart-bar text-gray-500"></i>
                    <span class="font-semibold">{{ trans('messages.reports_and_history') }}</span>
                    <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400 {{ request()->routeIs('reports.equipment.*') || request()->routeIs('reports.maintenance.*') || request()->routeIs('reports.cost.*') || request()->routeIs('reports.downtime.*') || request()->routeIs('reports.failure.*') || request()->routeIs('reports.resource.*') || request()->routeIs('history.*') ? 'active' : '' }}"></i>
                </div>

                <!-- Reports & History Submenu -->
                <div class="sidebar-nested-submenu {{ request()->routeIs('reports.equipment.*') || request()->routeIs('reports.maintenance.*') || request()->routeIs('reports.cost.*') || request()->routeIs('reports.downtime.*') || request()->routeIs('reports.failure.*') || request()->routeIs('reports.resource.*') || request()->routeIs('history.*') ? 'active' : '' }}" id="reportsHistorySubmenu">
                    <!-- Equipment Performance Reports -->
                    <a href="{{ route('reports.equipment.availability') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.equipment.availability') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-chart-line text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.equipment_availability') }}</span>
                    </a>
                    <a href="{{ route('reports.equipment.reliability') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.equipment.reliability') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-heartbeat text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.equipment_reliability') }}</span>
                    </a>

                    <!-- Maintenance Effectiveness Reports -->
                    <a href="{{ route('reports.maintenance.types') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.types') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-tools text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.maintenance_types') }}</span>
                    </a>
                    <a href="{{ route('reports.maintenance.compliance') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.compliance') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-clipboard-check text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.maintenance_compliance') }}</span>
                    </a>
                    <a href="{{ route('reports.maintenance.plan') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.maintenance.plan') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-file-alt text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.maintenance_plan_report') }}</span>
                    </a>

                    <!-- Cost & Resource Analysis Reports -->
                    <a href="{{ route('reports.resource.utilization') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.resource.utilization') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-users-cog text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.resource_utilization') }}</span>
                    </a>

                    <!-- Failure Analysis Reports -->
                    <a href="{{ route('reports.failure.analysis') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.failure.analysis') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-exclamation-triangle text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.root_cause_analysis') }}</span>
                    </a>
                    <a href="{{ route('reports.downtime.impact') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('reports.downtime.impact') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-hourglass-half text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.downtime_impact') }}</span>
                    </a>

                    <!-- History Tracking Components -->
                    <a href="{{ route('history.equipment.timeline') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.equipment.timeline') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-history text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.equipment_timeline') }}</span>
                    </a>
                    <a href="{{ route('history.maintenance.audit') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.maintenance.audit') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-clipboard-list text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.maintenance_audit_log') }}</span>
                    </a>
                    <a href="{{ route('history.parts.lifecycle') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.parts.lifecycle') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-cogs text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.part_supply_lifecycle') }}</span>
                    </a>
                    <a href="{{ route('history.team.performance') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('history.team.performance') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-user-clock text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.team_performance') }}</span>
                    </a>
                </div>
            @endcan

            @can('settings.manage')
                <!-- Settings Submenu -->
                <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="settingsMenu">
                    <i class="fas fa-cog text-gray-500"></i>
                    <span class="font-semibold">{{ trans('messages.settings') }}</span>
                    <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400 {{ request()->routeIs('settings.*') || request()->routeIs('maintenance.settings') ? 'active' : '' }}"></i>
                </div>
                
                <div class="sidebar-nested-submenu {{ request()->routeIs('settings.*') || request()->routeIs('maintenance.settings') ? 'active' : '' }}" id="settingsSubmenu">
                    <a href="{{ route('maintenance.settings') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('maintenance.settings') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-sliders-h text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.system_settings') }}</span>
                    </a>
                    <a href="{{ route('settings.unit-types') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('settings.unit-types') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-ruler-combined text-gray-500"></i>
                        <span class="font-semibold">{{ trans('messages.unit_types') }}</span>
                    </a>
                </div>
            @endcan
        </div>
        @endcanany

        <!-- MRP Menu - Add permission check -->
        @canany(['mrp.dashboard', 'mrp.demand_forecasting.view', 'mrp.bom_management.view', 'mrp.inventory_levels.view',
                'mrp.production_scheduling.view', 'mrp.production_orders.view', 'mrp.purchase_planning.view',
                'mrp.capacity_planning.view', 'mrp.financial_reporting.view'])
        <div class="sidebar-menu-item hover:bg-gray-50 transition duration-200" id="mrpMenu">
            <i class="fas fa-industry text-indigo-500"></i>
            <span>{{ trans('messages.mrp') }}</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
        </div>

        <div class="sidebar-submenu" id="mrpSubmenu">
            @can('mrp.dashboard')
            <a href="{{ route('mrp.dashboard') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.dashboard') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tachometer-alt text-gray-500"></i>
                <span>{{ trans('messages.dashboard') }}</span>
            </a>
            @endcan

            {{-- @can('mrp.demand_forecasting.view') --}}
            {{-- <a href="{{ route('mrp.demand-forecasting') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.demand-forecasting') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-chart-line text-gray-500"></i>
                <span>{{ trans('messages.demand_forecasting') }}</span>
            </a> --}}
            {{-- @endcan --}}

            @can('mrp.bom_management.view')
            <a href="{{ route('mrp.bom-management') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.bom-management') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-sitemap text-gray-500"></i>
                <span>{{ trans('messages.bom_management') }}</span>
            </a>
            @endcan

            @can('mrp.inventory_levels.view')
            <a href="{{ route('mrp.inventory-levels') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.inventory-levels') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-boxes text-gray-500"></i>
                <span>{{ trans('messages.inventory_levels') }}</span>
            </a>
            @endcan

            @can('mrp.production_scheduling.view')
            <a href="{{ route('mrp.production-scheduling') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.production-scheduling') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-calendar-alt text-gray-500"></i>
                <span>{{ trans('messages.production_scheduling') }}</span>
            </a>
            @endcan

            @can('mrp.production_orders.view')
            <a href="{{ route('mrp.production-orders') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.production-orders') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-clipboard-list text-gray-500"></i>
                <span>{{ trans('messages.production_orders') }}</span>
            </a>
            @endcan

            {{-- @can('mrp.purchase_planning.view') --}}
            {{-- <a href="{{ route('mrp.purchase-planning') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.purchase-planning') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-shopping-cart text-gray-500"></i>
                <span>{{ trans('messages.purchase_planning') }}</span>
            </a> --}}
            {{-- @endcan --}}

            @can('mrp.capacity_planning.view')
            <a href="{{ route('mrp.capacity-planning') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.capacity-planning') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-cogs text-gray-500"></i>
                <span>{{ trans('messages.capacity_planning') }}</span>
            </a>
            @endcan

            {{-- @can('mrp.resources.view') --}}
            {{-- <a href="{{ route('mrp.resources-management') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.resources-management') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tools text-gray-500"></i>
                <span>{{ trans('messages.resources_management') }}</span>
            </a> --}}
            {{-- @endcan --}}

            @can('mrp.shifts.view')
            <a href="{{ route('mrp.shifts') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.shifts') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-clock text-gray-500"></i>
                <span>{{ trans('messages.shifts') }}</span>
            </a>
            @endcan

            @can('mrp.lines.view')
            <a href="{{ route('mrp.lines') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.lines') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-project-diagram text-gray-500"></i>
                <span>{{ trans('messages.lines') }}</span>
            </a>
            @endcan

            @can('mrp.financial_reporting.view')
            <a href="{{ route('mrp.financial-reporting') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.financial-reporting') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-chart-pie text-gray-500"></i>
                <span>{{ trans('messages.financial_reporting') }}</span>
            </a>
            @endcan
            
            @can('mrp.failure_analysis.view')
            <a href="{{ route('mrp.failure-categories') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.failure-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-bug text-gray-500"></i>
                <span>{{ trans('messages.failure_categories') }}</span>
            </a>
            <a href="{{ route('mrp.failure-root-causes') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.failure-root-causes') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-search-minus text-gray-500"></i>
                <span>{{ trans('messages.failure_root_causes') }}</span>
            </a>
            @endcan
            
            @can('mrp.responsibles.view')
            <a href="{{ route('mrp.responsibles') }}" class="sidebar-submenu-item {{ request()->routeIs('mrp.responsibles') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-user-tie text-gray-500"></i>
                <span>{{ trans('messages.responsibles_management') }}</span>
            </a>
            @endcan
            
            @canany(['mrp.reports.raw_material'])
            <!-- Reports Dropdown Menu -->
            <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="mrpReportsMenu">
                <i class="fas fa-chart-line text-gray-500"></i>
                <span class="font-semibold">{{ trans('messages.reports') }}</span>
                <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
            </div>
            
            <!-- Reports Submenu -->
            <div class="sidebar-nested-submenu" id="mrpReportsSubmenu">
                @can('mrp.reports.raw_material')
                <a href="{{ route('mrp.raw-material-report') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('mrp.raw-material-report') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-boxes text-gray-500"></i>
                    <span>{{ trans('messages.raw_material_report') }}</span>
                </a>
                @endcan
                
                <!-- Add more report links here as needed -->
                <!--
                @can('mrp.reports.another_report')
                <a href="#" class="sidebar-nested-submenu-item hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-chart-pie text-gray-500"></i>
                    <span>Another Report</span>
                </a>
                @endcan
                -->
            </div>
            @endcanany
        </div>
        @endcanany

        <!-- Supply Chain Menu - Add permission check -->
        @canany(['supplychain.dashboard', 'supplychain.purchase_orders.view', 'supplychain.goods_receipts.view', 
                'supplychain.products.view', 'supplychain.suppliers.view', 'supplychain.inventory.view',
                'supplychain.warehouse_transfers.view'])
        <div class="sidebar-menu-item hover:bg-gray-50 transition duration-200" id="supplyChainMenu">
            <i class="fas fa-truck text-indigo-500"></i>
            <span>{{ trans('messages.supply_chain') }}</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
        </div>

        <div class="sidebar-submenu" id="supplyChainSubmenu">
            @can('supplychain.dashboard')
            <a href="{{ route('supply-chain.dashboard') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.dashboard') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tachometer-alt text-gray-500"></i>
                <span>{{ trans('messages.dashboard') }}</span>
            </a>
            @endcan

            @can('supplychain.suppliers.view')
            <a href="{{ route('supply-chain.suppliers') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.suppliers') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-handshake text-gray-500"></i>
                <span>{{ trans('messages.suppliers') }}</span>
            </a>
            @endcan

            @can('supplychain.suppliers.manage')
            <a href="{{ route('supply-chain.supplier-categories') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.supplier-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tags text-gray-500"></i>
                <span>{{ trans('messages.supplier_categories') }}</span>
            </a>
            @endcan

            @can('supplychain.products.view')
            <a href="{{ route('supply-chain.product-categories') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.product-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tags text-gray-500"></i>
                <span>{{ trans('messages.product_categories') }}</span>
            </a>

            <a href="{{ route('supply-chain.products') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.products') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-box text-gray-500"></i>
                <span>{{ trans('messages.products') }}</span>
            </a>
            @endcan

            @can('supplychain.inventory.view')
            <a href="{{ route('supply-chain.inventory-locations') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.inventory-locations') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-warehouse text-gray-500"></i>
                <span>{{ trans('messages.inventory_locations') }}</span>
            </a>

            <a href="{{ route('supply-chain.inventory') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.inventory') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-cubes text-gray-500"></i>
                <span>{{ trans('messages.inventory') }}</span>
            </a>

            @can('supplychain.warehouse_transfers.view')
            @php
                $pendingTransfers = \App\Models\SupplyChain\WarehouseTransferRequest::countPendingApproval();
            @endphp
            <a href="{{ route('supply-chain.warehouse-transfers') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.warehouse-transfers*') ? 'active' : '' }} hover:bg-gray-50 transition duration-200 relative">
                <div class="flex items-center w-full justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exchange-alt text-gray-500"></i>
                        <span class="mx-2">{{ trans('messages.warehouse_transfers') }}</span>
                    </div>
                    @if($pendingTransfers > 0)
                    <span class="inline-block min-w-[22px] h-5 px-1.5 text-xs font-bold text-white bg-gray-800 rounded-full shadow-sm text-center leading-5">
                        {{ $pendingTransfers }}
                    </span>
                    @endif
                </div>
            </a>
            @endcan
            @endcan
            @can('supplychain.reports.view')
            <!-- Reports Submenu -->
            <div class="sidebar-submenu-item hover:bg-gray-50 transition duration-200" id="supplyChainReportsMenu">
                <i class="fas fa-chart-bar text-gray-500"></i>
                <span class="font-semibold">{{ trans('messages.reports') }}</span>
                <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400 {{ request()->routeIs('supply-chain.reports.*') ? 'active' : '' }}"></i>
            </div>
            
            <div class="sidebar-nested-submenu {{ request()->routeIs('supply-chain.reports.*') ? 'active' : '' }}" id="supplyChainReportsSubmenu">
                <a href="{{ route('supply-chain.reports.inventory-management') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('supply-chain.reports.inventory-management') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-boxes mr-2"></i>
                    <span>{{ trans('messages.inventory_management_report') }}</span>
                </a>
                <a href="{{ route('supply-chain.reports.stock-movement') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('supply-chain.reports.stock-movement') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    <span>{{ trans('messages.stock_movement_report') }}</span>
                </a>
                <a href="{{ route('supply-chain.reports.raw-material') }}" class="sidebar-nested-submenu-item {{ request()->routeIs('supply-chain.reports.raw-material') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-cubes mr-2"></i>
                    <span>{{ trans('messages.raw_material_report') }}</span>
                </a>
            </div>
            @endcan

            @can('supplychain.purchase_orders.view')
            <a href="{{ route('supply-chain.purchase-orders') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.purchase-orders') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-file-invoice text-gray-500"></i>
                <span>{{ trans('messages.purchase_orders') }}</span>
            </a>
            @endcan
            
            

            @can('supplychain.goods_receipts.view')
            <a href="{{ route('supply-chain.goods-receipts') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.goods-receipts') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-dolly-flatbed text-gray-500"></i>
                <span>{{ trans('messages.goods_receipts') }}</span>
            </a>
            @endcan
            
            @can('supplychain.forms.manage')
            <a href="{{ route('supply-chain.custom-forms') }}" class="sidebar-submenu-item {{ request()->routeIs('supply-chain.custom-forms') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-file-alt text-gray-500"></i>
                <span>{{ trans('messages.custom_forms') }}</span>
            </a>
            @endcan
        </div>
        @endcanany


        <!-- Human Resources - Add permission check -->
        @canany(['hr.dashboard', 'hr.employees.view', 'hr.departments.view', 'hr.positions.view',
                'hr.attendance.view', 'hr.leave.view', 'hr.performance.view'])
        <div class="sidebar-menu-item hover:bg-gray-50 transition duration-200" id="hrMenu">
            <i class="fas fa-users text-indigo-500"></i>
            <span>{{ trans('messages.human_resources') }}</span>
            <i class="fas fa-chevron-down dropdown-indicator ml-auto text-gray-400"></i>
        </div>

        <div class="sidebar-submenu" id="hrSubmenu">
            @can('hr.dashboard')
            <a href="{{ route('hr.dashboard') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.dashboard') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tachometer-alt text-gray-500"></i>
                <span>{{ trans('messages.dashboard') }}</span>
            </a>
            @endcan

            @can('hr.employees.view')
            <a href="{{ route('hr.employees') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.employees') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-id-card text-gray-500"></i>
                <span>{{ trans('messages.employee_management') }}</span>
            </a>
            
            <a href="{{ route('hr.salary-advances') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.salary-advances') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-hand-holding-usd text-gray-500"></i>
                <span>{{ trans('messages.salary_advances') }}</span>
            </a>
            
            <a href="{{ route('hr.salary-discounts') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.salary-discounts') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-minus-circle text-gray-500"></i>
                <span>{{ trans('messages.salary_discounts') }}</span>
            </a>
            
            <a href="{{ route('hr.overtime-records') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.overtime-records') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-clock text-gray-500"></i>
                <span>{{ trans('messages.overtime_records') }}</span>
            </a>
            @endcan
            
            @can('hr.departments.view')
            <a href="{{ route('hr.departments') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.departments') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-building text-gray-500"></i>
                <span>{{ trans('messages.departments') }}</span>
            </a>
            @endcan
            
            @can('hr.positions.view')
            <a href="{{ route('hr.job-categories') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.job-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tags text-gray-500"></i>
                <span>{{ trans('messages.job_categories') }}</span>
            </a>
            
            <a href="{{ route('hr.job-positions') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.job-positions') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-briefcase text-gray-500"></i>
                <span>{{ trans('messages.job_positions') }}</span>
            </a>
            @endcan
            
            @can('hr.attendance.view')
            <a href="{{ route('hr.attendance') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.attendance') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-user-clock text-gray-500"></i>
                <span>{{ trans('messages.attendance_absence') }}</span>
            </a>
            @endcan
            
            @can('hr.leave.view')
            <a href="{{ route('hr.leave') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.leave') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-calendar-alt text-gray-500"></i>
                <span>{{ trans('messages.leave_management') }}</span>
            </a>
            
            <a href="{{ route('hr.leave-types') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.leave-types') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tags text-gray-500"></i>
                <span>{{ trans('messages.leave_types') }}</span>
            </a>
            @endcan
            
            @can('hr.leave.view')
            <a href="{{ route('hr.payroll') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.payroll') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-money-check-alt text-gray-500"></i>
                <span>{{ trans('messages.payroll_processing') }}</span>
            </a>
            
            <a href="{{ route('hr.payroll-periods') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.payroll-periods') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-calendar-week text-gray-500"></i>
                <span>{{ trans('messages.payroll_periods') }}</span>
            </a>
            <!--
            <a href="{{ route('hr.payroll-items') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.payroll-items') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-list-alt text-gray-500"></i>
                <span>{{ trans('messages.payroll_items') }}</span>
            </a>
            -->
            @endcan
            
            @can('hr.attendance.view')
            <a href="{{ route('hr.shifts') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.shifts') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-business-time text-gray-500"></i>
                <span>{{ trans('messages.shift_management') }}</span>
            </a>
            @endcan
            
            @can('hr.equipment.view')
            <a href="{{ route('hr.work-equipment-categories') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.work-equipment-categories') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-tags text-gray-500"></i>
                <span>{{ trans('messages.work_equipment_categories') }}</span>
            </a>
            @endcan
            
            @can('hr.employees.view')
            <a href="{{ route('hr.equipment') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.equipment') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-laptop text-gray-500"></i>
                <span>{{ trans('messages.work_equipment_control') }}</span>
            </a>
            @endcan
            
            @can('hr.settings.view')
            <a href="{{ route('hr.settings') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.settings') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-cogs text-gray-500"></i>
                <span>{{ trans('messages.hr_settings') }}</span>
            </a>
            @endcan
            
            @can('hr.dashboard')
            <a href="{{ route('hr.reports') }}" class="sidebar-submenu-item {{ request()->routeIs('hr.reports') ? 'active' : '' }} hover:bg-gray-50 transition duration-200">
                <i class="fas fa-chart-bar text-gray-500"></i>
                <span>{{ trans('messages.reports_and_dashboard') }}</span>
            </a>
            @endcan
        </div>
        @endcanany
      
      

        <!-- Moved user info to footer of sidebar
        <div class="absolute bottom-0 w-full border-t border-gray-100">
            <div class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 transition duration-200">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-sm">
                    {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}
                </div>
                <div class="ml-3 truncate">
                    <div class="text-sm font-medium text-gray-800">{{ auth()->user()->full_name ?? 'User' }}</div>
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
        <div class="header sticky top-0 z-10 shadow-sm bg-white">
            <div class="flex justify-between items-center px-4 py-2">
                <!-- Left: Title -->
                <h1 class="text-xl font-semibold text-gray-800 truncate">{{ $title ?? trans('messages.maintenance') . ' ' . trans('messages.dashboard') }}</h1>
                
                <!-- Center: Real-time Clock -->
                <div class="relative flex-grow max-w-lg mx-4 flex justify-center items-center">
                    <div id="current-time" class="text-xl font-semibold text-indigo-600 flex items-center bg-indigo-50 px-4 py-2 rounded-lg">
                        <i class="fas fa-clock mr-2 text-indigo-500"></i>
                        <span class="clock-time">00:00:00</span>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Verificar button -->
                    <button wire:click="$emit('verifyNotifications')" class="flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition duration-200 text-sm hover:scale-105">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ trans('messages.verify') }}</span>
                    </button>

                    <!-- Language Selector -->
                    <div class="relative" x-data="{ showLanguageMenu: false }">
                        <button @click="showLanguageMenu = !showLanguageMenu" class="flex items-center px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg transition duration-200 text-sm">
                            <i class="fas fa-globe mr-2"></i>
                            <span>{{ app()->getLocale() == 'pt-BR' ? trans('messages.portuguese') : trans('messages.english') }}</span>
                            <i class="fas fa-chevron-down ml-2 text-gray-400 text-xs"></i>
                        </button>
                        
                        <!-- Language Menu -->
                        <div x-show="showLanguageMenu" @click.away="showLanguageMenu = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200 overflow-hidden">
                            <div class="py-1">
                                <a href="{{ route('change.locale', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() == 'en' ? 'bg-gray-50 font-semibold' : '' }}">
                                    <span class="flag-icon flag-icon-us mr-2"></span> {{ trans('messages.english') }}
                                </a>
                                <a href="{{ route('change.locale', 'pt-BR') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() == 'pt-BR' ? 'bg-gray-50 font-semibold' : '' }}">
                                    <span class="flag-icon flag-icon-br mr-2"></span> {{ trans('messages.portuguese') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Notification -->
                    <div class="relative" x-data="{ showNotifications: false }">
                        <button @click="showNotifications = !showNotifications" class="relative p-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-full transition duration-200 focus:outline-none">
                            <i class="far fa-bell text-lg"></i>
                            @if(true) <!-- Replace with actual notification count condition -->
                            <span class="absolute top-0 right-0 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center font-bold">1</span>
                            @endif
                        </button>

                        <!-- Notifications dropdown -->
                        <div x-show="showNotifications" @click.away="showNotifications = false" class="absolute right-0 top-12 w-80 bg-white rounded-md shadow-lg z-50 border border-gray-200 overflow-hidden">
                            <div class="py-2 px-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-700">{{ trans('messages.notifications') }}</h3>
                                <span class="text-xs text-indigo-600 hover:text-indigo-800 cursor-pointer">{{ trans('messages.mark_all_as_read') }}</span>
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
                        <div class="flex items-center ml-2 cursor-pointer hover:bg-gray-100 rounded-full px-2 py-1 transition-colors duration-150" @click="open = !open">
                            <div class="text-right mr-2">
                                <div class="text-sm font-medium text-gray-800">{{ strtoupper(auth()->user()->full_name ?? 'User') }}</div>
                                <div class="text-xs text-gray-500">{{ strtolower(auth()->user()->roles->first()->name ?? 'no role') }}</div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold text-sm">
                                {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div x-show="open" @click.away="open = false" class="absolute right-0 top-12 w-56 bg-white rounded-md shadow-lg z-50 border border-gray-200 overflow-hidden">
                            <div class="py-3 border-b border-gray-100 md:hidden">
                                <div class="px-4">
                                    <div class="text-sm font-medium text-gray-800">{{ auth()->user()->full_name ?? 'User' }}</div>
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
        </div>

        <!-- Content -->
        <main class="p-6">
            {{ $slot ?? '' }}
        </main>
    </div>

    <script>
        // Funções definidas fora do DOMContentLoaded para serem acessíveis globalmente
        function saveMenuState() {
            const activeMenus = Array.from(document.querySelectorAll('.sidebar-submenu.active'))
                .map(element => element.id);
            localStorage.setItem('activeMenus', JSON.stringify(activeMenus));
        }
        
        function restoreMenuState() {
            const activeMenus = JSON.parse(localStorage.getItem('activeMenus') || '[]');
            activeMenus.forEach(menuId => {
                const submenu = document.getElementById(menuId);
                if (submenu) {
                    submenu.classList.add('active');
                    
                    // Ativar o indicador correspondente
                    const parentMenuItem = document.getElementById(menuId.replace('Submenu', 'Menu'));
                    if (parentMenuItem) {
                        const indicator = parentMenuItem.querySelector('.dropdown-indicator');
                        if (indicator) {
                            indicator.classList.add('active');
                        }
                    }
                }
            });
        }
        
        function setupSidebar() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleIcon = sidebarToggle ? sidebarToggle.querySelector('i') : null;
            const menuItems = document.querySelectorAll('.sidebar-menu-item');
            
            // Check if sidebar state is stored in localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            // Apply initial state
            if (sidebarCollapsed && sidebar && mainContent && toggleIcon) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            }
            
            // Restaurar estados dos menus
            restoreMenuState();
            
            // Handle hover effects for collapsed sidebar
            menuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        const subMenu = document.getElementById(this.id.replace('Menu', 'Submenu'));
                        if (subMenu) {
                            // Open this submenu
                            subMenu.classList.add('active');
                            
                            // Position the submenu correctly
                            const rect = this.getBoundingClientRect();
                            subMenu.style.top = `${rect.top}px`;
                        }
                    }
                });
            });
            
            // Handle mouseout for entire sidebar to close submenus when mouse leaves
            if (sidebar) {
                sidebar.addEventListener('mouseleave', function() {
                    if (sidebar.classList.contains('collapsed')) {
                        // Fechar apenas menus que não estão salvos no estado
                        const activeMenus = JSON.parse(localStorage.getItem('activeMenus') || '[]');
                        document.querySelectorAll('.sidebar-submenu, .sidebar-nested-submenu').forEach(sm => {
                            if (!activeMenus.includes(sm.id)) {
                                sm.classList.remove('active');
                            }
                        });
                    }
                });
            }
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    
                    // Toggle icon direction
                    if (sidebar.classList.contains('collapsed')) {
                        toggleIcon.classList.remove('fa-chevron-left');
                        toggleIcon.classList.add('fa-chevron-right');
                        localStorage.setItem('sidebarCollapsed', 'true');
                    } else {
                        toggleIcon.classList.remove('fa-chevron-right');
                        toggleIcon.classList.add('fa-chevron-left');
                        localStorage.setItem('sidebarCollapsed', 'false');
                    }
                });
            }
        }
        
        // Setup menu toggle functionality com evento para manter menus abertos
        function setupMenuToggle(menuId, submenuId) {
            const menuElement = document.getElementById(menuId);
            const submenu = document.getElementById(submenuId);
            if (!menuElement || !submenu) return;
            
            menuElement.addEventListener('click', function(e) {
                e.preventDefault();
                // Toggle current submenu
                submenu.classList.toggle('active');
                
                // Toggle dropdown indicator
                const indicator = this.querySelector('.dropdown-indicator');
                if (indicator) {
                    indicator.classList.toggle('active', submenu.classList.contains('active'));
                }
                
                // Save state to localStorage
                saveMenuState();
            });
        }
        
        // Inicialização inicial
        document.addEventListener('DOMContentLoaded', function() {
            setupSidebar();
            
            // Configurar menus - removidas duplicações e adicionados todos os menus
            setupMenuToggle('maintenanceMenu', 'maintenanceSubmenu');
            setupMenuToggle('maintenanceSettingsMenu', 'maintenanceSettingsSubmenu');
            setupMenuToggle('mrpMenu', 'mrpSubmenu');
            setupMenuToggle('mrpReportsMenu', 'mrpReportsSubmenu');
            setupMenuToggle('supplyChainMenu', 'supplyChainSubmenu');
            setupMenuToggle('supplyChainReportsMenu', 'supplyChainReportsSubmenu');
            setupMenuToggle('hrMenu', 'hrSubmenu');
            setupMenuToggle('settingsMenu', 'settingsSubmenu');
            setupMenuToggle('userManagementMenu', 'userManagementSubmenu');
            setupMenuToggle('reportsHistoryMenu', 'reportsHistorySubmenu');
            setupMenuToggle('stocksMenu', 'stocksSubmenu');
            setupMenuToggle('partsMenu', 'partsSubmenu');
        });
        
        // Integração com Livewire para reconfigurar menus após atualizações do DOM
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', (message, component) => {
                // Reconfigurar a sidebar após cada atualização do Livewire
                setupSidebar();
                
                // Reconfigurar todos os menus principal e sub-menus
                const menuPairs = [
                    ['maintenanceMenu', 'maintenanceSubmenu'],
                    ['maintenanceSettingsMenu', 'maintenanceSettingsSubmenu'],
                    ['mrpMenu', 'mrpSubmenu'],
                    ['mrpReportsMenu', 'mrpReportsSubmenu'],
                    ['supplyChainMenu', 'supplyChainSubmenu'],
                    ['supplyChainReportsMenu', 'supplyChainReportsSubmenu'],
                    ['hrMenu', 'hrSubmenu'],
                    ['settingsMenu', 'settingsSubmenu'],
                    ['userManagementMenu', 'userManagementSubmenu'],
                    ['reportsHistoryMenu', 'reportsHistorySubmenu'],
                    ['stocksMenu', 'stocksSubmenu'],
                    ['partsMenu', 'partsSubmenu']
                ];
                
                // Inicializar cada par menu/submenu e garantir a existência dos elementos
                menuPairs.forEach(pair => {
                    if (document.getElementById(pair[0]) && document.getElementById(pair[1])) {
                        setupMenuToggle(pair[0], pair[1]);
                    }
                });
            });
        });
    </script>

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
                console.log('{{ trans("messages.toast_event_received") }}:', params);

                // Check if toastr is defined
                if (typeof toastr === 'undefined') {
                    console.error('Toastr is not defined!');
                    alert(params.message || '{{ __('messages.error_occurred') }}');
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
                    toastr.success(params.message, params.title || '{{ __('messages.success') }}');
                } else if (params.type === 'error') {
                    toastr.error(params.message, params.title || '{{ __('messages.error') }}');
                } else if (params.type === 'warning') {
                    toastr.warning(params.message, params.title || '{{ __('messages.warning') }}');
                } else if (params.type === 'info') {
                    toastr.info(params.message, params.title || '{{ __('messages.information') }}');
                } else {
                    // Default to info if type is not recognized
                    toastr.info(params.message, params.title || '{{ __('messages.information') }}');
                }
            });

            // Debug: log when notification event is received (legacy support)
            Livewire.on('notify', (params) => {
                console.log('{{ trans("messages.notification_event_received") }}:', params);

                // Check if toastr is defined
                if (typeof toastr === 'undefined') {
                    console.error('Toastr is not defined!');
                    alert(params.message || '{{ __('messages.error_occurred') }}');
                    return;
                }

                // Make sure we have a message
                if (!params.message) {
                    // Default messages based on type (using Laravel translations)
                    if (params.type === 'error') {
                        params.message = '{{ __('messages.no_permission') }}';
                    } else if (params.type === 'success') {
                        params.message = '{{ __('messages.operation_successful') }}';
                    } else if (params.type === 'warning') {
                        params.message = '{{ __('messages.warning_check_input') }}';
                    } else {
                        params.message = '{{ __('messages.information_notice') }}';
                    }
                }

                // Ensure we have a title (also translated)
                if (!params.title) {
                    if (params.type === 'error') {
                        params.title = '{{ __('messages.error') }}';
                    } else if (params.type === 'success') {
                        params.title = '{{ __('messages.success') }}';
                    } else if (params.type === 'warning') {
                        params.title = '{{ __('messages.warning') }}';
                    } else {
                        params.title = '{{ __('messages.information') }}';
                    }
                }

                // Configure toastr with specific settings for error messages
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

                // Display notification
                if (params.type === 'success') {
                    toastr.success(params.message, params.title || '{{ __('messages.success') }}');
                } else if (params.type === 'error') {
                    toastr.error(params.message, params.title || '{{ __('messages.error') }}');
                } else if (params.type === 'warning') {
                    toastr.warning(params.message, params.title || '{{ __('messages.warning') }}');
                } else {
                    // If no type is specified, default to error for permission issues
                    if (params.message.toLowerCase().includes('permission')) {
                        toastr.error(params.message, params.title || '{{ __('messages.permission_denied') }}');
                    } else {
                        toastr.info(params.message, params.title || '{{ __('messages.information') }}');
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

    @livewireScripts
    @stack('scripts')
    
    <!-- Relógio em tempo real -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateClock() {
                const now = new Date();
                const options = { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: false
                    // O timezone é definido automaticamente pelo PHP date_default_timezone_set
                };
                const timeString = now.toLocaleTimeString('pt-PT', options);
                const clockElement = document.querySelector('.clock-time');
                if (clockElement) {
                    clockElement.textContent = timeString;
                }
            }
            
            // Atualiza o relógio a cada segundo
            updateClock();
            setInterval(updateClock, 1000);
            
            // Manipuladores de eventos para formulários personalizados
            window.addEventListener('open-form-submission', function(event) {
                const { noteId, formId } = event.detail;
                // Renderizar o componente de submissão de formulário
                Livewire.emit('openFormSubmission', noteId, formId);
                
                // Se houver um modal para o formulário, podemos abri-lo aqui
                const formModal = document.getElementById('custom-form-submission-modal');
                if (formModal) {
                    // Usando Alpine.js para controlar o modal
                    const alpineData = formModal.__x.$data;
                    if (alpineData) {
                        alpineData.open = true;
                    }
                }
            });
            
            window.addEventListener('view-form-submission', function(event) {
                const { submissionId } = event.detail;
                // Renderizar o componente de visualização de submissão
                Livewire.emit('viewFormSubmission', submissionId);
                
                // Se houver um modal para visualização, podemos abri-lo aqui
                const viewModal = document.getElementById('view-form-submission-modal');
                if (viewModal) {
                    // Usando Alpine.js para controlar o modal
                    const alpineData = viewModal.__x.$data;
                    if (alpineData) {
                        alpineData.open = true;
                    }
                }
            });
        });
    </script>
</body>
</html>
