# 🧪 Payment Gateway Testing Guide

## Setup Status

### Current Configuration (Development)
- **Active Gateway**: `mock` (default)
- **Location**: `.env` → `PAYMENT_GATEWAY=mock`
- **Purpose**: Testing payment flow without real charges

### Supported Gateways
1. ✅ **Mock** - Testing/Development (No API needed)
2. ✅ **DOKU** - Production Ready (API configured)
3. 🔄 **MindTrans** - Prepared (Waiting for approval)

---

## 🎯 Quick Start - Testing with Mock

### Step 1: Verify Configuration
```env
# .env file should have:
PAYMENT_GATEWAY=mock
APP_DEBUG=true
```

### Step 2: Run the Application
```bash
php artisan serve
```

### Step 3: Test Checkout → Payment Flow

1. **Add product to cart** (from home/product page)
2. **Go to checkout** (click cart icon)
3. **Fill shipping details**
4. **Select shipping method**
5. **Review order** and click "Lanjut ke Pembayaran"
6. **On Payment Page**: Click "Proses Pembayaran"

### Step 4: Mock Payment Checkout

In mock mode, you'll be redirected to a test checkout URL where you can:
- ✅ **APPROVE** - Simulate successful payment
- ❌ **CANCEL** - Simulate failed payment  
- ⏱️ **TIMEOUT** - Test payment expiry

### Step 5: Verify Payment

After selecting status, you'll be redirected back and can:
- Check payment status
- View order confirmation
- See order in profile/orders

---

## 🔀 Switching Payment Gateways

### To Use DOKU
```env
PAYMENT_GATEWAY=doku

# Make sure DOKU credentials are set:
DOKU_API_KEY=your_key
DOKU_SECRET_KEY=your_secret
DOKU_MODE=sandbox
DOKU_MERCHANT_ID=your_merchant_id
```

### To Use MindTrans (When Ready)
```env
PAYMENT_GATEWAY=mindtrans

# Set MindTrans credentials:
MINDTRANS_API_KEY=your_key
MINDTRANS_API_SECRET=your_secret
MINDTRANS_MODE=sandbox
MINDTRANS_MERCHANT_ID=your_merchant_id
```

### Back to Mock
```env
PAYMENT_GATEWAY=mock
```

---

## 📝 Testing Scenarios

### Scenario 1: Complete Purchase
```
1. Add product to cart
2. Checkout → Fill address → Select shipping
3. Process payment
4. Mock checkout: Click APPROVE
5. Back to app → Order marked as "processing"
✓ Payment status: Completed
✓ Order status: Processing
```

### Scenario 2: Payment Cancellation
```
1. Add product to cart
2. Checkout → Select shipping
3. Process payment
4. Mock checkout: Click CANCEL
5. Back to app → Payment marked as "cancelled"
✓ Order status: Cancelled
✓ Cart items cleared
```

### Scenario 3: Payment Investigation
```
1. Start checkout
2. Process payment
3. Do NOT complete on mock checkout
4. Navigate away from payment page
5. Go to Orders → Payment still shows "pending"
✓ Can retry payment
✓ Session status persists
```

### Scenario 4: Multiple Payments
```
1. First order: Complete payment successfully
2. Second order: Start new payment
3. Original payment amount cached in session
✓ Each payment is independent
✓ Different external IDs
```

---

## 🔍 Debugging & Logs

### View Payment Logs
```bash
# Recent payment activity
tail -f storage/logs/laravel.log | grep -i payment

# Or check storage/logs/laravel.log directly
```

### Payment Service Methods

#### Mock Service
```php
// In controller/artisan command:
$mock = new \App\Services\MockPaymentService();

// Create payment
$result = $mock->createPayment($payment, $itemDetails);

// Verify status
$status = $mock->verifyPayment($payment);

// Get testing instructions
$instructions = $mock->getTestingInstructions();
```

#### Check Active Gateway
```php
// In any controller:
$gateway = config('payment.gateway');
// Returns: 'mock', 'doku', 'mindtrans'
```

---

## 🛠️ File Structure

### New Files Created
```
app/Services/
├── MockPaymentService.php      ← Test payments (no API)
├── MindtransPaymentService.php ← MindTrans integration (prepared)
└── DokuPaymentService.php      ← DOKU integration (existing)

config/
└── payment.php                  ← Payment gateway config

.env                            ← PAYMENT_GATEWAY setting
```

### Modified Files
```
app/Http/Controllers/
├── PaymentController.php       ← Support multiple gateways
└── CheckoutController.php      ← Dynamic gateway selection

.env                            ← Payment gateway config added
```

---

## 🚀 Workflow: From Mock to Production

### Phase 1: Development (Current)
```
✓ Use Mock gateway
✓ Test all payment flows
✓ No external dependencies
✓ Instant testing
```

### Phase 2: Staging Testing
```
1. Get DOKU sandbox credentials
2. Set PAYMENT_GATEWAY=doku
3. Set DOKU_MODE=sandbox
4. Test with real DOKU sandbox
5. Verify webhook callbacks
```

### Phase 3: MindTrans Setup (When Approved)
```
1. Get MindTrans credentials
2. Set PAYMENT_GATEWAY=mindtrans
3. Set MINDTRANS_MODE=sandbox
4. Test MindTrans sandbox
5. Verify integration
```

### Phase 4: Production Deploy
```
1. Set PAYMENT_GATEWAY=doku (or mindtrans)
2. Set MODE=production
3. Use production API keys
4. Enable proper logging
5. Setup payment monitoring
```

---

## ✅ Testing Checklist

### Basic Flow
- [ ] Product add to cart works
- [ ] Checkout form validation works
- [ ] Shipping selection works
- [ ] Payment creation successful
- [ ] Mock checkout page loads
- [ ] Payment approval updates order
- [ ] Order shows in profile

### Payment Statuses
- [ ] Pending → Completed (after approval)
- [ ] Pending → Cancelled (after cancellation)
- [ ] Pending → Failed (error handling)
- [ ] Multiple orders independent payments
- [ ] Payment history visible in orders

### Error Handling
- [ ] Empty cart redirects to cart
- [ ] Invalid address shows error
- [ ] Unauthorized access blocked
- [ ] Payment errors caught gracefully
- [ ] Proper error messages shown

### Gateway Switching
- [ ] Mock works without config
- [ ] DOKU works with credentials
- [ ] MindTrans ready for credentials
- [ ] Config can be changed in .env
- [ ] No code changes needed for switching

---

## 📞 Troubleshooting

### Issue: Payment page shows error
**Solution**: Check `.env` PAYMENT_GATEWAY value and restart server
```bash
php artisan serve
```

### Issue: Mock checkout URL not working
**Solution**: Verify routes are published
```bash
php artisan optimize:clear
php artisan route:list | grep payment
```

### Issue: Payment creation fails
**Solution**: Check logs
```bash
tail -f storage/logs/laravel.log | grep -i "payment\|error"
```

### Issue: Session not persisting test status
**Solution**: Check SESSION_DRIVER in .env
```env
SESSION_DRIVER=file  # or database
```

---

## 🔐 Security Notes

### Mock Service
- ✅ Safe for development
- ✅ No real transactions
- ✅ Test data only
- ⚠️ Never use in production

### DOKU/MindTrans
- 🔒 Use sandbox for testing
- 🔒 Never commit credentials to git
- 🔒 Use environment variables
- 🔒 Verify webhook signatures

---

## 📚 References

### Config Files
- `config/payment.php` - Gateway configuration
- `.env` - Gateway selection and credentials

### Key Classes
- `MockPaymentService` - Test gateway
- `DokuPaymentService` - DOKU integration
- `MindtransPaymentService` - MindTrans integration
- `PaymentController` - Gateway abstraction

### Routes
- `POST /payment/{order}/process` - Process payment
- `GET /payment/{order}/verify` - Verify payment
- `POST /payment/callback/doku` - DOKU webhook
- `POST /payment/callback/mindtrans` - MindTrans webhook (prepared)

---

## 🎬 Next Steps

1. ✅ **Now**: Test with Mock gateway
2. ⏳ **Soon**: Test DOKU sandbox
3. ⏳ **Later**: Implement MindTrans (when approved)
4. 🚀 **Production**: Switch to production credentials

---

**Last Updated**: February 19, 2026
**Status**: Mock & DOKU Ready, MindTrans Prepared
