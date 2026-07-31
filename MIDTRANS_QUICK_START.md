# 🚀 Quick Start: Setup Midtrans (Bahasa Indonesia)

Alhamdulillah aktivasi Midtrans sudah selesai! Sekarang tinggal 3 langkah mudah:

---

## ✅ Langkah 1: Ambil Kredensial dari Dashboard

1. **Login** ke Dashboard Midtrans:
   - Sandbox (testing): https://dashboard.sandbox.midtrans.com
   - Production (live): https://dashboard.midtrans.com

2. **Buka Settings** → **Access Keys**

3. **Copy 2 value ini**:
   - ✅ **Server Key** (contoh: `SB-Mid-server-abc123...`)
   - ✅ **Client Key** (contoh: `SB-Mid-client-xyz789...`)

---

## ✅ Langkah 2: Update File .env

Buka file **`.env`** di root project, cari bagian `PAYMENT GATEWAY CONFIGURATION`, lalu edit:

```env
# Ganti dari mock ke mindtrans
PAYMENT_GATEWAY=mindtrans

# Paste Server Key dan Client Key dari dashboard
MINDTRANS_API_KEY=SB-Mid-server-paste-server-key-disini
MINDTRANS_API_SECRET=SB-Mid-client-paste-client-key-disini
MINDTRANS_MODE=sandbox
```

**Contoh lengkap**:
```env
PAYMENT_GATEWAY=mindtrans
MINDTRANS_API_KEY=SB-Mid-server-P1aBC2dEF3gH4i
MINDTRANS_API_SECRET=SB-Mid-client-Q5jKL6mNO7pQ8r
MINDTRANS_MODE=sandbox
```

💾 **Save** file `.env`

---

## ✅ Langkah 3: Clear Cache & Test

Jalankan command ini di terminal:

```bash
php artisan config:clear
php artisan cache:clear
php artisan serve
```

Buka browser: http://127.0.0.1:8000

**Test checkout flow:**
1. Login/Register
2. Tambah produk ke cart
3. Checkout → isi alamat → pilih kurir
4. Klik **"Proses Pembayaran"**
5. Anda akan diarahkan ke halaman Midtrans Snap
6. Pilih metode pembayaran (Gopay, Bank Transfer, QRIS, dll)
7. Untuk testing, gunakan **kartu kredit dummy**:
   ```
   Card Number: 4811 1111 1111 1114
   CVV: 123
   Exp: 01/25
   OTP: 112233
   ```
8. Setelah payment success, order status akan berubah jadi **"Processing"**

---

## 🎯 Selesai!

Sistem checkout dengan Midtrans sudah berfungsi! ✨

### Dokumentasi Lengkap:
- [MIDTRANS_SETUP_GUIDE.md](MIDTRANS_SETUP_GUIDE.md) - Setup lengkap, troubleshooting, webhook, dll
- [PAYMENT_TESTING_GUIDE.md](PAYMENT_TESTING_GUIDE.md) - Testing skenario

### Butuh Bantuan?
- **Webhook setup** untuk notifikasi otomatis → lihat [MIDTRANS_SETUP_GUIDE.md](MIDTRANS_SETUP_GUIDE.md) bagian "Setup Webhook"
- **Error troubleshooting** → lihat bagian "Troubleshooting" di guide
- **Production deployment** → lihat bagian "Deploy ke Production"

---

## 📞 Support

**Midtrans:**
- Docs: https://docs.midtrans.com
- Support: support@midtrans.com

**Sistem Toko:**
- Cek log: `storage/logs/laravel.log`
- Real-time monitor: `tail -f storage/logs/laravel.log`

Happy coding! 🚀
