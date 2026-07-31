# Mock Payment Verification Testing Guide

## Perbaikan yang Sudah Dilakukan

### 1. **Improved Error Handling in verifyMock()**
- ✅ Auth check dipindahkan ke awal (sebelum lookup Payment)
- ✅ Semua error cases redirect ke halaman yang valid (/orders)
- ✅ Fallback handling jika redirect route fails
- ✅ Comprehensive logging untuk debugging

### 2. **Route Cache Cleared**
- ✅ Route cache cleared 
- ✅ Application cache cleared
- ✅ Route `/payment/verify-mock` sudah terdaftar dan accessible

---

## Testing Flow

### Step 1: Login
```
1. Akses http://127.0.0.1:8000
2. Login dengan akun yang sudah dibuat
3. Verify session aktif di browser
```

### Step 2: Create Order & Payment
```
1. Add product ke cart
2. Go to /cart
3. Checkout → fill semua info
4. Press "Checkout" button
5. Akan redirect ke /payment/{order_id}
```

### Step 3: Initialize Mock Payment
```
1. Di payment page, scroll ke "Mock Payment Testing" section
2. Klik tombol "Lanjut ke Pembayaran" 
3. Akan redirect ke mock-checkout page dengan external_id
4. Verify URL seperti:
   http://127.0.0.1:8000/payment/mock-checkout/MOCK-14-XXXXXXX
```

### Step 4: Approve Payment (Testing)
```
1. Di mock-checkout page, klik "APPROVE PAYMENT"
2. Will show processing animation
3. After 1.5 seconds, should redirect to:
   /payment/verify-mock?external_id=MOCK-14-XXXXXXX&status=approved
```

### Step 5: Verify Success
```
✓ Payment should be processed
✓ Should redirect to /orders/{order_id} page
✓ Success message should show
✓ Order status should be "processing"
✓ Payment status should be "completed"
```

---

## Troubleshooting

### Issue: "Pembayaran tidak ditemukan" (Payment Not Found)
**Meaning**: Payment record dengan external_id tidak ditemukan
**Causes**:
1. Payment record tidak pernah di-create di step 3
2. external_id tidak updated ke Payment record
3. Database issue

**Debug**:
- Check logs: `storage/logs/laravel.log`
- Look for: "Mock Payment Verify Request" log entry
- Check if "Payment not found by external_id" appears

### Issue: "Silakan login terlebih dahulu" (Please Login First)
**Meaning**: Session lost atau user not authenticated
**Causes**:
1. Browser doesn't have session cookie
2. Session timeout

**Debug**:
- Clear browser cookies
- Login again
- Try fresh session

### Issue: 404 Not Found on verify-mock
**Meaning**: HTTP 404 error instead of redirect
**Causes**:
1. Route not registered (unlikely after cache clear)
2. Middleware blocking request
3. Exception thrown (caught by error handler)

**Debug**:
- Check route is registered: `php artisan route:list | grep verify-mock`
- Check logs for exceptions
- Verify no custom error handlers interfering

### Issue: Redirect to login instead of processing
**Meaning**: verifyMock() reaching auth check when should be already authenticated
**Causes**:
1. Session lost between mock-checkout and verify-mock
2. Browser cookies disabled

**Debug**:
- Ensure cookies enabled in browser
- Restart browser
- Try incognito window

---

## Logs Location

### Real-time Logs
```bash
tail -f storage/logs/laravel.log
```

### Key Log Entries to Look For

**Successful Flow**:
```
[timestamp] local.INFO: Mock Payment Verify Request {"external_id":"MOCK-14-XXXXXXX","status":"approved","is_authenticated":true,"user_id":1}
[timestamp] local.INFO: Mock Payment Approved {"order_id":1,"external_id":"MOCK-14-XXXXXXX","user_id":1}
```

**Error Cases**:
```
[timestamp] local.INFO: Mock Payment Verification: User not authenticated
[timestamp] local.INFO: Mock Payment Verification: Payment not found {"external_id":"MOCK-14-XXXXXXX",...}
[timestamp] local.INFO: Mock Payment Verification: Order not found
[timestamp] local.INFO: Mock Payment Verification: Unauthorized user
```

---

## Database Verification

```sql
-- Check Payment records exist
SELECT id, external_id, status, order_id FROM payments LIMIT 10;

-- Check Order status
SELECT id, order_number, status FROM orders LIMIT 10;

-- Check specific payment by external_id
SELECT * FROM payments WHERE external_id = 'MOCK-14-XXXXXXX';
```

---

## Manual Testing via Artisan Tinker

```bash
php artisan tinker

# Find test user
$user = User::first();

# Find or create order
$order = $user->orders()->first();

# Get payment
$payment = $order->payment;

# Check if payment has external_id
$payment->external_id; // Should show something like MOCK-14-XXXXXX

# If external_id is null, create one
$payment->update(['external_id' => 'MOCK-TEST-' . time()]);

# Now test verify-mock URL
// http://127.0.0.1:8000/payment/verify-mock?external_id=MOCK-TEST-XXXXX&status=approved
```

---

## Expected Results After Fix

### Before Mock Payment Fix
```
❌ "not found" error on verify-mock URL
❌ Payment status not updated
❌ Order status not synchronized
```

### After Mock Payment Fix
```
✅ Redirect to order detail page
✅ Payment status = "completed"  
✅ Order status = "processing"
✅ Success message displayed
✅ All 3 status boxes synchronized
```

---

## Files Modified

1. **app/Http/Controllers/PaymentController.php**
   - Updated `verifyMock()` method
   - Better error handling
   - Comprehensive logging
   - Improved redirects

2. **Routes (web.php)**
   - Route `/payment/verify-mock` - PUBLIC (no auth middleware)
   - Internal auth check in controller
   - All error cases redirect to valid pages

---

## Session Flow Diagram

```
User Login
  ↓
auth()->check() = true ✓
  ↓
Add Product to Cart
  ↓
Proceed to Checkout → Create Order + Payment
  ↓
Redirect to /payment/{order_id}
  ↓
Order Detail Page → Click "Lanjut ke Pembayaran"
  ↓
payment.process() → MockPaymentService::createPayment()
  - Generate external_id
  - Update Payment record with external_id
  - Return checkout URL
  ↓
Redirect to /payment/mock-checkout/{external_id}
  ↓
Mock Checkout Page
  - Check @auth ✓ (still logged in)
  - Show APPROVE/CANCEL/TIMEOUT buttons
  ↓
Click APPROVE Payment
  ↓
JavaScript redirect to /payment/verify-mock?external_id=...&status=approved
  ↓
verifyMock() method:
  - Validate external_id ✓
  - Check auth()->check() ✓
  - Find Payment by external_id ✓
  - Get Order ✓
  - Check authorization ✓
  - Update payment.status = "completed" ✓
  - Model observer triggers → order.status = "processing" ✓
  - Redirect to /orders/{order_id} ✓
  ↓
Order Detail Page
  - Show success message ✓
  - Display updated status ✓
  - Auto-refresh shows final status ✓
```

---

## Summary

**Problem**: User gets 404 or error when clicking approve payment

**Solution Implemented**:
1. ✅ Better error handling with safe fallbacks
2. ✅ Comprehensive logging for debugging  
3. ✅ Early auth check to prevent errors
4. ✅ Route cache cleared

**Next Steps**:
1. Test the flow following the steps above
2. Check logs for "Mock Payment Verify Request" entries
3. If error occurs, check the specific log message
4. Refer to troubleshooting section

**Expected Result**:
After clicking approve payment → redirect to /orders page with success message and updated status ✓
