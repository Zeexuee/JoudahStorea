# 🚀 Quick Start Guide - Payment & Shipping Setup

## ⚡30-Minute Setup

### Step 1: Configure API Keys (5 min)

1. **Get Doku API Keys**
   - Go to https://dashboard.doku.com
   - Sign up if needed
   - Get `API_KEY` dan `SECRET_KEY`

2. **Get Rajaongkir API Key**
   - Go to https://rajaongkir.com
   - Sign up for FREE account (Starter plan)
   - Get `API_KEY`

3. **Update `.env`**
   ```env
   DOKU_API_KEY=your_key_here
   DOKU_SECRET_KEY=your_secret_here
   DOKU_MODE=sandbox
   DOKU_MERCHANT_ID=your_merchant_id
   
   RAJAONGKIR_API_KEY=your_key_here
   RAJAONGKIR_ORIGIN_CITY_ID=501
   ```

### Step 2: Run Migrations (2 min)

```bash
php artisan migrate
```

This creates:
- `payments` table
- `shippings` table

### Step 3: Test the Flow (10 min)

1. **Start Server**
   ```bash
   php artisan serve
   ```

2. **Login & Add Products to Cart**
   - Go to http://localhost:8000
   - Login
   - Add products to cart

3. **Go to Checkout**
   - Click "Checkout" button (new!)
   - Fill shipping address
   - Select province → city
   - See shipping costs appear
   - Select shipping method
   - Click "Checkout"

4. **Make Payment**
   - You'll see payment page
   - Click "Lanjut ke Pembayaran"
   - Redirect to Doku
   - Use test card: `4111111111111111`
   - Complete payment

5. **Check Database**
   ```bash
   php artisan tinker
   > Order::latest()->first()
   > Payment::latest()->first()  
   > Shipping::latest()->first()
   ```

### Step 4: Configure Doku Webhook (3 min)

1. Go to Doku Dashboard → Settings
2. Add Webhook URL:
   ```
   https://yourdomain.com/payment/callback/doku
   ```
3. Select `payment.completed` event
4. Save

---

## 📋 API Integration Checklist

### Cart Updates
- ✅ Add "Checkout" button di cart page
- ✅ Required: User harus logged in untuk checkout
- ✅ Cart akan di-clear saat order dibuat

### Checkout Page
- ✅ Form untuk shipping address
- ✅ Province/City selector dengan AJAX
- ✅ Shipping costs calculator
- ✅ Order creation logic

### Payment Page
- ✅ Payment details display
- ✅ Doku redirect logic
- ✅ Payment verification
- ✅ Status checking

### Order Detail Page
- ✅ Payment status & details
- ✅ Shipping info & cost
- ✅ Timeline dengan status updates

---

## 🧪 Test Scenarios

### Scenario 1: Successful Payment
```
1. User add products → checkout
2. Select shipping → complete checkout
3. See payment page
4. Click "Lanjut ke Pembayaran"
5. Use test card 4111111111111111
6. Complete payment
7. Return to app → payment success
8. Order status: processing
9. Payment status: completed
```

### Scenario 2: Failed Payment
```
1. User reach payment page  
2. Click "Lanjut ke Pembayaran"
3. Keep test card field empty (fail)
4. Return to app → payment failed
5. See "Coba Lagi" button
6. Option to cancel order
```

### Scenario 3: Multiple Shipping Options
```
1. User at checkout page
2. Select Jawa Barat → Bandung
3. See 3 options:
   - JNE REG: Rp 50,000 (3 hari)
   - Pos REG: Rp 45,000 (4 hari)
   - TIKI ECO: Rp 40,000 (5 hari)
4. Select ongkos terkecil → price update
5. Complete checkout
```

---

## 🔧 Common Customizations

### Add Origin City Selection
Currently hardcoded to Jakarta (501). To make dynamic:

```php
// In config/rajaongkir.php
'origin_city_id' => env('RAJAONGKIR_ORIGIN_CITY_ID', 501),

// admin panel (Filament)
// Add setting untuk origin city
```

### Change Weight Calculation
Default: 500g per item. Di [CartController](app/Http/Controllers/CheckoutController.php):

```php
// Di CheckoutController.php line ~120
$weight = $cartItems->sum(function ($item) {
    return ($item->product->weight ?? 500) * $item->quantity;
});
```

Tambahkan `weight` column ke products table jika ingin per-product weight.

### Add Discount/Coupon
Di CheckoutController, sebelum create order:

```php
$discountAmount = $this->applyDiscount($validated['coupon_code']);
$total = $subtotal + $shippingCost - $discountAmount;
```

### Email Notifications
Saat payment completed:

```php
// Di DokuPaymentService.php processCallback()
Mail::send(new OrderPaidNotification($order));
Mail::send(new OrderConfirmationAdmin($order));
```

---

## 📊 Database Queries

### Get All Orders with Payments
```php
$orders = Order::with(['payment', 'shipping', 'items'])->paginate(10);
```

### Find Orders dengan Status Pending Payment
```php
$pending = Order::whereHas('payment', function($q) {
    $q->where('status', 'pending');
})->get();
```

### Get Shipping Costs by Courier
```php
$jneShippings = Shipping::where('courier', 'jne')->sum('cost');
```

### Get Total Revenue
```php
$revenue = Payment::where('status', 'completed')->sum('amount');
```

---

## 🐛 Debug Commands

```bash
# Check latest order
php artisan tinker
> Order::latest()->with('payment', 'shipping')->first()

# Check payment status
> Payment::where('order_id', 1)->first()

# Check shipping info
> Shipping::where('order_id', 1)->first()

# Test Rajaongkir API
> app('RajaongkirService')->getProvinces()

# Test Doku Connection
> app('DokuPaymentService')->createPayment(...)
```

---

## 📈 Monitoring

### Payment Success Rate
```php
$completed = Payment::where('status', 'completed')->count();
$total = Payment::count();
$rate = ($completed / $total) * 100;
```

### Average Shipping Cost
```php
$avgCost = Shipping::avg('cost');
```

### Popular Couriers
```php
Shipping::select('courier', \DB::raw('count(*) as count'))
    ->groupBy('courier')
    ->orderByDesc('count')
    ->get();
```

---

## 🎯 Next Features to Add

1. **Admin Dashboard**
   - View all payments
   - View all shipments
   - Manual status updates

2. **Email Notifications**
   - Order confirmation
   - Payment received
   - Order shipped
   - Order delivered

3. **SMS Notifications**
   - Payment reminder
   - Shipping update
   - Delivery notification

4. **Advanced Features**
   - Multiple payment gateways
   - Installment plans
   - Subscription orders
   - Inventory management

---

## 💡 Pro Tips

1. **Increase Payment Methods**
   - Configure Doku untuk enable lebih banyak payment methods
   - QRIS (most popular in Indonesia)
   - E-wallets untuk mobile users

2. **Optimize Shipping**
   - Sering-sering check rates
   - Negotiate dengan kurir untuk bulk shipments
   - Gunakan local couriers untuk same-day delivery

3. **Improve Conversion**
   - Show shipping cost early
   - Multiple payment options
   - Clear status updates
   - Good customer support

4. **Cost Optimization**
   - Cache rajaongkir results
   - Batch webhook callbacks
   - Monitor API usage

---

**Status**: ✅ Ready to Use
**Documentation Version**: 1.0
**Last Updated**: Feb 10, 2026
