# ADMIN QUICK REFERENCE

## 🚀 Quick Start

### Access Admin Dashboard
1. Login as any user
2. Click profile icon → "Admin Dashboard"
3. Or direct: `/admin/dashboard`

---

## 📊 Dashboard (`/admin/dashboard`)

### What You See
- **4 Big Metrics**: Total Orders, Delivered, Pending Payment, Total Revenue
- **5 Mini Stats**: Processing, Shipped, Failed Payments, Cancelled, Pending Payments
- **Recent Orders**: Last 10 orders in table

### What You Can Do
- Click "Lihat Semua →" to view all orders

---

## 📋 Orders List (`/admin/orders`)

### Filters Available
- **Status Filter**: 
  - All / Pending / Processing / Shipped / Delivered / Cancelled
- **Search Field**: 
  - Order number (ORD-001)
  - Customer name
  - Customer phone

### For Each Order You See
| Column | Info |
|--------|------|
| No. Pesanan | Order number (amber color) |
| Pelanggan | Name + phone |
| Tanggal | Order date/time |
| Total | Total price (Rp) |
| Status | Order status (color badge) |
| Pembayaran | Payment status (color badge) |
| Pengiriman | Shipping status text |
| Aksi | "Lihat Detail" link |

### How to Use
1. Filter by status dropdown (e.g., "Diproses")
2. Search by typing order number or customer name
3. Click "Filter" button
4. Click "Lihat Detail" on any order
5. Or click "Reset" to clear filters

---

## 🔧 Order Detail (`/admin/orders/{id}`)

### 3-Part Layout

#### LEFT SIDE (Main Content)
1. **Order Status Update**
   - Dropdown: pending, processing, shipped, delivered, cancelled
   - Button: "Perbarui"

2. **Payment Status Update**
   - Dropdown: pending, paid, failed, expired
   - Button: "Perbarui Pembayaran"
   - Shows: Method, Date, Transaction ID, Amount

3. **Shipping Status Update**
   - Dropdown: pending, picked_up, in_transit, out_for_delivery, delivered, failed, returned
   - Input: Kurir (JNE, POS, TIKI, etc.)
   - Input: Nomor Resi (tracking number)
   - Button: "Perbarui Pengiriman"
   - Shows: Current courier, shipping cost, tracking number

4. **Order Items**
   - Product image
   - Product name + SKU
   - Quantity × Price
   - Subtotal

#### RIGHT SIDE (Sidebar Info)
1. **Customer Info**
   - Name, Email, Phone

2. **Shipping Address**
   - Full address with postal code

3. **Price Summary**
   - Subtotal (without shipping)
   - Ongkir (shipping cost)
   - Total Price

4. **Timeline**
   - Order created date
   - Payment received date (if paid)
   - Last updated date

---

## 🎨 Status Color Guide

### Order Status Badges
- 🟡 Yellow: "Menunggu Pembayaran" (pending)
- 🔵 Blue: "Diproses" (processing)
- 🟣 Purple: "Dikirim" (shipped)
- 🟢 Green: "Terima" (delivered)
- 🔴 Red: "Dibatalkan" (cancelled)

### Payment Status Badges
- 🟡 Yellow: "Pending"
- 🟢 Green: "Paid"
- 🔴 Red: "Failed"
- 🟠 Orange: "Expired"

---

## 📱 Typical Workflow

### Scenario: Customer placed order

1. **Order comes in as "pending"**
   - Go to `/admin/orders`
   - Filter by status: "Menunggu Pembayaran"
   - Customer sees payment page

2. **Payment received**
   - Click "Lihat Detail"
   - Under "Status Pembayaran", change to "paid"
   - Click "Perbarui Pembayaran"
   - ✅ Order now shows payment status as "Paid"

3. **Process order**
   - Under "Status Pesanan", change to "processing"
   - Click "Perbarui"
   - ✅ Order now shows as "Diproses"

4. **Prepare for shipment**
   - Under "Status Pengiriman":
     - Select "picked_up"
     - Enter Kurir: "JNE"
     - Enter Nomor Resi: "123456789"
   - Click "Perbarui Pengiriman"
   - ✅ Shipping info updated with tracking number

5. **Mark as shipped**
   - Under "Status Pesanan", change to "shipped"
   - Click "Perbarui"

6. **Delivery confirmation**
   - Under "Status Pengiriman", change to "delivered"
   - Click "Perbarui Pengiriman"
   - ✅ Order complete!

---

## 💡 Tips & Tricks

### Quick Filtering
- Filter by "Menunggu Pembayaran" to see unpaid orders
- Filter by "Diproses" to see orders being prepared
- Filter by "Dikirim" to track shipped orders

### Search Power
- Search "08123" to find orders by customer phone
- Search "ORD-" to find specific order numbers
- Search customer name to find all their orders

### Bulk Info
- Dashboard shows immediately which statuses have most orders
- Colors help spot pending/important items at a glance
- Revenue calculation is automatic (paid + delivered only)

### Tracking Updates
- Always enter tracking number when shipping status changes
- Select correct courier for accurate tracking on customer end
- Customer can see this in their order detail page

---

## ⚙️ Technical Notes

### Auto-created Records
- If payment record doesn't exist, it's created when you update payment status
- If shipping record doesn't exist, it's created when you update shipping status
- No manual DB edits needed!

### Real-time Stats
- Dashboard counts refresh on every page load
- No caching - always latest numbers

### Pagination
- Orders list shows 20 per page
- Click page numbers to browse more orders

---

## 📞 Support

If something doesn't work:
1. Make sure you're logged in
2. Check browser console (F12) for errors
3. Refresh page and try again
4. Check that order ID is correct in URL

---

## 🎯 Key Features Summary

✅ Dashboard with real-time stats
✅ Filter & search all orders
✅ Update order status
✅ Update payment status
✅ Update shipping status with tracking
✅ View customer info
✅ View shipping address
✅ View detailed items list
✅ View order timeline
✅ Responsive on mobile
✅ Color-coded status badges

---

**You're all set! Start managing orders!** 🎉
