# Dokumentasi Payment Gateway & Shipping Integration

**Status: READY TO IMPLEMENT** ✅

---

## 📋 Ringkasan Implementasi

Sistem checkout, pembayaran (Doku), dan shipping cost (Rajaongkir) telah **SIAP DIIMPLEMENTASIKAN**. Dokumentasi ini menjelaskan setup, penggunaan, dan integrasi dengan payment gateway Doku dan shipping API Rajaongkir.

---

## 🔧 Setup & Konfigurasi

### 1. Environment Variables

Tambahkan ke file `.env`:

```env
# Payment Gateway - DOKU
DOKU_API_KEY=your_doku_api_key_here
DOKU_SECRET_KEY=your_doku_secret_key_here
DOKU_MODE=sandbox
DOKU_MERCHANT_ID=your_doku_merchant_id_here

# Shipping API - Rajaongkir
RAJAONGKIR_API_KEY=your_rajaongkir_api_key_here
RAJAONGKIR_ACCOUNT_TYPE=starter
RAJAONGKIR_ORIGIN_CITY_ID=501
```

**Dapatkan API Keys:**
- **Doku**: https://dashboard.doku.com (Sign up → Get API Keys)
- **Rajaongkir**: https://rajaongkir.com (Free account untuk testing)

### 2. Database Migration

Jalankan migration untuk membuat tabel payment dan shipping:

```bash
php artisan migrate
```

Ini akan membuat:
- `payments` table - menyimpan data pembayaran
- `shippings` table - menyimpan data pengiriman

### 3. Config Files

Config files sudah dibuat otomatis:
- `config/doku.php` - Konfigurasi Doku
- `config/rajaongkir.php` - Konfigurasi Rajaongkir

---

## 🛒 Checkout Flow

### User Flow:

1. **Browse & Add to Cart** (sudah ada)
   - User browsing produk
   - Add to cart

2. **Go to Checkout** (BARU)
   - User klik "Checkout" di cart page
   - Route: `/checkout`

3. **Fill Shipping Address** (BARU)
   - Input nama, telepon, alamat
   - Pilih provinsi & kota
   - Otomatis load shipping costs dari Rajaongkir

4. **Select Shipping Method** (BARU)
   - Select dari opsi kurir (JNE, Pos, TIKI)
   - Lihat ongkos kirim & estimasi pengiriman
   - Total harga update otomatis

5. **Create Order** (BARU)
   - Click "Checkout" button
   - Order dibuat dengan status `pending`
   - Payment + Shipping record dibuat
   - Cart di-clear

6. **Payment Page** (BARU)
   - Route: `/payment/{order}`
   - Tampil payment details
   - Click "Lanjut ke Pembayaran"

7. **Doku Payment Gateway** (BARU)
   - Redirect ke Doku checkout
   - User pilih metode pembayaran:
     - Transfer Bank Virtual Account
     - QRIS
     - E-Wallet
     - Bank Transfer
   - Bayar
   - Redirect kembali ke payment verification page

8. **Order Confirmation** (BARU)
   - Payment diverifikasi
   - Order status update ke `processing`
   - Email confirmation dikirim
   - Admin notified untuk pack & ship

---

## 📁 Struktur File yang Ditambahkan

```
app/
  Services/
    ├── RajaongkirService.php      # Integrasi Rajaongkir API
    └── DokuPaymentService.php     # Integrasi Doku Payment API
  
  Http/Controllers/
    ├── CheckoutController.php     # Checkout logic
    └── PaymentController.php      # Payment logic
  
  Models/
    ├── Payment.php                # Payment model
    └── Shipping.php               # Shipping model

config/
  ├── doku.php                     # Doku configuration
  └── rajaongkir.php              # Rajaongkir configuration

database/migrations/
  ├── 2026_02_10_000001_create_payments_table.php
  └── 2026_02_10_000002_create_shippings_table.php

resources/views/
  checkout/
    └── show.blade.php            # Checkout page
  
  payment/
    └── show.blade.php            # Payment page
```

---

## 🔗 Routes Reference

### Checkout Routes

```php
// GET - Show checkout form
GET /checkout → CheckoutController@show (auth required)

// POST - Process checkout (create order)
POST /checkout → CheckoutController@process (auth required)

// AJAX - Get cities by province
GET /checkout/cities → CheckoutController@getCities (auth required)

// AJAX - Get shipping costs
GET /checkout/shipping-costs → CheckoutController@getShippingCosts (auth required)
```

### Payment Routes

```php
// GET - Show payment page
GET /payment/{order} → PaymentController@show (auth required)

// POST - Process payment (redirect to Doku)
POST /payment/{order}/process → PaymentController@process (auth required)

// GET - Verify after return from Doku
GET /payment/{order}/verify → PaymentController@verify (auth required)

// GET - Check payment status (AJAX)
GET /payment/{order}/status → PaymentController@checkStatus (auth required)

// POST - Cancel payment
POST /payment/{order}/cancel → PaymentController@cancel (auth required)

// POST - Webhook callback from Doku
POST /payment/callback/doku → PaymentController@callback (no auth)
```

---

## 💳 Doku Payment Gateway

### Fitur yang Tersedia:

- ✅ Virtual Account (semua bank)
- ✅ QRIS (quick response code)
- ✅ E-Wallet (GCash, OVO, Dana, dll)
- ✅ Bank Transfer Direct

### Payment Flow:

1. **Create Payment Request**
   ```php
   $paymentService = new DokuPaymentService();
   $result = $paymentService->createPayment($payment, $itemDetails);
   // returns: ['success' => true, 'checkout_url' => '...']
   ```

2. **Redirect to Doku**
   ```php
   return redirect()->to($result['checkout_url']);
   ```

3. **User Complete Payment**
   - User di Doku checkout page
   - Pilih metode pembayaran
   - Selesaikan pembayaran

4. **Callback Verification**
   ```php
   // Doku will POST to /payment/callback/doku
   // PaymentController@callback handles it
   // Update payment status automatically
   ```

5. **Return to App**
   - Redirect kembali ke /payment/{order}/verify
   - Verify status dan show confirmation

### Payment Statuses:

- `pending` - Menunggu pembayaran
- `processing` - Sedang diproses
- `completed` - Lunas
- `failed` - Gagal
- `expired` - Kadaluarsa
- `cancelled` - Dibatalkan
- `refunded` - Dikembalikan

---

## 🚚 Rajaongkir Shipping Integration

### Fitur yang Tersedia:

- ✅ Get provinces list
- ✅ Get cities by province
- ✅ Calculate shipping cost
- ✅ Multiple couriers (JNE, Pos, TIKI)
- ✅ Estimate delivery time

### Shipping Flow:

1. **Load Provinces**
   ```php
   $provinces = $rajaongkir->getProvinces();
   // Cached untuk 1 jam
   ```

2. **Load Cities by Province**
   ```php
   $cities = $rajaongkir->getCitiesByProvince($provinceId);
   // Load saat user select province
   ```

3. **Calculate Shipping Cost**
   ```php
   $costs = $rajaongkir->getShippingCosts(
       $destinationCityId,
       $weightInGrams,
       ['jne', 'pos', 'tiki']
   );
   // Returns: [
   //   ['courier_code' => 'jne', 'cost' => 100000, 'estimated_days' => '3-5'],
   //   ...
   // ]
   ```

4. **Create Shipping Record**
   ```php
   Shipping::create([
       'order_id' => $order->id,
       'courier' => 'jne',
       'service' => 'REG',
       'cost' => 100000,
       'weight' => 1500,
       'status' => 'pending',
       ...
   ]);
   ```

### Supported Couriers:

| Code | Name | Services |
|------|------|----------|
| jne | JNE | REG, OKE |
| pos | Pos Indonesia | REG |
| tiki | TIKI | REG, ECO |

### Shipping Statuses:

- `pending` - Menunggu pickup
- `picked_up` - Sudah diambil
- `in_transit` - Sedang dalam perjalanan
- `out_for_delivery` - Sedang di anter
- `delivered` - Terima
- `failed` - Gagal dikirim
- `returned` - Dikembalikan

---

## 🎯 Model Relationships

### Order Model

```php
$order->payment()      // HasOne Payment
$order->shipping()     // HasOne Shipping
$order->items()        // HasMany OrderItem
$order->user()         // BelongsTo User
```

### Payment Model

```php
$payment->order()      // BelongsTo Order
$payment->isPaid()     // Check if paid
```

### Shipping Model

```php
$shipping->order()     // BelongsTo Order
```

---

## 📊 Database Schema

### Payments Table

```sql
-- Key columns
id                 - Primary key
order_id           - Foreign key ke orders
amount             - Amount dalam IDR
currency           - Currency (default: IDR)
payment_method     - Metode pembayaran (VIRTUAL_ACCOUNT, QRIS, etc)
payment_gateway    - Gateway yang digunakan (doku, stripe, etc)
external_id        - Reference dari payment gateway (unique)
status             - Payment status
reference_number   - Our internal reference (unique)
transaction_date   - Tanggal transaksi
paid_at            - Waktu pembayaran berhasil
metadata           - JSON data dari gateway
```

### Shippings Table

```sql
-- Key columns
id                 - Primary key
order_id           - Foreign key ke orders
courier            - Courier code (jne, pos, tiki)
courier_name       - Display name
service            - Service code (REG, OKE, etc)
cost               - Shipping cost dalam IDR
weight             - Total weight dalam grams
origin_city_id     - Origin city from Rajaongkir
destination_city_id - Destination city
tracking_number    - Nomor resi (unique when available)
status             - Shipping status
estimated_delivery - Estimasi waktu tiba
actual_delivery    - Waktu actual tiba
notes              - Catatan pengiriman
```

---

## 🔐 Security

### API Keys Encryption:

Simpan API keys di `.env` (sudah aman):
```env
DOKU_API_KEY=xxx
DOKU_SECRET_KEY=xxx
RAJAONGKIR_API_KEY=xxx
```

### Callback Verification:

Doku webhook signature diverifikasi:
```php
$isValid = $dokuService->verifyCallbackSignature($payload, $signature);
```

### Authorization:

Semua routes dilindungi dengan auth middleware kecuali callback:
```php
Route::middleware('auth')->group(...) // Protected
Route::post('/payment/callback/doku', ...)->withoutMiddleware('auth') // Webhook
```

---

## 🧪 Testing

### Test Checkout Flow:

1. Login dengan user account
2. Add produk ke cart
3. Go to `/checkout`
4. Fill shipping info
5. Select shipping method
6. Click "Checkout"
7. Check database:
   ```bash
   php artisan tinker
   > Order::latest()->first()
   > Payment::latest()->first()
   > Shipping::latest()->first()
   ```

### Test Payment Flow:

1. Go to `/payment/{order_id}`
2. Click "Lanjut ke Pembayaran"
3. Di Doku sandbox, gunakan test card:
   - **Card**: 4111111111111111
   - **Expire**: Any future date
   - **CVV**: Any number

### Test Webhook Callback:

```bash
# Using PostMan
POST http://localhost:8000/payment/callback/doku
Content-Type: application/json

{
  "invoice_id": "ORDER-xxx",
  "status": "COMPLETED",
  "amount": 100000,
  "signature": "..."
}
```

---

## 📱 Frontend Integration

### Checkout Page:

```html
<!-- resources/views/checkout/show.blade.php -->
- Form untuk shipping address
- AJAX load provinces → cities
- AJAX load shipping costs
- Select shipping method
- Price calculation
- Submit to create order
```

### Payment Page:

```html
<!-- resources/views/payment/show.blade.php -->
- Payment status display
- Payment details
- Button redirect ke Doku
- Return verification
- Status checking
```

### Order Detail Page:

```html
<!-- resources/views/profile/order-detail.blade.php -->
- Payment status & details
- Shipping info & tracking
- Timeline dengan status updates
- Address confirmation
```

---

## ⚙️ Admin Features (Next Phase)

Fitur berikut bisa ditambahkan di Filament admin panel:

1. **Payment Management**
   - View all payments
   - Mark as completed/failed
   - Refund processing
   - Payment reports

2. **Shipping Management**
   - Update tracking number
   - Change shipping status
   - Print shipping labels
   - Batch operations

3. **Order Management**
   - Change order status
   - Update shipping info
   - Send notifications
   - Export orders

---

## 🚀 Next Steps

### Phase 1 (Current):
- ✅ Payment Gateway (Doku)
- ✅ Shipping Cost (Rajaongkir)
- ✅ Checkout Flow
- ✅ Order Management

### Phase 2 (Recommended):
- [ ] Admin dashboard untuk shipping tracking
- [ ] Email notifications
- [ ] SMS notifications
- [ ] Real-time shipping status updates
- [ ] Return/Refund management
- [ ] Payment failure handling
- [ ] Abandoned cart recovery

### Phase 3 (Enhancement):
- [ ] Multiple payment gateways
- [ ] Subscription/recurring billing
- [ ] Installment options
- [ ] Discount/coupon system
- [ ] Invoice generation
- [ ] Advanced analytics

---

## 📞 Support & Troubleshooting

### Common Issues:

**API Keys tidak valid**
- Check `.env` file
- Verify keys di Doku/Rajaongkir dashboard
- Make sure mode (sandbox/production) sesuai

**Shipping costs tidak muncul**
- Verify Rajaongkir API key
- Check account type (starter/basic/pro)
- Pastikan city_id valid

**Payment callback tidak diterima**
- Configure callback URL di Doku dashboard
- Check firewall rules
- Verify webhook signature

**Migrations error**
- Drop & recreate tables:
  ```bash
  php artisan migrate:reset
  php artisan migrate
  ```

---

## 📚 References

- [Doku Documentation](https://docs.doku.com)
- [Rajaongkir API](https://rajaongkir.com/api)
- [Laravel Callables](https://laravel.com/docs/callable-string-callbacks)

---

**Last Updated**: February 10, 2026
**Status**: Ready for Implementation ✅
