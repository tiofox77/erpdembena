<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Manutenção')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- FullCalendar Core -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />

    <!-- Tippy.js -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css"/>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
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

        /* Alpine.js - Ocultar elementos com x-cloak */
        [x-cloak] {
            display: none !important;
        }
    </style>

    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content -->
        <div class="main-content min-h-screen bg-gray-50">
            <!-- Header -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
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

        // Open maintenance menu by default
        document.addEventListener('DOMContentLoaded', function() {
            const maintenanceSubmenu = document.getElementById('maintenanceSubmenu');
            const indicator = document.querySelector('#maintenanceMenu .dropdown-indicator');
            maintenanceSubmenu.classList.add('open');
            indicator.classList.add('open');
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
