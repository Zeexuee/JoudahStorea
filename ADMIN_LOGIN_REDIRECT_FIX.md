# Fix Admin Login Redirect - Dokumentasi

## Masalah
Halaman login admin memiliki dua opsi:
- **Kelola Barang** → arahkan ke `/admin`
- **Kelola Pesanan** → arahkan ke `/admin/dashboard`

**Namun, setiap kali login, selalu diarahkan ke `/admin` terlepas dari pilihan mana yang dipilih.**

## Root Cause
1. Halaman login menggunakan Filament framework
2. Default `LoginResponse` class dari Filament langsung redirect ke `Filament::getUrl()` 
3. Method `getRedirectUrl()` yang di-override di Login page tidak pernah di-call oleh Filament
4. Ini mengakibatkan semua login diarahkan ke URL default panel (/admin)

## Solusi Implementasi

### File 1: Buat Custom LoginResponse
**File:** `app/Filament/Http/Responses/AdminLoginResponse.php`

Custom response class yang:
- Membaca `login_type` dari session yang disimpan saat user klik tombol
- Menentukan redirect URL berdasarkan login_type:
  - `orders` → `/admin/dashboard`
  - `products` (default) → `/admin`
- Clear session setelah digunakan

### File 2: Update Login Page
**File:** `app/Filament/Pages/Auth/Login.php`

Perubahan:
- Method `setLoginType()` → menyimpan pilihan user ke session
- Method `authenticate()` → di-override untuk return `AdminLoginResponse` instead of default
- Flow: User klik tombol → setLoginType() save ke session → Submit form → authenticate() baca session → return custom response dengan redirect yang sesuai

## Cara Testing

### Test 1: Login untuk Kelola Pesanan
```
1. Buka halaman login admin: http://localhost/admin/login
2. Klik tombol "Kelola Pesanan" (pilihan kedua akan highlight)
3. Isikan email dan password admin
4. Klik "Sign In"
5. HASIL EXPECTED: Redirect ke /admin/dashboard
   ACTUAL: Sebelum fix = /admin, Sesudah fix = /admin/dashboard ✓
```

### Test 2: Login untuk Kelola Barang
```
1. Buka halaman login admin: http://localhost/admin/login
2. Klik tombol "Kelola Barang" (pilihan pertama highlight)
3. Isikan email dan password admin
4. Klik "Sign In"
5. HASIL EXPECTED: Redirect ke /admin
   ACTUAL: Sebelum fix = /admin, Sesudah fix = /admin ✓
```

### Test 3: Verifikasi Session
Jika ingin verify session backend:
```bash
# Buka terminal/console
cd /path/to/project

# Gunakan Tinker
php artisan tinker

# Check session setelah user login
>>> session('login_type')
=> "orders" // atau "products"
```

## Files yang Diubah

| File | Perubahan | Status |
|------|-----------|--------|
| `app/Filament/Pages/Auth/Login.php` | Override authenticate() method untuk return AdminLoginResponse | ✅ Updated |
| `app/Filament/Http/Responses/AdminLoginResponse.php` | Dibuat custom LoginResponse yang membaca session | ✨ Created |

## Technical Details

### Flow Diagram
```
User Interface (Blade Template)
    ↓
[Klik "Kelola Pesanan" button]
    ↓
wire:click="setLoginType('orders')"
    ↓
Login::setLoginType('orders')
├─ Set $this->loginType = 'orders'
└─ Session::put('login_type', 'orders')
    ↓
[User submit form]
    ↓
wire:submit="authenticate"
    ↓
Login::authenticate()
├─ Session::put('login_type', $this->loginType)
├─ Call parent::authenticate() [handle validation & auth]
├─ If parent success → Return new AdminLoginResponse()
└─ If parent failed → Return null
    ↓
AdminLoginResponse::toResponse()
├─ Read Session::get('login_type') → 'orders'
├─ Determine $redirectUrl = '/admin/dashboard'
├─ Session::forget('login_type') [cleanup]
└─ Return redirect()->intended($redirectUrl)
    ↓
[Browser redirect to /admin/dashboard] ✓
```

### Session Management
- **When set:** `setLoginType()` method dipanggil via Livewire when user click button
- **When used:** `AdminLoginResponse::toResponse()` baca session untuk determine redirect
- **When clear:** Session di-clear setelah digunakan untuk prevent side effects

## Notes
- Custom response ini hanya bekerja untuk admin login
- Tidak mempengaruhi user login atau fitur lainnya
- Session `login_type` di-clear otomatis setelah login berhasil
- Jika ada error saat authenticate, user akan kembali ke login page tanpa session tercemar

## Verification Checklist
- [x] File syntax OK
- [x] Class inheritance correct
- [x] Session put/get logic correct
- [x] Response interface implementation correct
- [ ] Manual test: Kelola Pesanan redirect OK
- [ ] Manual test: Kelola Barang redirect OK
- [ ] Check browser console no errors
- [ ] Check server logs no errors
