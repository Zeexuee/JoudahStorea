<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\Payment;

echo "=====================================" . PHP_EOL;
echo "TESTING COMPLETE STATUS SYNC FLOW" . PHP_EOL;
echo "=====================================" . PHP_EOL;

// Create a test order
$order = Order::find(12);
if (!$order) {
    echo "❌ Order not found" . PHP_EOL;
    exit(1);
}

$payment = $order->payment;
$user = $order->user;

echo PHP_EOL . "📦 ORDER DETAILS:" . PHP_EOL;
echo "Order Number: " . $order->order_number . PHP_EOL;
echo "Order Status: " . $order->status . PHP_EOL;
echo "Payment Status: " . $payment->status . PHP_EOL;
echo "User Email: " . $user->email . PHP_EOL;

echo PHP_EOL . "=" . str_repeat("=", 50) . PHP_EOL;
echo "SIMULATING COMPLETE PAYMENT FLOW" . PHP_EOL;
echo "=" . str_repeat("=", 50) . PHP_EOL;

// Step 1: Login user
echo PHP_EOL . "STEP 1: User Login" . PHP_EOL;
auth()->login($user);
if (auth()->check()) {
    echo "  ✓ User authenticated: " . auth()->user()->email . PHP_EOL;
}

// Step 2: Simulate payment completion
echo PHP_EOL . "STEP 2: Simulate Payment Completion" . PHP_EOL;
echo "  Updating payment status from '" . $payment->status . "' to 'completed'..." . PHP_EOL;

$payment->update([
    'status' => 'completed',
    'paid_at' => now(),
    'metadata' => array_merge($payment->metadata ?? [], [
        'verified_at' => now()->toIso8601String(),
    ])
]);

echo "  ✓ Payment updated" . PHP_EOL;

// Step 3: Verify auto-update
echo PHP_EOL . "STEP 3: Verify Auto-Update" . PHP_EOL;
$payment->refresh();
$order->refresh();

echo "  Payment Status (after update): " . $payment->status . PHP_EOL;
echo "  Order Status (after update): " . $order->status . PHP_EOL;

if ($order->status === 'processing') {
    echo "  ✓ Order auto-updated to 'processing'" . PHP_EOL;
} else {
    echo "  ❌ Order NOT updated! Still: " . $order->status . PHP_EOL;
}

// Step 4: Check fresh load
echo PHP_EOL . "STEP 4: Verify Fresh Data Load" . PHP_EOL;
$freshOrder = Order::find($order->id)->fresh();
$freshPayment = $freshOrder->payment;
echo "  Fresh order status: " . $freshOrder->status . PHP_EOL;
echo "  Fresh payment status: " . $freshPayment->status . PHP_EOL;

if ($freshOrder->status === 'processing' && $freshPayment->status === 'completed') {
    echo "  ✓ Fresh data loaded correctly" . PHP_EOL;
} else {
    echo "  ❌ Fresh data NOT correct" . PHP_EOL;
}

echo PHP_EOL . "=" . str_repeat("=", 50) . PHP_EOL;
echo "VERIFICATION SUMMARY" . PHP_EOL;
echo "=" . str_repeat("=", 50) . PHP_EOL;

$checks = [];

// Check 1: Payment completed
$checks[] = [
    'name' => 'Payment Status Updated',
    'expected' => 'completed',
    'actual' => $payment->status,
    'status' => $payment->status === 'completed'
];

// Check 2: Order auto-updated  
$checks[] = [
    'name' => 'Order Auto-Updated to Processing',
    'expected' => 'processing',
    'actual' => $order->status,
    'status' => $order->status === 'processing'
];

// Check 3: Fresh data load works
$checks[] = [
    'name' => 'Fresh Data Load Works',
    'expected' => 'completed/processing',
    'actual' => $freshPayment->status . '/' . $freshOrder->status,
    'status' => $freshPayment->status === 'completed' && $freshOrder->status === 'processing'
];

$allPassed = true;
foreach ($checks as $check) {
    $icon = $check['status'] ? '✓' : '❌';
    echo "\n$icon {$check['name']}" . PHP_EOL;
    echo "   Expected: {$check['expected']}" . PHP_EOL;
    echo "   Actual: {$check['actual']}" . PHP_EOL;
    if (!$check['status']) $allPassed = false;
}

echo PHP_EOL . "=" . str_repeat("=", 50) . PHP_EOL;

if ($allPassed) {
    echo "✓✓✓ ALL CHECKS PASSED! ✓✓✓" . PHP_EOL;
    echo PHP_EOL . "IMPROVEMENTS MADE:" . PHP_EOL;
    echo "1. ✓ Force fresh() data load in controllers" . PHP_EOL;
    echo "2. ✓ Auto-refresh JS every 5 seconds" . PHP_EOL;
    echo "3. ✓ Visual indicator showing auto-refresh active" . PHP_EOL;
    echo "4. ✓ Auto-refresh stops after 1-2 minutes" . PHP_EOL;
    echo PHP_EOL . "AREAS COVERED:" . PHP_EOL;
    echo "  - Admin order page" . PHP_EOL;
    echo "  - Customer order detail page" . PHP_EOL;
    echo "  - Both pages now auto-refresh while order is in progress" . PHP_EOL;
} else {
    echo "❌ SOME CHECKS FAILED" . PHP_EOL;
}

echo PHP_EOL . "=====================================" . PHP_EOL;
?>
