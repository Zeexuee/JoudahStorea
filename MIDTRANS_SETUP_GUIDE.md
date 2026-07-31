# 🎉 Panduan Setup Midtrans - Lengkap

**Status**: ✅ Aktivasi Midtrans Selesai  
**Tanggal**: 6 Maret 2026

---

## 🚀 Langkah-Langkah Setup

### 📋 Langkah 1: Dapatkan Kredensial Midtrans

1. **Login ke Dashboard Midtrans**  
   - Sandbox: https://dashboard.sandbox.midtrans.com
   - Production: https://dashboard.midtrans.com

2. **Ambil Access Keys**
   - Masuk ke menu **Settings** → **Access Keys**
   - Anda akan melihat:
     - **Server Key** (untuk API backend)
     - **Client Key** (untuk frontend)
     - **Merchant ID** (ID merchant Anda)

3. **Pilih Environment**
   - **Sandbox**: Untuk testing dengan kartu kredit dummy
   - **Production**: Untuk transaksi real

---

### ⚙️ Langkah 2: Update File `.env`

Buka file `.env` di root project Anda dan update bagian berikut:

```env
# ===================================================
# PAYMENT GATEWAY CONFIGURATION
# ===================================================
# Ganti dari 'mock' ke 'mindtrans' untuk mengaktifkan Midtrans
PAYMENT_GATEWAY=mindtrans

# Midtrans Payment Gateway (Snap API)
# Ganti value kosong dengan kredensial dari dashboard
MINDTRANS_API_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxxxx
MINDTRANS_API_SECRET=SB-Mid-client-xxxxxxxxxxxxxxxxxxxxx
MINDTRANS_MODE=sandbox
```

**Contoh dengan kredensial sandbox sebenarnya:**
```env
PAYMENT_GATEWAY=mindtrans
MINDTRANS_API_KEY=SB-Mid-server-abc123def456ghi789jkl
MINDTRANS_API_SECRET=SB-Mid-client-xyz987uvw654rst321mno
MINDTRANS_MODE=sandbox
```

**Catatan Penting:**
- **MINDTRANS_API_KEY** = Server Key dari Midtrans Dashboard
- **MINDTRANS_API_SECRET** = Client Key dari Midtrans Dashboard (opsional untuk backend, tapi sebaiknya diisi)
- Untuk **Sandbox**: Gunakan Server Key dan Client Key dari tab **Sandbox**
- Untuk **Production**: Gunakan Server Key dan Client Key dari tab **Production**
- `MINDTRANS_MODE=sandbox` untuk testing
- `MINDTRANS_MODE=production` untuk live (ganti saat sudah siap)
- **JANGAN tambahkan MINDTRANS_MERCHANT_ID** (tidak dibutuhkan oleh Snap API)

---

### 🔔 Langkah 3: Setup Webhook/Notification URL

Midtrans perlu tahu kemana mengirim notifikasi saat status payment berubah.

#### A. Di Dashboard Midtrans:

1. Masuk ke **Settings** → **Configuration**
2. Scroll ke bagian **Payment Notification URL**
3. Masukkan URL ini:
   ```
   https://domain-anda.com/payment/callback/mindtrans
   ```
   
   **Untuk Development/Local:**
   - Jika menggunakan **ngrok** atau **Laravel Valet**:
     ```
     https://your-ngrok-url.ngrok.io/payment/callback/mindtrans
     ```
   - Jika belum deploy, bisa skip dulu (test manual saja)

4. **Klik Save/Update**

#### B. Setup Ngrok (Opsional - untuk test lokal):

Jika ingin test webhook di localhost:
```bash
# Install ngrok dari https://ngrok.com
# Jalankan Laravel app
php artisan serve

# Di terminal lain, jalankan ngrok
ngrok http 8000

# Copy URL dari ngrok (contoh: https://abc123.ngrok.io)
# Paste ke Midtrans Dashboard sebagai notification URL
```

---

### ✅ Langkah 4: Verifikasi Konfigurasi

Setelah update `.env`, jalankan command ini:

```bash
# Clear cache untuk memastikan config terbaca
php artisan config:clear
php artisan cache:clear

# (Opsional) Test koneksi
php artisan tinker
```

Di tinker, test:
```php
// Cek config payment
config('payment.gateway');
// Output: "mindtrans"

config('payment.mindtrans.api_key');
// Output: "SB-Mid-server-xxxxx..."

// Test service
$service = new App\Services\MindtransPaymentService();
$service->isReady();
// Output: true (jika kredensial sudah benar)
```

Ketik `exit` untuk keluar dari tinker.

---

### 🧪 Langkah 5: Testing Payment Flow

#### Test End-to-End Checkout:

1. **Start Laravel Server:**
   ```bash
   php artisan serve
   ```

2. **Buka Browser:**
   ```
   http://127.0.0.1:8000
   ```

3. **Lakukan Transaksi Test:**
   - Login atau Register akun
   - Tambah produk ke cart
   - Klik icon cart → **Checkout**
   - Isi alamat pengiriman
   - Pilih metode pengiriman
   - Klik **"Lanjut ke Pembayaran"**
   - Klik **"Proses Pembayaran"**
   
4. **Anda akan diarahkan ke Midtrans Snap:**
   - Halaman payment Midtrans akan muncul
   - Pilih metode pembayaran (BCA, Gopay, QRIS, dll)

5. **Test dengan Kartu Kredit Dummy (Sandbox):**
   - Gunakan kartu test dari Midtrans:
     ```
     Card Number: 4811 1111 1111 1114
     CVV: 123
     Exp Date: 01/25
     ```
   - Masukkan OTP: `112233`
   - Status: Success ✅

6. **Cek Order Status:**
   - Setelah payment berhasil, cek di **Profile** → **Orders**
   - Status order harus jadi **"Processing"**
   - Status payment harus **"Completed"**

---

### 📝 Kartu Test Midtrans (Sandbox)

Untuk testing berbagai skenario:

| Scenario | Card Number | CVV | Exp | OTP |
|----------|------------|-----|-----|-----|
| ✅ Success | 4811 1111 1111 1114 | 123 | 01/25 | 112233 |
| ❌ Denied | 4911 1111 1111 1113 | 123 | 01/25 | 112233 |
| ⏱️ Challenge by FDS | 4411 1111 1111 1118 | 123 | 01/25 | 112233 |

**Catatan:** 
- OTP selalu `112233` untuk semua test card
- Bisa juga test dengan Gopay, OVO, DANA dummy di sandbox

---

### 🌐 Deploy ke Production

Saat sudah siap untuk go-live:

1. **Ganti Mode di `.env`:**
   ```env
   MINDTRANS_MODE=production
   MINDTRANS_API_KEY=Mid-server-xxxxx (production key)
   MINDTRANS_API_SECRET=Mid-client-xxxxx (production key)
   ```

2. **Update Webhook URL di Dashboard:**
   - Ganti dari URL test ke production URL
   - Contoh: `https://tokoanda.com/payment/callback/mindtrans`

3. **Clear Cache di Server:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

4. **Test dengan Transaksi Kecil:**
   - Lakukan transaksi real dengan nominal kecil
   - Pastikan notifikasi webhook masuk
   - Cek status order update dengan benar

---

## 🐛 Troubleshooting

### Problem 1: Error "Midtrans is not configured"

**Solution:**
- Pastikan **MINDTRANS_API_KEY** (Server Key) sudah diisi di `.env`
- Jalankan `php artisan config:clear` dan `php artisan cache:clear`
- Cek dengan tinker:
  ```bash
  php artisan tinker
  >>> config('payment.mindtrans.api_key')
  >>> $service = new App\Services\MindtransPaymentService();
  >>> $service->getConfigStatus()
  ```

### Problem 2: Payment tidak redirect ke Midtrans

**Cek:**
1. Apakah `PAYMENT_GATEWAY=mindtrans` di `.env`?
2. Apakah ada error di `storage/logs/laravel.log`?
3. Buka Browser DevTools → Network tab, cek request API

**Debug:**
```bash
# Lihat log real-time
tail -f storage/logs/laravel.log
```

### Problem 3: Webhook tidak update status order

**Cek:**
1. Apakah Notification URL sudah benar di Dashboard Midtrans?
2. Apakah server bisa diakses dari internet (tidak localhost)?
3. Cek log: `storage/logs/laravel.log`

**Test Webhook Manual:**
```bash
# Test endpoint dengan curl
curl -X POST http://your-domain.com/payment/callback/mindtrans \
  -H "Content-Type: application/json" \
  -d '{
    "transaction_status": "settlement",
    "order_id": "test-order-123",
    "gross_amount": "100000.00"
  }'tidak valid
- Menggunakan sandbox key di production mode (atau sebaliknya)
- Format Server Key salah (harus diawali "SB-Mid-server-" untuk sandbox atau "Mid-server-" untuk production)

**Solution:**
- Re-check Server Key di Dashboard Midtrans → Settings → Access Keys
- Pastikan mode dan key cocok:
  - Sandbox mode: `MINDTRANS_MODE=sandbox` + Server Key dari tab Sandbox
  - Production mode: `MINDTRANS_MODE=production` + Server Key dari tab Production
- Copy-paste ulang Server Key (jangan ada spasi di awal/akhir)
- Clear config: `php artisan config:clear`
- Server Key salah atau expired
- Menggunakan sandbox key di production mode (atau sebaliknya)

**Solution:**
- Re-check Server Key di Dashboard Midtrans
- Pastikan mode dan key cocok (sandbox-sandbox, production-production)

---

## 📊 Monitoring & Logs

### Laravel Logs:
```bash
# Monitor real-time
tail -f storage/logs/laravel.log

# Search for payment errors
grep -i "midtrans" storage/logs/laravel.log
grep -i "payment" storage/logs/laravel.log
```

### Midtrans Dashboard:
- **Transactions**: Lihat semua transaksi
- **Transaction Status**: Pending, Settlement, Expired, Cancel
- **Settlement**: Dana masuk ke rekening

---

## ✅ Checklist Sebelum Go-Live

- [ ] Kredensial **Production** sudah dimasukkan ke `.env`
- [ ] `MINDTRANS_MODE=production`
- [ ] Webhook URL production sudah diset di Dashboard
- [ ] Test transaksi kecil (1000 - 10000 rupiah) berhasil
- [ ] Order status update otomatis setelah payment
- [ ] Email konfirmasi terkirim (jika ada)
- [ ] Backup database sebelum deploy
- [ ] Clear cache di production server

---

## 🎯 Quick Reference

### Kredensial Locations:
- Midtrans Dashboard → Settings → Access Keys

### Config File:
- `.env` → `MINDTRANS_*` variables

### Routes:
- Payment Process: `/payment/{order}/process`
- Webhook Callback: `/payment/callback/mindtrans`

### Service File:
- `app/Services/MindtransPaymentService.php`

### Controller:
- `app/Http/Controllers/PaymentController.php`

---

## 📞 Support

### Midtrans Support:
- Email: support@midtrans.com
- Docs: https://docs.midtrans.com
- Dashboard: https://dashboard.midtrans.com

### Testing Tools:
- Sandbox: https://simulator.sandbox.midtrans.com
- Test Cards: https://docs.midtrans.com/en/technical-reference/sandbox-test

---

## 🎉 Selesai!

Sistem checkout dengan Midtrans sudah siap digunakan!  
Silakan test dan jangan ragu untuk bertanya jika ada issue.

**Happy Coding! 🚀**
