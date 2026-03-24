<nav id="main-navbar" class="fixed top-0 w-full z-50 transition-all duration-300 bg-white text-gray-900 shadow-sm">
    <style>
        /* Desktop Navigation Styles */
        .nav-desktop {
            display: none;
        }

        @media (min-width: 768px) {
            .nav-desktop {
                display: flex;
            }

            .nav-mobile-btn {
                display: none !important;
            }
        }

        /* Mobile Menu Styles */
        .nav-mobile-btn {
            display: block;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .nav-mobile-btn:hover {
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        /* Hamburger Icon */
        .hamburger {
            width: 24px;
            height: 18px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hamburger span {
            width: 100%;
            height: 2px;
            background-color: #1a1a1a;
            transition: all 0.3s ease;
            display: block;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(7px, 7px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }

        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 40;
        }

        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Menu Panel */
        .mobile-menu {
            position: fixed;
            top: 80px;
            right: 0;
            bottom: 0;
            width: 100%;
            max-width: 400px;
            background-color: #ffffff;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            overflow-y: auto;
            box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 600px) {
            .mobile-menu {
                max-width: 100%;
            }
        }

        .mobile-menu.active {
            transform: translateX(0);
        }

        /* Mobile Menu Items */
        .mobile-menu-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mobile-menu-item {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .mobile-menu-item.border-t {
            border-top: 2px solid #e5e7eb;
        }

        .mobile-menu-item a,
        .mobile-menu-btn,
        .mobile-dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.2s ease;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            font-family: inherit;
        }

        .mobile-menu-item a:hover,
        .mobile-menu-btn:hover {
            background-color: #f9fafb;
            color: #b45309;
        }
        
        /* User menu items styling */
        .mobile-menu-item a.menu-content,
        .mobile-menu-item button.menu-content {
            gap: 12px;
            padding: 12px 16px;
        }
        
        .mobile-menu-item a.menu-content span,
        .mobile-menu-item button.menu-content span {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }
        
        .mobile-menu-item a.menu-content i,
        .mobile-menu-item button.menu-content i {
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }
        
        /* Logout button styling */
        .logout-btn {
            color: #dc2626;
            padding: 12px 16px;
        }
        
        .logout-btn:hover {
            background-color: #fef2f2 !important;
            color: #991b1b !important;
        }

        /* Mobile Dropdown */
        .mobile-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #fafafa;
        }

        .mobile-submenu.active {
            max-height: 500px;
        }

        .mobile-submenu-item {
            border-bottom: 1px solid #f0f0f0;
        }

        .mobile-submenu-item a {
            padding: 12px 24px 12px 48px;
            font-weight: 400;
            font-size: 13px;
        }

        /* Dropdown Indicator */
        .dropdown-arrow {
            transition: transform 0.3s ease;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dropdown-arrow.active {
            transform: rotate(180deg);
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center h-20 items-center relative">
            <!-- Left: Logo (Absolute Positioning) -->
            <div class="absolute left-0 flex-shrink-0 flex items-center">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center relative overflow-hidden">
                        <img src="{{ asset('images/logos/logo.png') }}" alt="Joudah Store">
                    </div>
                </a>
            </div>

            <!-- Center: Desktop Navigation Links -->
            <div class="nav-desktop space-x-8">
                <a href="/" class="nav-link font-bold text-sm border-b-2 border-transparent pb-1 hover:border-gray-300 transition">
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
                        <a href="{{ route('category.show', 'produk-lainnya') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">Produk Lainnya</a>
                    </div>
                </div>
            </div>

            <!-- Right: Cart Icon + Auth Buttons (Desktop Only) -->
            <div class="absolute right-0 hidden md:flex items-center gap-4">
                @auth
                    <a href="{{ route('cart.index') }}" class="p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Shopping Cart">
                        <i class="fa-solid fa-shopping-cart text-lg"></i>
                    </a>
                @else
                    <button onclick="showCartLoginAlert()" class="p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Shopping Cart">
                        <i class="fa-solid fa-shopping-cart text-lg"></i>
                    </button>
                @endauth

                @if(auth()->check())
                    <div class="relative group">
                        <button class="flex items-center justify-center p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Profil">
                            <i class="fa-solid fa-circle-user text-2xl"></i>
                        </button>
                        <!-- User Dropdown Menu -->
                        <div class="absolute top-full right-0 w-48 bg-white shadow-xl rounded-lg py-2 mt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">
                                <i class="fa-solid fa-user mr-2"></i>Profil
                            </a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">
                                <i class="fa-solid fa-history mr-2"></i>Riwayat Pesanan
                            </a>
                            <hr class="my-2">
                            <button onclick="handleLogout()" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                <i class="fa-solid fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </div>
                    </div>
                @else
                    <button onclick="openAuthModal()" class="flex items-center justify-center p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Login">
                        <i class="fa-solid fa-circle-user text-2xl"></i>
                    </button>
                @endif
            </div>

            <!-- Right: Cart Icon + Auth Button + Hamburger Menu Button (Mobile) -->
            <div class="absolute right-0 flex md:hidden items-center gap-2">
                @auth
                    <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Shopping Cart">
                        <i class="fa-solid fa-shopping-cart text-lg"></i>
                    </a>
                @else
                    <button onclick="showCartLoginAlert()" class="relative p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Shopping Cart">
                        <i class="fa-solid fa-shopping-cart text-lg"></i>
                    </button>
                @endauth
                @if(auth()->check())
                    <button onclick="toggleProfileMenu()" class="p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Profil">
                        <i class="fa-solid fa-circle-user text-2xl"></i>
                    </button>
                @else
                    <button onclick="openAuthModal()" class="p-2 text-gray-900 hover:bg-gray-100 transition rounded-lg" title="Login">
                        <i class="fa-solid fa-circle-user text-2xl"></i>
                    </button>
                @endif
                <button id="nav-hamburger-btn" class="nav-mobile-btn" aria-label="Toggle menu" aria-expanded="false">
                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="mobile-menu-overlay"></div>

    <!-- Mobile Menu Panel -->
    <div id="mobile-menu" class="mobile-menu">
        <ul class="mobile-menu-items">
            <li class="mobile-menu-item">
                <a href="/">Home</a>
            </li>
            <li class="mobile-menu-item">
                <a href="{{ route('category.show', 'kayu-gaharu') }}">Kayu Gaharu</a>
            </li>
            <li class="mobile-menu-item">
                <a href="{{ route('category.show', 'bukhur-gaharu') }}">Bukhur Gaharu</a>
            </li>
            <li class="mobile-menu-item">
                <a href="{{ route('category.show', 'perfume') }}">Perfume</a>
            </li>
            <li class="mobile-menu-item">
                <button class="mobile-dropdown-toggle" data-submenu="lainnya">
                    Lainnya
                    <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="submenu-lainnya" class="mobile-submenu">
                    <div class="mobile-submenu-item">
                        <a href="{{ route('category.show', 'linen-spray') }}">Linen Spray</a>
                    </div>
                    <div class="mobile-submenu-item">
                        <a href="{{ route('category.show', 'deodorant') }}">Deodorant</a>
                    </div>
                    <div class="mobile-submenu-item">
                        <a href="{{ route('category.show', 'premium-series') }}">Premium Series</a>
                    </div>
                    <div class="mobile-submenu-item">
                        <a href="{{ route('category.show', 'produk-lainnya') }}">Produk Lainnya</a>
                    </div>
                </div>
            </li>
            <li class="mobile-menu-item border-t border-gray-200">
                @auth
                    <a href="{{ route('cart.index') }}" class="menu-content">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-shopping-cart"></i>
                            <span>Keranjang</span>
                        </span>
                    </a>
                @else
                    <button onclick="showCartLoginAlert(); closeMenu();" class="menu-content">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-shopping-cart"></i>
                            <span>Keranjang</span>
                        </span>
                    </button>
                @endauth
            </li>
            @if(auth()->check())
                <li class="mobile-menu-item">
                    <a href="{{ route('profile.show') }}" class="menu-content">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-user"></i>
                            <span>Profil</span>
                        </span>
                    </a>
                </li>
                <li class="mobile-menu-item">
                        <a href="{{ route('orders.index') }}" class="menu-content">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-shopping-bag"></i>
                            <span>Riwayat Pesanan</span>
                        </span>
                    </a>
                </li>
                <li class="mobile-menu-item border-t border-gray-200">
                    <button onclick="handleLogout(); closeMenu();" class="menu-content logout-btn">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </span>
                    </button>
                </li>
            @else
                <li class="mobile-menu-item">
                    <button onclick="openAuthModal(); closeMenu();" class="w-full text-left px-5 py-3.5 bg-amber-600 text-white font-medium rounded-md hover:bg-amber-700 transition">
                        <i class="fa-solid fa-sign-in-alt mr-2"></i>Login
                    </button>
                </li>
            @endif
        </ul>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerBtn = document.getElementById('nav-hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
    const hamburger = hamburgerBtn.querySelector('.hamburger');
    const dropdownToggles = document.querySelectorAll('.mobile-dropdown-toggle');

    // Toggle mobile menu
    function toggleMenu() {
        const isActive = mobileMenu.classList.contains('active');
        
        if (!isActive) {
            // Open menu
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            hamburger.classList.add('active');
            document.body.style.overflow = 'hidden';
            hamburgerBtn.setAttribute('aria-expanded', 'true');
        } else {
            // Close menu
            closeMenu();
        }
    }

    function closeMenu() {
        mobileMenu.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
        hamburger.classList.remove('active');
        document.body.style.overflow = '';
        hamburgerBtn.setAttribute('aria-expanded', 'false');
        
        // Close all submenus
        dropdownToggles.forEach(toggle => {
            const submenuId = toggle.getAttribute('data-submenu');
            const submenu = document.getElementById(`submenu-${submenuId}`);
            const arrow = toggle.querySelector('.dropdown-arrow');
            
            if (submenu) {
                submenu.classList.remove('active');
                arrow.classList.remove('active');
            }
        });
    }

    window.closeMenu = closeMenu;

    // Hamburger button click
    hamburgerBtn.addEventListener('click', toggleMenu);

    // Overlay click
    mobileMenuOverlay.addEventListener('click', closeMenu);

    // Mobile menu items click (close menu)
    document.querySelectorAll('.mobile-menu-item a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    // Dropdown toggle
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuId = this.getAttribute('data-submenu');
            const submenu = document.getElementById(`submenu-${submenuId}`);
            const arrow = this.querySelector('.dropdown-arrow');
            
            if (submenu) {
                submenu.classList.toggle('active');
                arrow.classList.toggle('active');
            }
        });
    });

    // Close menu on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });

    // Initialize cart count on page load
    async function initializeCartCount() {
        try {
            const response = await fetch('/cart/count');
            if (response.ok) {
                const data = await response.json();
                const cartCountDesktop = document.getElementById('cart-count');
                const cartCountMobile = document.getElementById('cart-count-mobile');
                
                if (cartCountDesktop && data.cartCount > 0) {
                    cartCountDesktop.textContent = data.cartCount;
                    cartCountDesktop.style.display = 'flex';
                }
                if (cartCountMobile && data.cartCount > 0) {
                    cartCountMobile.textContent = data.cartCount;
                    cartCountMobile.style.display = 'flex';
                }
            }
        } catch (error) {
            console.error('Error loading cart count:', error);
        }
    }

    initializeCartCount();
    
    // Expose to window for updates from other scripts
    window.updateCartCount = function(count) {
        const cartCountDesktop = document.getElementById('cart-count');
        const cartCountMobile = document.getElementById('cart-count-mobile');
        
        if (count > 0) {
            if (cartCountDesktop) {
                cartCountDesktop.textContent = count;
                cartCountDesktop.style.display = 'flex';
            }
            if (cartCountMobile) {
                cartCountMobile.textContent = count;
                cartCountMobile.style.display = 'flex';
            }
        } else {
            if (cartCountDesktop) {
                cartCountDesktop.style.display = 'none';
            }
            if (cartCountMobile) {
                cartCountMobile.style.display = 'none';
            }
        }
    };

    // Handle logout
    window.handleLogout = function() {
        try {
            // Try POST first with CSRF token
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/auth/logout';
            form.style.display = 'none';

            // Add CSRF token
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = csrfMeta.content;
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
                return;
            }
        } catch (error) {
            console.error('Logout form error:', error);
        }

        // Fallback: Use simple GET redirect if POST fails or CSRF token missing
        setTimeout(() => {
            window.location.href = '/auth/logout';
        }, 500);
    };
});
</script>
