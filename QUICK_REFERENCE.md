# Quick Reference - Sistem Login & Register

## 🚀 Quick Start

### Buka Modal Login/Register
```javascript
openAuthModal();
```

### Check User Status
```javascript
async function checkUser() {
    const response = await fetch('/auth/user');
    const data = await response.json();
    
    if (data.user) {
        console.log('Logged in as:', data.user.name);
    } else {
        console.log('Not logged in');
    }
}
```

## 📁 File Locations

| File | Purpose |
|------|---------|
| `app/Http/Controllers/AuthController.php` | Logic login/register |
| `app/Models/CartItem.php` | Model with user relation |
| `app/Models/User.php` | Model with cartItems relation |
| `app/Http/Controllers/CartController.php` | Cart logic (updated for user_id) |
| `resources/views/components/auth-modal.blade.php` | Modal component |
| `resources/views/components/navbar.blade.php` | Navbar with auth buttons |
| `routes/web.php` | Auth routes |
| `database/migrations/2026_02_05_100000_*.php` | Database migration |

## 🔐 Routes

```
POST   /auth/login        - Login user
POST   /auth/register     - Register user
POST   /auth/logout       - Logout user
GET    /auth/user         - Get current user
```

## 📋 Database Structure

### Cart Items Table
```
id           int
user_id      int (nullable, foreign key to users)
session_id   string (nullable, for guests)
product_id   int (foreign key to products)
quantity     int
created_at   timestamp
updated_at   timestamp

Unique constraint: (user_id, product_id)
```

## 🎨 Key Components

### Auth Modal
- **Location:** `resources/views/components/auth-modal.blade.php`
- **Function:** Modal with login & register forms
- **Trigger:** Call `openAuthModal()`

### Navbar
- **Location:** `resources/views/components/navbar.blade.php`
- **Shows:** Login button (guest) or User dropdown (logged in)
- **Mobile:** Login button on mobile menu

### Layout
- **Location:** `resources/views/components/layouts/app.blade.php`
- **Includes:** `<x-auth-modal />` for all pages

## 🔄 Flow Diagram

```
User Visit Site
    ↓
1️⃣ Guest - Cart stored with session_id
    ↓
2️⃣ Click Login → Modal opens
    ↓
3️⃣ Enter email & password
    ↓
4️⃣ Submit to /auth/login
    ↓
5️⃣ Server validates & migrates cart
    ↓
6️⃣ User logged in
    ↓
7️⃣ Cart now stored with user_id
```

## 🛠️ Common Tasks

### Add Login Protection to Button
```html
<button onclick="
    fetch('/auth/user').then(r => r.json()).then(d => {
        if (!d.user) openAuthModal();
        else doSomething();
    })
">
    Lakukan Sesuatu
</button>
```

### Check Login Status in JavaScript
```javascript
fetch('/auth/user').then(r => r.json()).then(data => {
    if (data.user) {
        // User logged in
    } else {
        // User not logged in
    }
});
```

### Logout User
```html
<form action="{{ route('auth.logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>
```

## ⚠️ Important Notes

1. **Password:** Minimum 6 characters
2. **Email:** Must be unique in database
3. **CSRF:** All POST requests need CSRF token
4. **Session:** Used for CSRF + session management
5. **Cart Migration:** Automatic when user logs in
6. **Middleware:** Logout route uses `auth` middleware

## 🧪 Testing

1. Open browser console
2. Run: `openAuthModal()`
3. Try login/register
4. Check navbar - should show user name
5. Add product to cart
6. Logout and login again - cart should persist
7. Check cart count updates

## 🐛 Debugging

### Check if user is logged in
```php
// In controller
auth()->check() // returns true/false
auth()->user() // returns User object or null
auth()->id() // returns user id or null
```

### Check database
```sql
-- Check users table
SELECT * FROM users;

-- Check cart items
SELECT * FROM cart_items;

-- Check cart for specific user
SELECT * FROM cart_items WHERE user_id = 1;
```

### Check session cart (before login)
```sql
SELECT * FROM cart_items WHERE session_id = 'xxxx' AND user_id IS NULL;
```

## 📱 Mobile Support

- ✓ Modal responsive
- ✓ Login button in mobile menu
- ✓ All forms mobile-friendly
- ✓ Touch-friendly buttons

## 🔒 Security Features

- ✓ Password hashing (bcrypt)
- ✓ CSRF protection
- ✓ Email validation
- ✓ Session management
- ✓ Logout invalidates session

## 🚀 Future Enhancements

- [ ] Email verification
- [ ] Password reset
- [ ] Two-factor authentication
- [ ] Social login (Google, Facebook)
- [ ] Remember me feature
- [ ] Rate limiting
- [ ] Captcha

## 💡 Tips

1. Modal bisa dipanggil dari mana saja - gunakan `openAuthModal()`
2. Navbar otomatis update untuk login/logout - no refresh needed
3. Cart items otomatis migrate saat login
4. Gunakan `@auth` blade directive untuk conditional content
5. Testing: Buka DevTools → Console → copy `test-auth-system.js` code dan jalankan

## 📚 Related Documentation

- `DOKUMENTASI_LOGIN_REGISTER.md` - Dokumentasi lengkap
- `PANDUAN_IMPLEMENTASI_MODAL.md` - Contoh implementasi
- `public/test-auth-system.js` - Testing script

---

**Last Updated:** 2026-02-05
**Version:** 1.0
