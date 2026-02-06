# API Documentation - Authentication & Cart

## Base URL
```
http://localhost/
```

## Headers (untuk semua request)
```
Content-Type: application/json
X-CSRF-TOKEN: [csrf_token_dari_meta_tag]
```

---

## 🔐 Authentication Endpoints

### 1. Login
**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login berhasil",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "created_at": "2026-02-05T10:00:00.000000Z",
        "updated_at": "2026-02-05T10:00:00.000000Z"
    }
}
```

**Error Response (401/422):**
```json
{
    "success": false,
    "message": "Email atau password salah"
}
```

---

### 2. Register
**Endpoint:** `POST /auth/register`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Register berhasil",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "created_at": "2026-02-05T10:00:00.000000Z",
        "updated_at": "2026-02-05T10:00:00.000000Z"
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

---

### 3. Logout
**Endpoint:** `POST /auth/logout`

**Request Body:**
```json
{}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

**Error Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

**Note:** Requires authentication (middleware: auth)

---

### 4. Get Current User
**Endpoint:** `GET /auth/user`

**Request Body:** None

**Success Response (if logged in) (200):**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "created_at": "2026-02-05T10:00:00.000000Z",
        "updated_at": "2026-02-05T10:00:00.000000Z"
    }
}
```

**Response (if not logged in) (200):**
```json
{
    "success": false,
    "user": null
}
```

---

## 🛒 Cart Endpoints

### 1. Get Cart Items
**Endpoint:** `GET /cart`

**Response:** Returns HTML page with cart items

**Cart Items Structure:**
```
- Display all items in cart (from session_id if guest, user_id if logged in)
- Show product details (name, price, image)
- Show quantity
- Allow update/delete
```

---

### 2. Add to Cart
**Endpoint:** `POST /cart/add`

**Request Body:**
```json
{
    "product_id": 1,
    "quantity": 2
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Produk ditambahkan ke keranjang",
    "productName": "Perfume Premium",
    "cartCount": 3
}
```

**Error Response (404):**
```json
{
    "message": "Product not found"
}
```

---

### 3. Remove from Cart
**Endpoint:** `DELETE /cart/{productId}`

**Request Body:** None

**Success Response (200):**
```json
{
    "success": true,
    "message": "Produk dihapus dari keranjang",
    "cartCount": 2
}
```

---

### 4. Update Cart Quantity
**Endpoint:** `PATCH /cart/{productId}`

**Request Body:**
```json
{
    "quantity": 5
}
```

**Success Response (200):**
```json
{
    "success": true,
    "cartCount": 5
}
```

**If Quantity <= 0:** Item will be deleted

---

### 5. Get Cart Count
**Endpoint:** `GET /cart/count`

**Request Body:** None

**Response (200):**
```json
{
    "cartCount": 3
}
```

---

## 🔄 Data Flow Examples

### Example 1: Guest Add to Cart
```javascript
// Guest (session_id = 'abc123')
POST /cart/add
{
    "product_id": 1,
    "quantity": 2
}

// Result in database:
// cart_items table
// id | user_id | session_id | product_id | quantity
// 1  | NULL    | 'abc123'   | 1          | 2
```

### Example 2: Login After Adding to Cart
```javascript
// 1. Guest adds to cart (session_id = 'abc123')
POST /cart/add
{
    "product_id": 1,
    "quantity": 2
}

// 2. Guest logins
POST /auth/login
{
    "email": "user@example.com",
    "password": "password123"
}

// 3. Cart migrates to user_id
// Result in database (after login):
// id | user_id | session_id | product_id | quantity
// 1  | 5       | NULL       | 1          | 2
```

### Example 3: User Already Has Item, Adds More
```javascript
// User (user_id = 5) already has:
// id | user_id | product_id | quantity
// 1  | 5       | 1          | 2

// User adds same product again
POST /cart/add
{
    "product_id": 1,
    "quantity": 3
}

// Result:
// id | user_id | product_id | quantity
// 1  | 5       | 1          | 5  // quantity updated (2 + 3)
```

---

## 🛡️ Authentication & Authorization

### Current User Session
- User session otomatis managed oleh Laravel
- Check dengan `auth()->check()` di backend
- Check dengan `GET /auth/user` di frontend

### Protected Routes
- `POST /auth/logout` - requires authentication

### Public Routes
- `POST /auth/login` - public
- `POST /auth/register` - public
- `GET /auth/user` - public (returns null if not authenticated)
- `GET /cart` - public
- `POST /cart/add` - public
- `DELETE /cart/{productId}` - public
- `PATCH /cart/{productId}` - public
- `GET /cart/count` - public

---

## 📊 Database Queries (Backend)

### Get User Cart
```php
// If logged in
$items = $user->cartItems()->with('product')->get();

// Or direct query
$items = CartItem::where('user_id', $user->id)->with('product')->get();
```

### Get Guest Cart
```php
$sessionId = session()->getId();
$items = CartItem::where('session_id', $sessionId)
                  ->whereNull('user_id')
                  ->with('product')
                  ->get();
```

### Migrate Cart from Session to User
```php
// Done automatically in AuthController@migrateSessionCartToUser()
$sessionCartItems = CartItem::where('session_id', $sessionId)
                             ->where('user_id', null)
                             ->get();

foreach ($sessionCartItems as $item) {
    $existing = CartItem::where('user_id', $user->id)
                        ->where('product_id', $item->product_id)
                        ->first();
    
    if ($existing) {
        $existing->increment('quantity', $item->quantity);
        $item->delete();
    } else {
        $item->update([
            'user_id' => $user->id,
            'session_id' => null
        ]);
    }
}
```

---

## ⏱️ Response Times

- Login/Register: ~100-200ms
- Cart Add/Update: ~50-100ms
- Get Cart Count: ~20-50ms
- Cart Migration: ~100-300ms (depends on number of items)

---

## 🔒 Validation Rules

### Login
| Field | Rules |
|-------|-------|
| email | required, email format |
| password | required, string, min:6 |

### Register
| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| email | required, email, unique:users |
| password | required, string, min:6, confirmed |

### Add to Cart
| Field | Rules |
|-------|-------|
| product_id | required, exists:products |
| quantity | required, integer, min:1 |

---

## 🐛 Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 401 | Unauthorized |
| 404 | Not Found |
| 422 | Validation Failed |

---

## 📝 Example Requests

### Using cURL
```bash
# Login
curl -X POST http://localhost/auth/login \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: [csrf_token]" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Register
curl -X POST http://localhost/auth/register \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: [csrf_token]" \
  -d '{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Get current user
curl http://localhost/auth/user

# Get cart count
curl http://localhost/cart/count
```

### Using JavaScript Fetch
```javascript
// Login
fetch('/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password123'
    })
}).then(r => r.json()).then(data => console.log(data));

// Add to cart
fetch('/cart/add', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        product_id: 1,
        quantity: 2
    })
}).then(r => r.json()).then(data => console.log(data));
```

---

**Last Updated:** 2026-02-05
