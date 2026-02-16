# CUSTOMER VIEW - Order Tracking

## How Customers See Their Orders

Setelah admin update status, customer bisa lihat di `/orders/{id}` (profile page mereka)

### Current Customer Order Detail Page
Location: `resources/views/profile/order-detail.blade.php`

Already displays:
✅ Order number & date
✅ All items with images & prices
✅ Customer info (name, email, phone, address)
✅ Shipping address
✅ Price summary (subtotal + shipping = total)

---

## What Gets Updated When Admin Changes Status

### 1. Admin Updates Order Status
```
Example: pending → processing
```
**Customer sees:**
```
STATUS: Diproses (Processing)
Badge color: 🔵 Blue
```

### 2. Admin Updates Payment Status
```
Example: pending → paid
```
**Customer sees:**
```
PEMBAYARAN: Terbayar (Paid)
Tanggal Pembayaran: [Date] 
No. Transaksi: [Transaction ID]
```

### 3. Admin Updates Shipping Status with Tracking
```
Example: pending → in_transit
Kurir: JNE
Tracking: ABC123456
```
**Customer sees:**
```
PENGIRIMAN: Dalam Pengiriman
Kurir: JNE
Resi: ABC123456
```

---

## Current Display on Customer Order Page

### Payment Section
```
═══════════════════════════════════
    INFORMASI PEMBAYARAN
═══════════════════════════════════
Status: [Paid/Pending/Failed]
Metode: [Payment Method]
Tanggal: [Date if paid]
No. Transaksi: [Transaction ID]
Jumlah: Rp[Amount]
```

### Shipping Section
```
═══════════════════════════════════
    INFORMASI PENGIRIMAN
═══════════════════════════════════
Status: [Pengiriman Status]
Kurir: [JNE/POS/TIKI]
Resi/Tracking: [Tracking Number]
Biaya: Rp[Shipping Cost]
```

### Order Timeline
```
📍 Pesanan Dibuat: 2025-02-10 10:30
📍 Pembayaran Diterima: 2025-02-10 11:00 (only if paid)
📍 Terakhir Diupdate: 2025-02-10 12:15
```

---

## Live Update Flow

```
┌─────────────────────────────────────────────┐
│ Customer places order                       │
│ Status: pending                             │
│ Payment: pending                            │
│ Shipping: pending                           │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│ Admin updates → Payment Status = Paid       │
└─────────────────────────────────────────────┘
                    ↓
Customer sees: ✓ Pembayaran: Terbayar
              + Tanggal Pembayaran: [now]
              + No. Transaksi: [method]
                    
                    ↓

┌─────────────────────────────────────────────┐
│ Admin updates → Order Status = Processing   │
└─────────────────────────────────────────────┘
                    ↓
Customer sees: STATUS: Diproses (Blue badge)

                    ↓

┌─────────────────────────────────────────────┐
│ Admin updates → Shipping Status = Picked Up │
│ Adds HND Kurir, Tracking 123456789          │
└─────────────────────────────────────────────┘
                    ↓
Customer sees: PENGIRIMAN: Diambil Kurir
              + Kurir: JNE
              + Resi: 123456789
                    
                    ↓

┌─────────────────────────────────────────────┐
│ Admin updates → Order Status = Shipped      │
└─────────────────────────────────────────────┘
                    ↓
Customer sees: STATUS: Dikirim (Purple badge)

                    ↓

┌─────────────────────────────────────────────┐
│ Admin updates → Shipping Status = Delivered │
└─────────────────────────────────────────────┘
                    ↓
Customer sees: PENGIRIMAN: Terkirim ✓
              + Timeline shows delivery complete
```

---

## Sample Customer Order View (After Updates)

```
╔═══════════════════════════════════════════════════╗
║           PESANAN ORD-001-2025-001               ║
║  Dipesan pada: 10 Feb 2025, 10:30                ║
╚═══════════════════════════════════════════════════╝

STATUS PESANAN:
  🟣 Dikirim
  (Pesanan sudah dikirim oleh admin)

INFORMASI PEMBAYARAN:
  Status: ✓ Terbayar
  Metode Pembayaran: DOKU
  Tanggal Pembayaran: 10 Feb 2025, 11:00
  No. Transaksi: TRX-123456789
  Jumlah: Rp500.000

INFORMASI PENGIRIMAN:
  Status: Dalam Pengiriman
  Kurir: JNE
  Nomor Resi: 123456789ABCD
  Biaya Pengiriman: Rp50.000

TIMELINE:
  📍 Order Created: 10 Feb 2025, 10:30
  📍 Payment Received: 10 Feb 2025, 11:00
  📍 Last Updated: 10 Feb 2025, 14:45

ITEMS:
  [Product 1] Qty: 2 × Rp100.000 = Rp200.000
  [Product 2] Qty: 1 × Rp300.000 = Rp300.000

SUMMARY:
  Subtotal: Rp500.000
  Ongkir: Rp50.000
  ─────────────────
  TOTAL: Rp550.000
```

---

## Key Points

✅ **Real-time**: Customer sees updates immediately when admin saves
✅ **Complete Info**: All payment, shipping, and order details visible
✅ **Tracking**: Customer can use tracking number to track package
✅ **Timeline**: Shows progression of order from creation to delivery
✅ **Status Badges**: Color-coded for quick understanding
✅ **Address**: Full delivery address shown for verification

---

## What Customer Can Do

From order detail page, customer can:
- ✅ View full order details
- ✅ Check payment status
- ✅ See tracking number & courier
- ✅ View shipping address
- ✅ See items list with images
- ✅ View final pricing

**What customer CANNOT do:**
- ❌ Modify order (only view)
- ❌ Change status (admin only)
- ❌ Update payment method
- ❌ Change shipping address (locked after order)

---

## Admin Actions → Customer Updates

| Admin Does | Customer Sees |
|-----------|--------------|
| Change order status to "processing" | ORDER STATUS: Diproses 🔵 |
| Mark payment as "paid" | PEMBAYARAN: Terbayar ✓ |
| Add shipping tracking | RESI: [Tracking Number] |
| Change to "shipped" | ORDER STATUS: Dikirim 🟣 |
| Mark as "delivered" | PENGIRIMAN: Terkirim ✓ |

---

## Future Enhancements

Optional improvements for customer email/SMS notifications:
- ✉️ Email when payment confirmed
- ✉️ Email when order starts processing
- 📱 SMS when order shipped with tracking link
- 📧 Email when delivered

(These would be in a separate notification system)

---

## Summary

**Admin Panel** (/admin/orders/{id})
→ Updates order/payment/shipping status
↓
**Database** (Order, Payment, Shipping tables)
↓
**Customer Page** (/orders/{id})
→ Customer sees latest status

Everything is **live and synchronized**! ✨

When admin updates, customer sees it immediately on refresh.
