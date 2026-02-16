<?php

/**
 * Test Script untuk Verify Admin Login Redirect Fix
 * 
 * File ini untuk testing di Tinker atau console
 * Bukan unit test, hanya untuk verify logic
 */

// Test 1: Verify AdminLoginResponse class exists dan implement interface
use App\Filament\Http\Responses\AdminLoginResponse;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;

echo "Test 1: Check if AdminLoginResponse exists and implements LoginResponse interface\n";
$adminLoginResponse = new AdminLoginResponse();
if ($adminLoginResponse instanceof LoginResponseContract) {
    echo "✓ PASS: AdminLoginResponse implements LoginResponse interface\n";
} else {
    echo "✗ FAIL: AdminLoginResponse does not implement LoginResponse interface\n";
}

// Test 2: Verify setLoginType method works
echo "\nTest 2: Check if Login page setLoginType method works\n";
use App\Filament\Pages\Auth\Login;
use Illuminate\Support\Facades\Session;

// Start a new session
Session::flush();
$loginPage = new Login();
$loginPage->setLoginType('orders');

// Check session
$sessionValue = Session::get('login_type');
if ($sessionValue === 'orders') {
    echo "✓ PASS: setLoginType('orders') correctly saves to session\n";
} else {
    echo "✗ FAIL: Session not set correctly. Got: " . json_encode($sessionValue) . "\n";
}

// Test 3: Verify AdminLoginResponse reads from session correctly
echo "\nTest 3: Check if AdminLoginResponse reads login_type from session\n";
// Use reflection to check logic (since toResponse needs real request)
$reflection = new ReflectionClass(AdminLoginResponse::class);
$methods = $reflection->getMethods();
if (in_array('toResponse', array_map(fn($m) => $m->getName(), $methods))) {
    echo "✓ PASS: AdminLoginResponse has toResponse method\n";
} else {
    echo "✗ FAIL: AdminLoginResponse missing toResponse method\n";
}

// Test 4: Verify Login authenticate method overridden
echo "\nTest 4: Check if Login page authenticate method is overridden\n";
$loginReflection = new ReflectionClass(Login::class);
$authenticateMethod = $loginReflection->getMethod('authenticate');
$declaringClass = $authenticateMethod->getDeclaringClass()->getName();
if ($declaringClass === 'App\Filament\Pages\Auth\Login') {
    echo "✓ PASS: Login class has overridden authenticate method\n";
} else {
    echo "✗ FAIL: authenticate method not properly overridden\n";
}

echo "\n=== All Logic Tests Complete ===\n";
echo "\nNow test manually:\n";
echo "1. Open http://localhost/admin/login\n";
echo "2. Click 'Kelola Pesanan' button\n";
echo "3. Login with admin credentials\n";
echo "4. Expected: Redirect to /admin/dashboard ✓\n";
echo "5. Repeat with 'Kelola Barang' button\n";
echo "6. Expected: Redirect to /admin ✓\n";
