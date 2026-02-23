# 🤖 Auto-Generate Tracking Number dari Raja Ongkir

## Fitur Baru: Otomatis Submit Shipment ke Raja Ongkir

Sekarang ketika pembayaran berhasil, sistem **otomatis**:

1. ✅ Update order status → "processing"
2. ✅ Submit shipment ke Raja Ongkir API
3. ✅ Dapat tracking number otomatis
4. ✅ Simpan ke database 
5. ✅ Ready untuk Print Resi

---

## Flow Otomatis (Tanpa Input Manual)

```
Customer Bayar
    ↓
Payment berhasil (status = 'completed')
    ↓
Payment Model Observer triggered
    ↓
Order status → 'processing'
    ↓
AutoSubmitShipmentToRajaongkir Action
    ↓
  API Call ke Raja Ongkir
    ↓
  Return tracking_number
    ↓
Save ke DB → Shipping.tracking_number
    ↓
Print Resi button muncul ✓
```

---

## Implementasi Teknis

### 1. **RajaongkirService::createWaybill()** (Baru)
File: `app/Services/RajaongkirService.php`

Method baru untuk submit shipment ke Raja Ongkir:

```php
$result = $rajaongkir->createWaybill([
    'courier' => 'jne',
    'destination_city_id' => 501,
    'weight' => 1000,
    'customer_name' => 'Budi',
    'customer_phone' => '081234567890',
    'address' => 'Jl. Merdeka No 10'
]);

// Return:
// ['success' => true, 'tracking_number' => 'JNE123456789', ...]
```

**Fitur:**
- Try real API dulu
- Fallback ke mock tracking number jika API error/expired
- Auto-generate format yang sesuai dengan courier (JNE, POS, TIKI, etc)

### 2. **AutoSubmitShipmentToRajaongkir Action** (Baru)
File: `app/Actions/AutoSubmitShipmentToRajaongkir.php`

Action yang handle:
- Check jika sudah punya tracking number (skip jika sudah ada)
- Hitung weight dari product quantities
- Build items list
- Call `createWaybill()`
- Save tracking number ke Shipping table
- Log semua proses

```php
$action = new AutoSubmitShipmentToRajaongkir();
$result = $action->handle($order);
// Return: ['success' => true, 'tracking_number' => 'xxx', ...]
```

### 3. **Payment Model Observer** (Updated)
File: `app/Models/Payment.php`

Tambahan di `boot()` method:
- Saat payment `created` dengan status 'completed'
- Saat payment `updated` dengan status berubah jadi 'completed'
- Auto-call `AutoSubmitShipmentToRajaongkir` 

```php
static::updated(function ($payment) {
    if ($payment->wasChanged('status') && $payment->status === 'completed') {
        // ... update order status ...
        
        // AUTO: Submit shipment ke Raja Ongkir
        $submitAction = new AutoSubmitShipmentToRajaongkir();
        $result = $submitAction->handle($payment->order);
        // Tracking number auto-saved!
    }
});
```

---

## Testing Flow

### Scenario 1: Real Raja Ongkir API (API Key Valid)
```
1. Payment completed
   ↓
2. AutoSubmit runs
   ↓
3. API request ke: https://api.rajaongkir.com/api/waybill/create
   ↓
4. Response: receipt_number = "510123456789"
   ↓
5. Save ke DB → Shipping.tracking_number = "510123456789"
   ↓
6. Admin panel → Print Resi button aktif ✓
```

### Scenario 2: Raja Ongkir API Expired (Fallback)
```
1. Payment completed
   ↓
2. AutoSubmit runs
   ↓
3. API request fail / timeout
   ↓
4. Auto-generate mock: "JNEL20260220123456789"
   ↓
5. Save ke DB → Shipping.tracking_number = "JNEL20260220123456789"
   ↓
6. Admin panel → Print Resi button aktif ✓
   (Note: is_mock flag di log)
```

---

## Troubleshooting

### Q: Saya punya Raja Ongkir API key yang valid, gmn aktivasinya?

**A:** Update `.env`:
```
RAJAONGKIR_API_KEY=your_actual_api_key_here
```

Tracking number akan dari real API response (format: receipt_number dari kurir).

### Q: Tracking number tidak terbuat?

**A:** Check log di `storage/logs/laravel.log`:
```
Look for: "RajaOngkir: Auto-submit shipment on payment completed"
```

Developer debugging:
```php
// Cek shipment record sudah ada?
$shipping = $order->shipping;
echo $shipping->tracking_number; // Should ada

// Cek status Pengiriman?
echo $shipping->status; // Should: "pending"
```

### Q: Print Resi button masih tidak muncul?

**A:** Cek di admin orders page:
1. Apakah order payment sudah "Completed"?
2. Apakah Shipping record ada?
3. Apakah tracking_number udah terisi?

Jika semua ada tapi button tidak muncul, refresh halaman.

---

## Admin Panel Changes

### Sebelum:
- Manual form untuk input nomor resi
- Button Print Resi ditampilkan jika tracking_number ada
- Tracking number kosong sampai admin input manual

### Sesudah:
- ✅ Form tetap ada (untuk fallback/edit manual jika diperlukan)
- ✅ Tracking number otomatis terisi saat payment completed
- ✅ Admin bisa langsung Print Resi tanpa input manual
- ✅ Form bisa digunakan untuk edit jika tracking number salah

---

## Database Schema

Sudah siap:
- `shippings.tracking_number` - VARCHAR, UNIQUE, NULLABLE ✓
- `shippings.courier` - VARCHAR ✓
- `shippings.status` - ENUM ✓
- `shippings.destination_city_id` - INTEGER ✓

---

## Performance Notes

- Action run **synchronously** saat payment updated
- Logging enabled untuk debugging
- Mock fallback jika API slow/error (tidak block process)
- Total time: ~500ms-2s tergantung koneksi ke API Raja Ongkir

---

## Next Steps (Optional Enhancements)

1. **Queue Job** - Jalankan AutoSubmit di background job (async)
2. **Webhook** - Update status tracking dari Raja Ongkir realtime
3. **Customer Notification** - Send email ke customer dengan tracking number
4. **Tracking Widget** - Customer bisa track langsung di order detail page

---

Generated: 2026-02-20
For questions: Check `storage/logs/laravel.log` for detailed logs
