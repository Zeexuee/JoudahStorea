# Panduan Implementasi Modal Login/Register di Berbagai Tempat

## 1. Trigger Modal dari Button
```html
<button onclick="openAuthModal()" class="your-button-class">
    Login Sekarang
</button>
```

## 2. Trigger Modal dengan Custom Class
```html
<!-- Bootstrap Button -->
<button class="btn btn-primary" onclick="openAuthModal()">
    Login
</button>

<!-- Material Design Button -->
<button class="mdc-button" onclick="openAuthModal()">
    Login
</button>
```

## 3. Trigger Modal dari Link
```html
<a href="javascript:void(0)" onclick="openAuthModal()" class="text-blue-600 hover:text-blue-800">
    Login di sini
</a>
```

## 4. Trigger Modal pada Event
```javascript
// Saat klik tombol add to cart (jika user belum login)
document.getElementById('add-to-cart-btn').addEventListener('click', function() {
    if (!userIsLoggedIn) {
        openAuthModal();
    } else {
        // Lanjutkan proses add to cart
    }
});
```

## 5. Trigger Modal Saat Page Load
```javascript
// Tampilkan modal jika ada parameter redirect
const params = new URLSearchParams(window.location.search);
if (params.get('login') === '1') {
    openAuthModal();
}
```

## 6. Conditional Render Based on Auth Status

### Blade Template
```blade
@if(auth()->check())
    <!-- User sudah login -->
    <p>Selamat datang, {{ auth()->user()->name }}!</p>
@else
    <!-- User belum login -->
    <button onclick="openAuthModal()">Login untuk melanjutkan</button>
@endif
```

### JavaScript
```javascript
async function checkAuthStatus() {
    const response = await fetch('/auth/user');
    const data = await response.json();
    
    if (data.success && data.user) {
        console.log('User:', data.user);
        // User sudah login
    } else {
        console.log('User belum login');
        // User belum login
    }
}

checkAuthStatus();
```

## 7. Protect Routes (Require Login)
```javascript
// Di product detail page - require login untuk add review
const submitReviewBtn = document.getElementById('submit-review-btn');
submitReviewBtn.addEventListener('click', async function(e) {
    e.preventDefault();
    
    // Check apakah user sudah login
    const userResponse = await fetch('/auth/user');
    const userData = await userResponse.json();
    
    if (!userData.user) {
        openAuthModal();
        return;
    }
    
    // Lanjutkan submit review
    submitReview();
});
```

## 8. Show Different Content After Login

### Navbar Conditional
```blade
<!-- Di navbar.blade.php -->
@if(auth()->check())
    <!-- Tampilkan order history link -->
    <a href="/order-history">Riwayat Pesanan</a>
    
    <!-- Tampilkan user dropdown -->
    <div class="user-menu">
        <span>{{ auth()->user()->name }}</span>
        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button>Logout</button>
        </form>
    </div>
@else
    <!-- Tampilkan login button -->
    <button onclick="openAuthModal()">Login</button>
@endif
```

## 9. Auto-Login After Checkout

```javascript
// Setelah checkout berhasil
async function completeCheckout() {
    // ... proses checkout ...
    
    // Jika user belum login, suggest mereka login/register
    const userResponse = await fetch('/auth/user');
    const userData = await userResponse.json();
    
    if (!userData.user) {
        // Show modal dengan pesan khusus
        showCheckoutMessage();
        openAuthModal();
    }
}
```

## 10. Handle Auth Errors

```javascript
// Custom error handling
async function handleAuthError(error) {
    if (error.response?.status === 401) {
        // Unauthorized - user session expired
        console.log('Session expired, please login again');
        openAuthModal();
    }
}
```

## 11. Update Cart Count After Login

```javascript
// Di auth-modal.blade.php, setelah login berhasil
if (data.success) {
    // Update cart count
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        // Fetch updated cart count
        const countResponse = await fetch('/cart/count');
        const countData = await countResponse.json();
        cartCount.textContent = countData.cartCount;
    }
    
    // Close modal
    authModal.style.display = 'none';
    
    // Reload atau update UI
    window.location.reload();
}
```

## 12. Integration dengan Cart (Contoh Real)

### Product Detail Page
```html
<button id="add-cart-btn" class="btn btn-primary">
    Tambah ke Keranjang
</button>

<script>
document.getElementById('add-cart-btn').addEventListener('click', async function() {
    const productId = {{ $product->id }};
    const quantity = document.getElementById('quantity').value;
    
    try {
        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: parseInt(quantity)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update cart count
            document.getElementById('cart-count').textContent = data.cartCount;
            alert('Produk ditambahkan ke keranjang!');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambah ke keranjang');
    }
});
</script>
```

## 13. Remember Me Feature (Future Implementation)

```javascript
// Di auth-modal.blade.php - tambahkan checkbox
<div>
    <label>
        <input type="checkbox" id="remember-me" name="remember">
        Ingat saya
    </label>
</div>

// Saat login
const rememberMe = document.getElementById('remember-me').checked;
body JSON: { email, password, remember: rememberMe }
```

## 14. Social Login Button (Future)

```html
<!-- Di auth modal -->
<div class="social-login">
    <button class="google-btn" onclick="loginWithGoogle()">
        <i class="fab fa-google"></i> Login dengan Google
    </button>
    <button class="facebook-btn" onclick="loginWithFacebook()">
        <i class="fab fa-facebook"></i> Login dengan Facebook
    </button>
</div>
```

## 15. Notification After Successful Action

```javascript
// Setelah register berhasil
if (data.success) {
    // Tampilkan notifikasi
    showNotification('Register berhasil! Selamat datang ' + data.user.name, 'success');
    
    // Close modal
    authModal.style.display = 'none';
}
```

## Notes

- `openAuthModal()` adalah fungsi global yang didefinisikan di `auth-modal.blade.php`
- Pastikan modal sudah di-include di layout (`app.blade.php` sudah include `<x-auth-modal />`)
- Untuk production, tambahkan rate limiting dan captcha di login/register
- Gunakan HTTPS untuk security
- Simpan sensitive data di localStorage dengan hati-hati atau gunakan session saja
