<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin - ' . config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Admin Header -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="/" class="flex items-center gap-2 group">
                        <img src="{{ asset('images/logos/logo.png') }}" alt="Logo" class="w-8 h-8">
                        
                    </a>
                    <span class="text-sm text-gray-500 ml-4 border-l border-gray-300 pl-4">Admin Panel</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Admin Menu -->
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-amber-700 font-medium transition">Dashboard</a>
                        <a href="{{ route('admin.orders.index') }}" class="text-gray-700 hover:text-amber-700 font-medium transition">Pesanan</a>
                    </nav>

                    <!-- User Dropdown -->
                    <div class="relative" id="adminUserDropdown">
                        <button id="adminUserButton" class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition">
                            <i class="fa-solid fa-user-circle text-2xl text-gray-700"></i>
                        </button>
                        <div id="adminUserMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden z-50">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                <i class="fa-solid fa-home mr-2"></i>Kembali ke Toko
                            </a>
                            <form action="{{ route('auth.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                    <i class="fa-solid fa-sign-out-alt mr-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto">
            @yield('content')
        </main>
    </div>
    @stack('scripts')

    <script>
        // Admin User Dropdown Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const userButton = document.getElementById('adminUserButton');
            const userMenu = document.getElementById('adminUserMenu');
            const userDropdown = document.getElementById('adminUserDropdown');

            // Toggle menu on button click
            if (userButton) {
                userButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                });
            }

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (userDropdown && !userDropdown.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });

            // Close menu when pressing Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    userMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
