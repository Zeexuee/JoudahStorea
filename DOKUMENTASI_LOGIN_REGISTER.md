# Dokumentasi Sistem Login & Register dengan Modal

## Ringkasan Fitur

Sistem login dan register telah diimplementasikan dengan modal yang dapat dipanggil dari mana saja di aplikasi. Sistem ini terintegrasi penuh dengan cart items sehingga setiap user memiliki keranjang mereka sendiri.

## File-File yang Telah Dibuat/Diubah

### 1. **Database Migration**
- **File:** `database/migrations/2026_02_05_100000_update_cart_items_table_add_user_id.php`
- **Fungsi:** Menambahkan kolom `user_id` ke tabel `cart_items`
- **Perubahan:**
  - Menambah foreign key `user_id` yang referensi ke tabel `users`
  - Mengubah unique constraint dari `['session_id', 'product_id']` menjadi `['user_id', 'product_id']`
  - Tetap mempertahankan `session_id` untuk user yang belum login

### 2. **Model Perubahan**

#### CartItem Model
- **File:** `app/Models/CartItem.php`
- **Perubahan:**
  - Menambah `user_id` ke `$fillable`
  - Menambah relasi `user()` untuk relasi dengan User

#### User Model
- **File:** `app/Models/User.php`
- **Perubahan:**
  - Menambah relasi `cartItems()` untuk relasi dengan CartItem

### 3. **Controller Baru**
- **File:** `app/Http/Controllers/AuthController.php`
- **Fungsi:** Handle login, register, logout, dan migrasi cart dari session ke user
- **Methods:**
  - `login()` - Validasi email dan password, auto-login user, migrasi cart
  - `register()` - Buat user baru, validasi data, auto-login, migrasi cart
  - `logout()` - Logout user dan invalidate session
  - `getCurrentUser()` - Get user yang sedang login
  - `migrateSessionCartToUser()` - Migrasi cart items dari session_id ke user_id

### 4. **Controller Update**
- **File:** `app/Http/Controllers/CartController.php`
- **Perubahan:** 
  - Menambah method `getCartIdentifier()` untuk cek user login atau session
  - Menambah method `queryCartItems()` untuk query cart berdasarkan user atau session
  - Update semua method (`index`, `add`, `remove`, `updateQuantity`, `getCartCount`) untuk support user dan session

### 5. **View Component Baru**
- **File:** `resources/views/components/auth-modal.blade.php`
- **Fungsi:** Modal form login dan register dalam satu component
- **Fitur:**
  - Toggle antara form login dan register
  - Input validation dengan error messages
  - Loading spinner saat submit
  - Auto-close modal saat berhasil login/register
  - Migrasi session cart otomatis

### 6. **View Component Update**
- **File:** `resources/views/components/navbar.blade.php`
- **Perubahan:**
  - Menambah tombol "Login" untuk user yang belum login (desktop dan mobile)
  - Menambah dropdown menu user untuk user yang sudah login (desktop)
  - Menambah user name dan avatar di navbar
  - Menambah menu logout
  - Menambah button login di mobile menu
  - Menambah `closeMenu()` sebagai window function untuk dipanggil dari modal

### 7. **Layout Update**
- **File:** `resources/views/components/layouts/app.blade.php`
- **Perubahan:** Menambah `<x-auth-modal />` component untuk tampilkan modal di semua halaman

### 8. **Routes Update**
- **File:** `routes/web.php`
- **Routes Baru:**
  ```
  POST /auth/login              -> AuthController@login
  POST /auth/register           -> AuthController@register  
  POST /auth/logout             -> AuthController@logout (middleware: auth)
  GET  /auth/user               -> AuthController@getCurrentUser
  ```

## Cara Penggunaan

### Memanggil Modal Login/Register
Modal bisa dipanggil dari mana saja dengan JavaScript:
```javascript
openAuthModal();
```

### Login User
```javascript
// Form akan di-submit ke /auth/login
// Data: { email, password }
```

### Register User
```javascript
// Form akan di-submit ke /auth/register
// Data: { name, email, password, password_confirmation }
```

### Logout User
Tombol logout ada di dropdown menu user di navbar:
```html
<form id="logout-form" action="{{ route('auth.logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
```

## Fitur Otomatis

### 1. Migrasi Cart dari Session ke User
Ketika user berhasil login atau register:
- Cart items yang sebelumnya disimpan dengan session_id akan dimigrasi ke user_id
- Jika user sudah memiliki produk yang sama di cartnya, quantity akan dijumlahkan
- session_id akan di-set ke NULL untuk item yang sudah dimigrasi

### 2. Conditional Rendering
- Navbar menampilkan tombol "Login" jika user belum login
- Navbar menampilkan dropdown user jika user sudah login
- Mobile menu menampilkan tombol login jika belum login, dan logout jika sudah login

## Database Structure

### Cart Items Table (Setelah Migration)
```
id                  - INT (Primary Key)
user_id             - INT (Foreign Key to users, nullable)
session_id          - STRING (Nullable, untuk guest)
product_id          - INT (Foreign Key to products)
quantity            - INT (default 1)
created_at          - TIMESTAMP
updated_at          - TIMESTAMP

Unique Constraint: user_id + product_id
```

## Frontend JavaScript

Modal menggunakan vanilla JavaScript dengan fitur:
- Form submission dengan AJAX/Fetch
- Client-side form validation
- Error handling dan display
- Loading state dengan spinner
- Toggle antara login dan register form
- Auto-reload halaman setelah login berhasil

## Middleware & Protection

- Route `/auth/logout` menggunakan middleware `auth` sehingga hanya user terautentikasi yang bisa logout
- Semua route auth endpoints handle ValidationException dan return JSON error response

## Customization

Anda bisa customize:
1. **Styling:** Edit Tailwind classes di `auth-modal.blade.php`
2. **Validation Rules:** Edit di `AuthController` methods `login()` dan `register()`
3. **Modal Trigger:** Call `openAuthModal()` dari button manapun
4. **Redirect:** Edit behavior setelah login berhasil di `auth-modal.blade.php` line dengan `window.location.reload()`

## Testing

### Test Login
1. Buka aplikasi
2. Klik tombol "Login" di navbar
3. Masukkan email dan password user yang sudah ada
4. Klik "Login"
5. Modal akan tertutup dan halaman reload

### Test Register
1. Buka aplikasi  
2. Klik tombol "Login" di navbar
3. Klik "Daftar di sini"
4. Isi form dengan data baru (email belum terdaftar)
5. Klik "Register"
6. Modal akan tertutup dan halaman reload

### Test Cart Migration
1. Tambahkan produk ke cart (tanpa login)
2. Login/register
3. Cart item akan tetap ada dan terassosiasi dengan user yang login

## Notes Penting

- Password harus minimal 6 karakter
- Email harus unique
- Session masih digunakan untuk CSRF protection dan session handling
- Untuk production, tambahkan rate limiting di login/register routes
- Pertimbangkan untuk menambahkan email verification di masa depan
