# Solusi: Membuat Admin User - Production

## Situasi
Setelah update kode dengan keamanan baru yang memerlukan field `is_admin`, akun admin lama tertolak karena field belum di-set atau database belum di-migrate.

## Solusi Langkah-Langkah

### Step 1: Jalankan Migration (Jika Belum Ada Column is_admin)

Pertama, pastikan migration untuk menambahkan column `is_admin` sudah di-run:

```bash
php artisan migrate --force
```

**Output yang diharapkan:**
```
Migration table created successfully.
Migrating: 2026_02_15_add_is_admin_to_users_table
Migrated:  2026_02_15_add_is_admin_to_users_table (0.5 seconds)
```

> Jika migration sudah pernah di-run sebelumnya, command ini tidak akan menjalankan ulang migration yang sudah completed.

---

### Step 2: Membuat Admin User Baru

Gunakan artisan command yang sudah disiapkan untuk membuat admin user baru:

#### **Option A: Interaktif (Recommended)**
```bash
php artisan admin:make-admin --create
```

**Maka akan diminta input:**
- Email address
- Full name
- Password (min 6 characters)

**Contoh:**
```
$ php artisan admin:make-admin --create
Enter email address: admin@joudahstore.com
Enter full name: [Admin] 
Enter password (min 6 characters): ••••••••

✓ New admin user created successfully!
  Email: admin@joudahstore.com
  Name: Admin
  ID: 1
```

#### **Option B: Langsung dengan Parameter**
```bash
php artisan admin:make-admin --create --email=admin@joudahstore.com --name="Admin" --password="YourSecurePassword123"
```

---

### Step 3: Update User Existing Menjadi Admin

Jika ada user yang sudah ada dan ingin di-upgrade menjadi admin:

#### **Option A: Menggunakan Email (Interaktif)**
```bash
php artisan admin:make-admin
```

Akan menampilkan list semua email user untuk dipilih.

#### **Option B: Menggunakan User ID**
```bash
php artisan admin:make-admin 5
```

Ganti `5` dengan ID user yang ingin di-upgrade.

---

## ✅ Verifikasi

### Test 1: Check User di Database
```bash
php artisan tinker
>>> \App\Models\User::where('is_admin', true)->get();
```

### Test 2: Try Login
1. Buka halaman admin: `http://yoursite.com/admin`
2. Login dengan email admin yang baru dibuat
3. Seharusnya bisa akses admin dashboard

### Test 3: Check Unauthorized Access
1. Login dengan user biasa (is_admin = false)
2. Try akses `/admin`
3. Seharusnya di-reject dengan error 403

---

## 🔍 Troubleshooting

### Problem: "SQLSTATE[42S22]: Column not found"
**Cause:** Column `is_admin` belum ditambahkan ke table users
**Solution:** Jalankan migration:
```bash
php artisan migrate --force
```

### Problem: "User with email already exists"
**Cause:** Email sudah terdaftar
**Solution:** Gunakan email yang belum ada, atau update user existing

### Problem: "Password must be at least 6 characters"
**Cause:** Password terlalu pendek
**Solution:** Gunakan password minimal 6 karakter

### Problem: "No users found in database"
**Cause:** Database kosong
**Solution:** Jalankan seeder atau create user terlebih dahulu

---

## 📋 File Command
- **File:** `app/Console/Commands/MakeAdminUser.php`
- **Command:** `admin:make-admin`
- **Options:**
  - `{user_id?}` - User ID untuk di-upgrade (optional)
  - `{--create}` - Flag untuk create user baru
  - `{--email=}` - Email untuk user baru
  - `{--password=}` - Password untuk user baru  
  - `{--name=}` - Name untuk user baru

---

## 🚀 Contoh Full Flow

```bash
# 1. Run migration (add is_admin column)
php artisan migrate --force

# 2. Create new admin user
php artisan admin:make-admin --create --email=admin@joudah.com --name="Admin" --password="SecurePass123"

# 3. Verify
php artisan tinker
>>> \App\Models\User::where('email', 'admin@joudah.com')->first()
```

> Untuk halaman distributor, gunakan slug kategori kanonik baru di navbar dan halaman distributor publik yang sekarang menampilkan peta, kontak, dan daftar toko mitra.

---

## ⚠️ Security Notes

1. **Gunakan password yang kuat** - Minimal 6 chars, gunakan kombinasi uppercase, lowercase, number, special char
2. **Jangan share admin credentials** - Hanya admin yang butuh akses
3. **Check log** - Unauthorized login attempts di-log di `storage/logs/laravel.log`
4. **Production Migration** - Dari production env, ssh ke server dan jalankan migration

---

**Created:** Feb 16, 2026  
**Status:** Ready for Production ✅
