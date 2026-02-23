<!-- Auth Modal Container -->
<div id="auth-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b">
            <h2 id="modal-title" class="text-2xl font-bold text-gray-900">Login</h2>
            <button id="close-auth-modal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">
                &times;
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- Login Form -->
            <form id="login-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="login-email" name="email" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan email Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="login-email-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="login-password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan password Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="login-password-error"></span>
                </div>

                <div id="login-general-error" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition">
                    <span id="login-btn-text">Login</span>
                    <span id="login-btn-spinner" class="hidden ml-2">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </form>

            <!-- Register Form -->
            <form id="register-form" class="space-y-4 hidden max-h-96 overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" id="register-name" name="name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan nama Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-name-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="register-email" name="email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan email Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-email-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                    <input type="tel" id="register-phone" name="phone" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan nomor telepon Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-phone-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap</label>
                    <textarea id="register-address" name="address" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan alamat lengkap Anda" rows="3"></textarea>
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-address-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                    <input type="text" id="register-city" name="city" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan kota Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-city-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                    <input type="text" id="register-province" name="province" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan provinsi Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-province-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                    <input type="text" id="register-postal-code" name="postal_code" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan kode pos Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-postal-code-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="register-password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Masukkan password (minimal 6 karakter)">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-password-error"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" id="register-password-confirmation" name="password_confirmation" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent"
                        placeholder="Konfirmasi password Anda">
                    <span class="text-red-500 text-sm mt-1 hidden error-message" id="register-password-confirmation-error"></span>
                </div>

                <div id="register-general-error" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition">
                    <span id="register-btn-text">Register</span>
                    <span id="register-btn-spinner" class="hidden ml-2">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </form>

            <!-- Toggle between login and register -->
            <div class="text-center mt-6 text-sm text-gray-600">
                <span id="toggle-text-login">Belum punya akun? <button type="button" id="toggle-to-register" class="text-blue-600 hover:text-blue-700 font-medium">Daftar di sini</button></span>
                <span id="toggle-text-register" class="hidden">Sudah punya akun? <button type="button" id="toggle-to-login" class="text-blue-600 hover:text-blue-700 font-medium">Login di sini</button></span>
            </div>
        </div>
    </div>
</div>

<!-- Cart Login Reminder Modal -->
<div id="cart-login-reminder-modal" class="fixed inset-0 z-[70] hidden bg-black/50 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 ease-out">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md transform scale-95 transition-transform duration-300 ease-out overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="bg-gray-50 px-6 py-8 text-center border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Login untuk Lanjutkan</h2>
        </div>

        <!-- Body -->
        <div class="px-6 py-6">
            <p class="text-gray-600 text-center text-sm mb-6 leading-relaxed">
                Untuk melihat dan mengelola keranjang belanja Anda, silakan login terlebih dahulu.
            </p>

            <div class="space-y-3">
                <button onclick="closeCartLoginReminderModal(); openAuthModal();" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                    Login Sekarang
                </button>
                <button onclick="closeCartLoginReminderModal();" class="w-full bg-white hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-300 border border-gray-300">
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const authModal = document.getElementById('auth-modal');
    const closeModalBtn = document.getElementById('close-auth-modal');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const toggleToRegister = document.getElementById('toggle-to-register');
    const toggleToLogin = document.getElementById('toggle-to-login');
    const modalTitle = document.getElementById('modal-title');

    // Close modal
    closeModalBtn.addEventListener('click', () => {
        authModal.style.display = 'none';
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
        document.getElementById('toggle-text-login').classList.remove('hidden');
        document.getElementById('toggle-text-register').classList.add('hidden');
        modalTitle.textContent = 'Login';
    });

    // Close modal when clicking outside
    authModal.addEventListener('click', (e) => {
        if (e.target === authModal) {
            authModal.style.display = 'none';
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
        }
    });

    // Toggle to register
    toggleToRegister.addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
        document.getElementById('toggle-text-login').classList.add('hidden');
        document.getElementById('toggle-text-register').classList.remove('hidden');
        modalTitle.textContent = 'Register';
    });

    // Toggle to login
    toggleToLogin.addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
        document.getElementById('toggle-text-login').classList.remove('hidden');
        document.getElementById('toggle-text-register').classList.add('hidden');
        modalTitle.textContent = 'Login';
    });

    // Handle login form submission
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const btnText = document.getElementById('login-btn-text');
        const btnSpinner = document.getElementById('login-btn-spinner');

        // Clear previous errors
        document.querySelectorAll('#login-form .error-message').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
        document.getElementById('login-general-error').classList.add('hidden');

        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');

        try {
            const response = await fetch('/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                authModal.style.display = 'none';
                
                // Clear form
                loginForm.reset();

                // Redirect to cart if came from cart, otherwise reload
                if (document.referrer.includes('/cart') || window.location.pathname === '/') {
                    window.location.href = '/cart';
                } else {
                    window.location.reload();
                }
            } else {
                document.getElementById('login-general-error').textContent = data.message;
                document.getElementById('login-general-error').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Login error:', error);
            document.getElementById('login-general-error').textContent = 'Terjadi kesalahan. Coba lagi.';
            document.getElementById('login-general-error').classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnSpinner.classList.add('hidden');
        }
    });

    // Handle register form submission
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const phone = document.getElementById('register-phone').value;
        const address = document.getElementById('register-address').value;
        const city = document.getElementById('register-city').value;
        const province = document.getElementById('register-province').value;
        const postalCode = document.getElementById('register-postal-code').value;
        const password = document.getElementById('register-password').value;
        const passwordConfirmation = document.getElementById('register-password-confirmation').value;
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        const btnText = document.getElementById('register-btn-text');
        const btnSpinner = document.getElementById('register-btn-spinner');

        // Clear previous errors
        document.querySelectorAll('#register-form .error-message').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
        document.getElementById('register-general-error').classList.add('hidden');

        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');

        try {
            const response = await fetch('/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    name, 
                    email,
                    phone,
                    address,
                    city,
                    province,
                    postal_code: postalCode,
                    password,
                    password_confirmation: passwordConfirmation
                })
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                authModal.style.display = 'none';
                
                // Clear form
                registerForm.reset();

                // Redirect to cart if came from cart, otherwise reload
                if (document.referrer.includes('/cart') || window.location.pathname === '/') {
                    window.location.href = '/cart';
                } else {
                    window.location.reload();
                }
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorEl = document.getElementById(`register-${field}-error`);
                        if (errorEl) {
                            errorEl.textContent = data.errors[field][0];
                            errorEl.classList.remove('hidden');
                        }
                    });
                } else {
                    document.getElementById('register-general-error').textContent = data.message || 'Terjadi kesalahan';
                    document.getElementById('register-general-error').classList.remove('hidden');
                }
            }
        } catch (error) {
            console.error('Register error:', error);
            document.getElementById('register-general-error').textContent = 'Terjadi kesalahan. Coba lagi.';
            document.getElementById('register-general-error').classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnSpinner.classList.add('hidden');
        }
    });

    // Expose functions globally to open modal from navbar
    window.openAuthModal = function() {
        authModal.style.display = 'flex';
    };

    // Function to show cart login reminder modal
    window.showCartLoginAlert = function() {
        const modal = document.getElementById('cart-login-reminder-modal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.style.opacity = '1';
                const content = modal.querySelector('div');
                content.style.transform = 'scale(1)';
            }, 10);
        }
    };

    // Function to close cart login reminder modal
    window.closeCartLoginReminderModal = function() {
        const modal = document.getElementById('cart-login-reminder-modal');
        if (modal) {
            modal.style.opacity = '0';
            const content = modal.querySelector('div');
            content.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    };

    // Close cart login modal when clicking outside
    document.getElementById('cart-login-reminder-modal')?.addEventListener('click', (e) => {
        if (e.target.id === 'cart-login-reminder-modal') {
            window.closeCartLoginReminderModal();
        }
    });
});
</script>
