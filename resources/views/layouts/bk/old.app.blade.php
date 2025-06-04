<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @livewireStyles
    @stack('styles')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <!-- Links de Manutenção -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="maintenanceDropdown" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    Manutenção
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="maintenanceDropdown">
                                    <li><a class="dropdown-item" href="{{ route('maintenance.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('maintenance.equipment') }}">Equipamentos</a></li>
                                    <li><a class="dropdown-item" href="{{ route('maintenance.task') }}">Tarefas</a></li>
                                    <li><a class="dropdown-item" href="{{ route('maintenance.corrective') }}">Manutenção Corretiva</a></li>
                                    <li><a class="dropdown-item" href="{{ route('maintenance.scheduling') }}">Agendamento</a></li>
                                    <li><a class="dropdown-item" href="{{ route('maintenance.category') }}">Categorias</a></li>
                                    <li><a class="dropdown-item" href="{{ route('maintenance.linearea') }}">Linhas & Áreas</a></li>
                                </ul>
                            </li>

                            <!-- Links de Relatórios -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    Relatórios
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                                    <li><a class="dropdown-item" href="{{ route('reports.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('reports.history') }}">Histórico</a></li>
                                </ul>
                            </li>

                            <!-- Links de Administração -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    Administração
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item" href="{{ route('users.management') }}">Usuários</a></li>
                                    <li><a class="dropdown-item" href="{{ route('permissions.management') }}">Permissões</a></li>
                                    <li><a class="dropdown-item" href="{{ route('holidays.management') }}">Feriados</a></li>
                                    <li><a class="dropdown-item" href="{{ route('settings.system') }}">Configurações</a></li>
                                </ul>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/bootstrap.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @livewireScripts

    @stack('scripts')
</body>
</html>
