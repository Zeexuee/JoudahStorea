# API ENDPOINTS & ROUTES REFERENCE

## Customer Routes (Public)

### Home & Browse
```
GET  /                              Home page with categories
GET  /product/{slug}                Product detail page  
GET  /category/{slug}               Category products page
```

### Cart Management
```
GET    /cart                        View cart page
POST   /cart/add                    Add item to cart
DELETE /cart/{productId}            Remove item from cart
PATCH  /cart/{productId}            Update item quantity
GET    /cart/count                  Get cart count (AJAX)
```

### Authentication
```
POST /auth/login                    Login user
POST /auth/register                 Register new user
POST /auth/logout                   Logout user
GET  /auth/user                     Get current user info
```

---

## Customer Routes (Protected - Requires Login)

### Profile Management
```
GET    /profile                     View profile page
PUT    /profile                     Update profile
GET    /orders                      List my orders
GET    /orders/{order}              View order detail
```

### Checkout & Payment
```
GET    /checkout                    Checkout page with form
POST   /checkout                    Process checkout (create order)
GET    /checkout/cities             Get cities for province (AJAX)
GET    /checkout/shipping-costs     Get shipping options (AJAX)
```

### Payment Processing
```
GET    /payment/{order}             Payment page (show Doku button)
POST   /payment/{order}/process     Process payment (create Doku session)
GET    /payment/{order}/verify      Verify payment status
GET    /payment/{order}/status      Check payment status (AJAX)
POST   /payment/{order}/cancel      Cancel payment
```

### Payment Webhook (No Auth Required)
```
POST   /payment/callback/doku       Doku callback for verification
```

---

## Admin Routes (Protected - Requires Login)

### Dashboard
```
GET  /admin/dashboard             Admin dashboard with stats
```

### Order Management
```
GET    /admin/orders              List all orders (with filter & search)
GET    /admin/orders/{order}      Order detail & edit form
POST   /admin/orders/{order}/status
       Update order status (pending→processing→shipped→delivered)
POST   /admin/orders/{order}/payment-status
       Update payment status (pending→paid→failed→expired)
POST   /admin/orders/{order}/shipping-status
       Update shipping status + add kurir + tracking number
```

---

## Public Routes (Utility)

```
GET  /privacy-policy              Privacy policy page
GET  /shipping-policy             Shipping policy page
GET  /returns-exchanges           Returns & exchanges page
GET  /test/rajaongkir             Test Rajaongkir API
GET  /test/cities/{provinceId}    Test get cities (debug)
```

---

## Route Groups & Middleware

### Unprotected (Public)
- Home, products, categories
- Auth endpoints (login/register)
- Cart add/remove
- Payment callback

### Protected by `auth` Middleware
- Profile routes
- Orders routes
- Checkout routes
- Payment routes (non-webhook)
- Admin routes

---

## AJAX Endpoints (Used by JavaScript)

### Checkout Page
```
GET /checkout/cities?province_id={id}
    Response: { "success": true, "cities": { "id": "name", ... } }

GET /checkout/shipping-costs?province_id={id}&city_id={id}
    Response: { "success": true, "costs": [ { "courier_code": "...", ... } ] }
```

### Payment Status Checking
```
GET /payment/{order}/status
    Response: { "status": "paid|pending|failed", ... }
```

---

## Request/Response Examples

### Login
```
POST /auth/login
{
  "email": "user@example.com",
  "password": "password123"
}
```

### Add to Cart
```
POST /cart/add
{
  "product_id": 1,
  "quantity": 2
}
```

### Checkout
```
POST /checkout
{
  "shipping_name": "John Doe",
  "shipping_phone": "08123456789",
  "shipping_address": "Jl. Example No. 1",
  "shipping_province": "DKI Jakarta",
  "shipping_city": "Jakarta Selatan",
  "shipping_postal_code": "12345",
  "notes": "..." (optional)
}
```

### Update Order Status
```
POST /admin/orders/{id}/status
{
  "status": "processing"  // or: pending, shipped, delivered, cancelled
}
```

### Update Payment Status
```
POST /admin/orders/{id}/payment-status
{
  "payment_status": "paid"  // or: pending, failed, expired
}
```

### Update Shipping Status
```
POST /admin/orders/{id}/shipping-status
{
  "shipping_status": "in_transit",  // or: pending, picked_up, out_for_delivery, delivered, failed, returned
  "courier": "JNE",                  // optional
  "tracking_number": "123456789"     // optional
}
```

---

## Response Codes

```
200 OK              Successful request
201 Created         Resource created
204 No Content      Success, no response body
302 Found           Redirect (form success)
400 Bad Request     Invalid input
401 Unauthorized    Not logged in
403 Forbidden       Access denied
404 Not Found       Resource not found
422 Unprocessable   Validation error
500 Server Error    Server error
```

---

## Status Values

### Order Status
```
- pending        (Menunggu Pembayaran)
- processing     (Diproses)
- shipped        (Dikirim)
- delivered      (Terkirim)
- cancelled      (Dibatalkan)
```

### Payment Status
```
- pending        (Menunggu)
- paid           (Terbayar)
- failed         (Gagal)
- expired        (Kadaluarsa)
```

### Shipping Status
```
- pending                  (Menunggu Pengambilan)
- picked_up               (Diambil Kurir)
- in_transit              (Dalam Pengiriman)
- out_for_delivery        (Siap Pengiriman Hari Ini)
- delivered               (Terkirim)
- failed                  (Gagal Pengiriman)
- returned                (Dikembalikan)
```

---

## Courier Options
```
- JNE       (Jne Express)
- POS       (POS Indonesia)
- TIKI      (TIKI)
```

---

## Query Parameters

### Admin Orders List
```
GET /admin/orders?status=pending
    Filter by order status

GET /admin/orders?search=ORD-001
    Search by order number, customer name, or phone

GET /admin/orders?status=processing&page=2
    Paginate with filters
```

---

## File Upload (Future)

Currently no file uploads (optional future feature):
- Order attachments
- Invoice uploads
- Receipt uploads
- Image proofs

---

## Rate Limiting (Not Implemented)

Current setup has no rate limiting. For production, add:
- Max 100 requests/minute for checkout
- Max 1000 requests/hour for APIs
- Prevent brute force on login

---

## Authentication

### Session-Based (Currently Used)
```
Login → Session cookie stored → Protected routes check session
Logout → Session destroyed
```

### Token-Based (Future Alternative)
Could implement API tokens for mobile app:
```
POST /api/auth/login → Get token
Use token in Authorization header for API calls
```

---

## Admin Routes Summary Table

| Method | Route | Handler | Purpose |
|--------|-------|---------|---------|
| GET | /admin/dashboard | AdminOrderController@dashboard | Stats & overview |
| GET | /admin/orders | AdminOrderController@index | List orders |
| GET | /admin/orders/{id} | AdminOrderController@show | View detail |
| POST | /admin/orders/{id}/status | AdminOrderController@updateStatus | Change order status |
| POST | /admin/orders/{id}/payment-status | AdminOrderController@updatePaymentStatus | Change payment status |
| POST | /admin/orders/{id}/shipping-status | AdminOrderController@updateShippingStatus | Change shipping status |

---

## Customer Routes Summary Table

| Method | Route | Handler | Purpose |
|--------|-------|---------|---------|
| GET | /checkout | CheckoutController@show | Checkout form |
| POST | /checkout | CheckoutController@process | Create order |
| GET | /checkout/cities | CheckoutController@getCities | Get cities list |
| GET | /checkout/shipping-costs | CheckoutController@getShippingCosts | Get shipping options |
| GET | /payment/{id} | PaymentController@show | Payment page |
| POST | /payment/{id}/process | PaymentController@process | Create Doku session |
| GET | /payment/{id}/verify | PaymentController@verify | Verify payment |

---

## Webhook Endpoints

### Doku Payment Callback
```
POST /payment/callback/doku
Headers:
  - Signature: HMAC-SHA256(body, secret_key)
  
Body:
{
  "order_id": "...",
  "reference": "...",
  "amount": 50000,
  "status": "paid|failed",
  "timestamp": "..."
}
```

---

## Testing Tools

### Test Order Creation
```bash
# Via UI (Easiest)
1. Login at http://localhost
2. Add products to cart
3. Go to /checkout
4. Select province/city/shipping
5. Submit to create order
```

### Test Admin Features
```bash
# Via browser
1. Login 
2. Go to /admin/dashboard
3. View stats
4. Go to /admin/orders
5. Filter & search
6. Click order detail
7. Update statuses
```

### Test API (Programmatic)
```bash
# Using curl
curl -X GET http://localhost/checkout/cities?province_id=31

curl -X POST http://localhost/checkout \
  -d "shipping_name=John" \
  -d "shipping_phone=08123456789" \
  ... more fields
```

---

## Database Tables Used

```
users              ← Customers & admins
orders             ← Customer orders
order_items        ← Line items in orders
products           ← Product catalog
categories         ← Product categories
cart_items         ← Current shopping cart
payments           ← Payment records per order
shippings          ← Shipping records per order
```

---

## Models & Relations

```
User
  ├─ hasMany Orders
  └─ hasMany CartItems

Order
  ├─ belongsTo User
  ├─ hasMany Items (OrderItem)
  ├─ hasOne Payment
  └─ hasOne Shipping

OrderItem
  ├─ belongsTo Order
  └─ belongsTo Product

Product
  └─ hasMany OrderItems

Payment
  └─ belongsTo Order

Shipping
  └─ belongsTo Order

CartItem
  ├─ belongsTo User
  └─ belongsTo Product
```

---

## Complete URL Map

```
Web

GET     /               → home
GET     /product/{slug} → product-detail
GET     /category/{slug} → category-page

POST    /auth/login     → login
POST    /auth/register  → register
POST    /auth/logout    → logout
GET     /auth/user      → get-user

GET     /cart           → cart-page
POST    /cart/add       → add-to-cart
DELETE  /cart/{id}      → remove-from-cart
PATCH   /cart/{id}      → update-quantity
GET     /cart/count     → get-count

Protected Routes (require login):

GET     /profile        → profile-page
PUT     /profile        → update-profile
GET     /orders         → orders-list
GET     /orders/{id}    → order-detail

GET     /checkout       → checkout-page
POST    /checkout       → create-order
GET     /checkout/cities → get-cities-ajax
GET     /checkout/shipping-costs → get-shipping-ajax

GET     /payment/{id}   → payment-page
POST    /payment/{id}/process → create-payment
GET     /payment/{id}/verify → verify-payment
GET     /payment/{id}/status → check-status-ajax
POST    /payment/{id}/cancel → cancel-payment

Admin Routes (require login):

GET     /admin/dashboard → admin-dashboard
GET     /admin/orders → admin-orders-list
GET     /admin/orders/{id} → admin-order-detail
POST    /admin/orders/{id}/status → update-order-status
POST    /admin/orders/{id}/payment-status → update-payment-status
POST    /admin/orders/{id}/shipping-status → update-shipping-status

Callbacks (no auth):

POST    /payment/callback/doku → doku-webhook
```

---

## You Now Have:

✅ 20+ customer-facing routes
✅ 6 admin management routes
✅ 1 payment webhook endpoint
✅ 2 AJAX data endpoints
✅ Complete order flow
✅ Admin dashboard
✅ All endpoints documented

**Ready to deploy!** 🚀
