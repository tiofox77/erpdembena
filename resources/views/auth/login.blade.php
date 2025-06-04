<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>DEMBENA ERP - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-industry mr-2"></i>
                    DEMBENA ERP
                </h1>
                <p class="text-gray-600 mt-2">Industrial Maintenance Integrated System</p>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-blue-600 border-b border-blue-700">
                    <h2 class="text-xl font-semibold text-white">System Access</h2>
                </div>

                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 p-4 rounded-md bg-green-50 text-green-800 border-l-4 border-green-500">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 rounded-md bg-red-50 text-red-800 border-l-4 border-red-500">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input
                                    id="email"
                                    type="email"
                                    class="w-full pl-10 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-300 focus:ring-red-500 @enderror"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    autofocus
                                    placeholder="name@company.com"
                                >
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message === 'These credentials do not match our records.' ? 'Invalid credentials. Please check your email and password.' : $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    class="w-full pl-10 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-300 focus:ring-red-500 @enderror"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                >
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center">
                                <input
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    type="checkbox"
                                    name="remember"
                                    id="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label class="ml-2 block text-sm text-gray-700" for="remember">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <button
                                type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center"
                            >
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login
                            </button>
                        </div>

                        @if (Route::has('password.request'))
                            <div class="mt-4 text-center">
                                <a class="text-sm text-blue-600 hover:text-blue-800" href="{{ route('password.request') }}">
                                    <i class="fas fa-key mr-1"></i>
                                    Forgot your password?
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="mt-6 text-center text-gray-500 text-sm">
                <div>
                    <strong>DEMBENA ERP</strong> - Industrial Management System
                </div>
                <div class="mt-1">
                    &copy; {{ date('Y') }} DEMBENA. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
