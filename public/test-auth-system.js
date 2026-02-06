// Test script untuk sistem login & register
// Taruh di console browser atau di file <script> terpisah

console.log('=== Login & Register System Test ===');

// 1. Test openAuthModal function exists
console.log('1. Testing openAuthModal function...');
if (typeof openAuthModal === 'function') {
    console.log('✓ openAuthModal function exists');
} else {
    console.error('✗ openAuthModal function not found');
}

// 2. Test auth modal element exists
console.log('2. Testing auth modal element...');
const authModal = document.getElementById('auth-modal');
if (authModal) {
    console.log('✓ Auth modal element exists');
} else {
    console.error('✗ Auth modal element not found');
}

// 3. Test login form exists
console.log('3. Testing login form...');
const loginForm = document.getElementById('login-form');
if (loginForm) {
    console.log('✓ Login form exists');
} else {
    console.error('✗ Login form not found');
}

// 4. Test register form exists
console.log('4. Testing register form...');
const registerForm = document.getElementById('register-form');
if (registerForm) {
    console.log('✓ Register form exists');
} else {
    console.error('✗ Register form not found');
}

// 5. Test auth endpoints
console.log('5. Testing auth endpoints...');

async function testAuthEndpoints() {
    // Test get current user
    try {
        const response = await fetch('/auth/user');
        const data = await response.json();
        console.log('✓ GET /auth/user - Response:', data);
        
        if (data.user) {
            console.log('  User is logged in as:', data.user.name);
        } else {
            console.log('  User is not logged in (guest)');
        }
    } catch (error) {
        console.error('✗ GET /auth/user - Error:', error);
    }
}

// 6. Test cart count endpoint
console.log('6. Testing cart count endpoint...');

async function testCartCount() {
    try {
        const response = await fetch('/cart/count');
        const data = await response.json();
        console.log('✓ GET /cart/count - Cart count:', data.cartCount);
    } catch (error) {
        console.error('✗ GET /cart/count - Error:', error);
    }
}

// 7. Test modal toggle
console.log('7. Testing modal toggle...');

function testModalToggle() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const toggleToRegister = document.getElementById('toggle-to-register');
    const toggleToLogin = document.getElementById('toggle-to-login');
    
    if (toggleToRegister && toggleToLogin) {
        console.log('✓ Modal toggle buttons exist');
        
        // Test click toggle
        toggleToRegister.click();
        setTimeout(() => {
            if (registerForm.classList.contains('hidden')) {
                console.error('✗ Register form should not be hidden after toggle');
            } else {
                console.log('✓ Register form toggles correctly');
            }
            
            toggleToLogin.click();
            setTimeout(() => {
                if (loginForm.classList.contains('hidden')) {
                    console.error('✗ Login form should not be hidden after toggle back');
                } else {
                    console.log('✓ Login form toggles correctly');
                }
            }, 300);
        }, 300);
    } else {
        console.error('✗ Modal toggle buttons not found');
    }
}

// 8. Test CSRF token
console.log('8. Testing CSRF token...');

const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    console.log('✓ CSRF token found:', csrfToken.content.substring(0, 20) + '...');
} else {
    console.error('✗ CSRF token not found');
}

// 9. Test Cart button in navbar
console.log('9. Testing cart button in navbar...');

const cartBtn = document.querySelector('a[href*="/cart"]');
if (cartBtn) {
    console.log('✓ Cart button found in navbar');
} else {
    console.error('✗ Cart button not found in navbar');
}

// 10. Test Login button in navbar
console.log('10. Testing login button in navbar...');

const loginBtn = Array.from(document.querySelectorAll('button')).find(btn => 
    btn.textContent.includes('Login') && btn.onclick && btn.onclick.toString().includes('openAuthModal')
);
if (loginBtn) {
    console.log('✓ Login button found in navbar');
} else {
    console.log('! Login button might be hidden (user might be logged in)');
}

// Run all tests
console.log('\n=== Running All Tests ===');
testAuthEndpoints();
setTimeout(() => testCartCount(), 500);
setTimeout(() => testModalToggle(), 1000);

console.log('\n=== Test Complete ===');
console.log('If you see all ✓, the system is working correctly!');
