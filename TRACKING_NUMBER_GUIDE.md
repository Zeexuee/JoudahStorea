# 📍 Panduan Mendapatkan Nomor Resi (Tracking Number)

## 3 Cara Mendapatkan Nomor Resi

### 1️⃣ **Dari Raja Ongkir API** (Otomatis)
Jika integrasi Raja Ongkir sudah aktif di sistem Anda:

```
Flow:
Admin Panel → Create Shipment via Raja Ongkir 
→ API returns tracking_number 
→ Auto-fill dalam database shippings table
→ Langsung bisa Print Resi
```

**Keuntungan:**
- Otomatis, tidak perlu input manual
- Data real-time dari kurir
- Terintegrasi langsung dengan sistem tracking kurir

**Bagaimana:**
- Gunakan dashboard Raja Ongkir untuk submit shipment
- Tracking number akan di-return dari API
- Simpan ke field `tracking_number` di order

---

### 2️⃣ **Input Manual di Panel Admin**
Cek website kurir → copy nomor resi → paste di admin panel

**Langkah:**
1. Buka order page di admin panel
2. Scroll ke bagian "Status Pengiriman"
3. Isi form "Update Data Pengiriman":
   - **Kurir:** JNE, POS, TIKI, Shopee Express, etc
   - **Status Pengiriman:** Pilih status (biasanya "Sudah Diambil" jika pickup sudah)
   - **Nomor Resi:** Copy-paste nomor resi dari kurir
4. Klik **"💾 Simpan Data Pengiriman"**
5. Sekarang nomor resi tersimpan → bisa Print Resi

**Tempat Cek Nomor Resi:**

#### 🚚 **JNE**
- Website: https://www.jne.co.id
- Menu: "Lacak" → Masukkan nomor resi
- Nomor resi format: Contoh `510123456789`

#### 📮 **Pos Indonesia**
- Website: https://www.posindonesia.co.id
- Menu: "Lacak Pengiriman" → Masukkan nomor resi
- Nomor resi format: Contoh `EA123456789ID`

#### 🚛 **TIKI**
- Website: https://www.tiki.id
- Menu: "Lacak" → Masukkan nomor resi
- Nomor resi format: Contoh `000123456`

#### 🛍️ **Shopee Express**
- Website: https://shopee.co.id/m/settings/shipping
- Atau cek email dari Shopee
- Nomor resi format: Contoh `SE1234567890`

---

### 3️⃣ **Dari Email Kurir**
Kurir biasanya kirim email konfirmasi pengiriman dengan tracking number

**Ciri-ciri:**
- Subject: "Pengiriman telah dipickup" atau "Shipment Picked Up"
- Body email berisi nomor resi
- Biasanya ada link tracking untuk pelacakan real-time

**Contoh Email JNE:**
```
Dari: noreply@jne.co.id
Subject: Pengiriman telah dipickup oleh kurir
Body: Nomor Resi: 510123456789
Tracking: https://tracking.jne.co.id/tracking/510123456789
```

---

## 📋 Checklist Sebelum Print Resi

- ✅ Order sudah dibayar (status pembayaran = "Completed")
- ✅ Nomor resi sudah diisi di admin panel
- ✅ Kurir sudah memilih (JNE/POS/TIKI/etc)
- ✅ Status pengiriman sudah diupdate (minimal "Sudah Diambil")

## 🔗 Integrasi Raja Ongkir

Jika Anda menggunakan Raja Ongkir API untuk shipping:

```php
// Saat submit shipment ke Raja Ongkir API
// Response biasanya return:
{
    "status": 200,
    "data": {
        "receipt_number": "510123456789",  // <-- Ini nomor resi!
        "courier": "JNE",
        "service": "REG"
    }
}

// Simpan receipt_number ke database:
Shipping::create([
    'order_id' => $order->id,
    'tracking_number' => $response['data']['receipt_number'],
    'courier' => $response['data']['courier'],
    // ... data lainnya
]);
```

---

## 🎯 Flowchart: Dari Order ke Print Resi

```
┌─────────────────────────────────────┐
│ 1. Customer Order & Payment         │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 2. Admin Update Shipping Status     │
│    • Isi Kurir                      │
│    • Isi Nomor Resi                 │
│    • Ubah Status: "Sudah Diambil"   │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 3. Print Resi (Ready)               │
│    ✅ Tombol Print Resi Aktif       │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 4. Customer Terima Invoice          │
│    (Kirim nomor resi ke customer)   │
└─────────────────────────────────────┘
```

---

## ⚠️ Troubleshooting

**Q: Tombol Print Resi tidak muncul?**
- A: Pastikan nomor resi sudah diisi di form "Update Data Pengiriman"

**Q: Di mana form input nomor resi?**
- A: Di halaman order detail admin → bagian "Status Pengiriman" → form "Update Data Pengiriman"

**Q: Bagaimana jika Raja Ongkir sudah terintegrasi?**
- A: Biasanya nomor resi otomatis masuk ke database saat Anda submit shipment via Raja Ongkir. Jika belum, bisa manual input juga di form admin panel.

**Q: Format nomor resi apa?**
- A: Tergantung kurir:
  - JNE: `510123456789` (12 digit)
  - POS: `EA123456789ID` (13 karakter)
  - TIKI: `000123456` (9 digit)
  - Shopee: `SE1234567890` (variasi)

---

## 📲 Saran Best Practice

1. **Saat pickup barang dari gudang:**
   - Catat nomor resi yang diberikan kurir
   - Foto/screenshot nomor resi untuk backup

2. **Update sistem segera:**
   - Input nomor resi di admin panel dalam 1 jam pickup
   - Notifikasi ke customer via email/SMS

3. **Share dengan customer:**
   - Print resi dan include di paket
   - Send tracking link via email
   - Update order page jadi customer bisa track sendiri

---

Generated: 2026-02-20
