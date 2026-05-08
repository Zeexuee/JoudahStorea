# Panduan Migrasi SQLite → MySQL

**Status:** Siap untuk tahap testing
**Tanggal:** 8 Mei 2026

## ✅ Apa yang sudah selesai

1. ✅ Database backup: `backups/database.sqlite.backup` (0.30 MB)
2. ✅ Storage backup: `backups/storage_public/` (semua file upload)
3. ✅ Artisan command dibuat: `app/Console/Commands/TransferSqliteToMysql.php`
4. ✅ Config database updated: `sqlite_backup` connection added
5. ✅ MySQL credentials ready di `.env`

---

## 🚀 Langkah Berikutnya: Setup MySQL & Test Transfer

### 1️⃣ Start MySQL di Laragon
- Buka **Laragon** dari system tray (task bar kanan bawah)
- Klik **Start All** atau specific **MySQL**
- Tunggu ~10 detik sampai MySQL ready

### 2️⃣ Buat Database MySQL
Jalankan di folder project:
```powershell
.\setup-mysql.bat
```

Output yang diharapkan:
```
✓ MySQL connection successful
✓ Database created successfully
```

### 3️⃣ Test Transfer dengan Dry-Run
Jalankan di terminal:
```bash
php artisan db:transfer-sqlite-to-mysql --dry-run
```

**Apa yang akan terjadi:**
- Baca semua tabel dari SQLite
- Hitung jumlah rows per tabel
- **TIDAK** mengubah data MySQL (safe!)
- Tampilkan summary jumlah data

**Contoh output yang diharapkan:**
```
🔍 DRY-RUN MODE (no data will be modified)

Found 14 tables to transfer:
  • categories
  • migrations
  • orders
  • payments
  • products
  • shippings
  • users
  ... (dst)

Continue with transfer?

📊 TRANSFER SUMMARY
Tables processed: 14
Total rows transferred: 2,450
(DRY-RUN: no changes made)
```

### 4️⃣ Verifikasi Data
Setelah dry-run, periksa dengan Tinker:
```bash
php artisan tinker
```

Di Tinker, jalankan:
```php
// Hitung rows di SQLite
$sqlite_count = DB::connection('sqlite_backup')->table('users')->count();
echo "SQLite users: $sqlite_count";

// Hitung rows di MySQL (masih 0 karena belum transfer)
$mysql_count = DB::connection('mysql')->table('users')->count();
echo "MySQL users: $mysql_count";

exit
```

### 5️⃣ Jalankan Transfer (Live)
Setelah yakin dari dry-run, jalankan:
```bash
php artisan db:transfer-sqlite-to-mysql
```

**⚠️ PENTING:**
- Command akan **TRUNCATE** semua tabel MySQL (hapus existing data)
- Akan ask untuk konfirmasi sebelum truncate
- Tekan `y` untuk confirm

**Output yang diharapkan:**
```
⚠️  This will TRUNCATE MySQL tables. Proceed? (yes/no) [no]: y

✅ users transferred: 15 rows
✅ orders transferred: 45 rows
✅ products transferred: 200 rows
... (dst)

📊 TRANSFER SUMMARY
Tables processed: 14
Total rows transferred: 2,450
✅ Transfer completed successfully!
```

---

## 🔄 Untuk Testing: Switch ke MySQL Sementara

**File:** `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joudah_store
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan:
```bash
php artisan config:clear
php artisan cache:clear
php artisan serve
```

Test dengan login, buat order, upload gambar, dll.

---

## ⏮️ Rollback ke SQLite

Jika ada issue, switch kembali:
```env
DB_CONNECTION=sqlite
```

```bash
php artisan config:clear
php artisan cache:clear
```

SQLite backup masih ada di `backups/database.sqlite.backup`

---

## 📋 Checklist Sebelum Production

- [ ] Dry-run berhasil tanpa error
- [ ] Row count cocok (SQLite vs MySQL)
- [ ] Switch ke MySQL di staging/test machine
- [ ] Test semua flow: login, order, payment, upload
- [ ] Semua relasi bekerja (users→orders→payments)
- [ ] File uploads tersimpan dengan benar
- [ ] Backup SQLite & storage masih aman di `backups/`
- [ ] Siap switch production `.env`

---

## ❌ Troubleshooting

**Error: "Can't connect to MySQL server"**
- Check Laragon MySQL service running
- Jalankan `setup-mysql.bat` lagi

**Error: "TRUNCATE foreign key constraints"**
- Command otomatis disable FK checks saat transfer
- Akan re-enable setelah selesai

**Error: "Duplicate entry" saat transfer**
- Terdapat ID conflict (ID sudah ada di MySQL)
- Gunakan `--skip-truncate` flag (akan INSERT UPDATE jika duplicate key)

**Data tidak cocok setelah transfer**
- Verify row counts dengan:
  ```bash
  php artisan tinker
  # Cek: DB::table('orders')->count() untuk MySQL dan SQLite
  ```

---

**Status: Ready for testing** ✅

Jalankan `setup-mysql.bat` untuk lanjut ke tahap testing!
