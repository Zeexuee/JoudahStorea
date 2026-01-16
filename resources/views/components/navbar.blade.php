<nav id="main-navbar" class="fixed top-0 w-full z-50 transition-all duration-300 bg-transparent text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center h-20 items-center relative">
            <!-- Left: Logo -->
            <div class="absolute left-0 flex-shrink-0 flex items-center">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-amber-200 flex items-center justify-center relative overflow-hidden">
                         <!-- Placeholder Abstract Logo -->
                         <div class="w-5 h-5 bg-amber-100 rounded-full absolute -left-1"></div>
                         <div class="w-5 h-5 bg-amber-300 rounded-full absolute -right-1 mix-blend-multiply"></div>
                    </div>
                </a>
            </div>

            <!-- Center: Navigation Links -->
            <div class="hidden md:flex space-x-8">
                <a href="/" class="nav-link font-bold text-sm border-b-2 border-transparent pb-1">
                    Home
                </a>
                <a href="{{ route('category.show', 'kayu-gaharu') }}" class="nav-link font-medium text-sm transition pb-1 border-b-2 border-transparent hover:border-gray-300">
                    Kayu Gaharu
                </a>
                <a href="{{ route('category.show', 'bukhur-gaharu') }}" class="nav-link font-medium text-sm transition pb-1 border-b-2 border-transparent hover:border-gray-300">
                    Bukhur Gaharu
                </a>
                <a href="{{ route('category.show', 'perfume') }}" class="nav-link font-medium text-sm transition pb-1 border-b-2 border-transparent hover:border-gray-300">
                    Perfume
                </a>
                <!-- Dropdown: Lainnya -->
                <div class="relative group">
                    <button class="nav-link font-medium text-sm transition pb-1 border-b-2 border-transparent hover:border-gray-300 flex items-center gap-1">
                        Lainnya
                        <svg class="w-3 h-3 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute top-full left-0 w-56 bg-white shadow-xl rounded-lg py-3 mt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 translate-y-2">
                        <a href="{{ route('category.show', 'linen-spray') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">Linen Spray</a>
                        <a href="{{ route('category.show', 'deodorant') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">Deodorant</a>
                        <a href="{{ route('category.show', 'premium-series') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">Premium Series</a>
                        <a href="{{ route('category.show', 'produk-luar') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">Produk Luar Joudah</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
