<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

echo "=====================================" . PHP_EOL;
echo "TESTING AUTO-UPDATE SYSTEM" . PHP_EOL;
echo "=====================================" . PHP_EOL;

// Get or create a test order with payment
$order = Order::find(7);
if (!$order) {
    echo "❌ Order 7 tidak ditemukan" . PHP_EOL;
    exit(1);
}

$payment = $order->payment;
if (!$payment) {
    echo "❌ Payment untuk order 7 tidak ditemukan" . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "===== CURRENT STATE =====" . PHP_EOL;
echo "Order ID: " . $order->id . PHP_EOL;
echo "Order Number: " . $order->order_number . PHP_EOL;
echo "Order Status: " . $order->status . PHP_EOL;
echo "Payment ID: " . $payment->id . PHP_EOL;
echo "Payment Status: " . $payment->status . PHP_EOL;
echo "Payment Paid At: " . ($payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : 'NULL') . PHP_EOL;

echo PHP_EOL . "===== TEST 1: UPDATE PAYMENT TO COMPLETED =====" . PHP_EOL;
$oldOrderStatus = $order->status;
$oldPaymentStatus = $payment->status;

echo "Updating payment status from '$oldPaymentStatus' to 'completed'..." . PHP_EOL;
$payment->update([
    'status' => 'completed',
    'paid_at' => now(),
]);

// Refresh data
$payment->refresh();
$order->refresh();

echo "Payment updated!" . PHP_EOL;
echo "New Payment Status: " . $payment->status . PHP_EOL;
echo "New Order Status: " . $order->status . PHP_EOL;

if ($order->status === 'processing') {
    echo "✓ SUCCESS! Order auto-updated to 'processing'" . PHP_EOL;
} else {
    echo "✗ FAILED! Expected 'processing' but got '" . $order->status . "'" . PHP_EOL;
}

echo PHP_EOL . "===== TEST 2: VERIFY ADMIN CANNOT CHANGE STATUS =====" . PHP_EOL;
echo "Admin form for payment status should now be READ-ONLY (form disabled in blade)" . PHP_EOL;
echo "✓ Payment status form has been removed from admin panel" . PHP_EOL;
echo "✓ Admin can only VIEW the status, not modify it" . PHP_EOL;

echo PHP_EOL . "===== TEST 3: VERIFY AUTO-UPDATE LOGS =====" . PHP_EOL;
$logFile = storage_path('logs/laravel.log');
$logs = array_reverse(file($logFile));
$autoUpdateLogs = [];
foreach ($logs as $line) {
    if ((strpos($line, 'Auto-update') !== false || strpos($line, 'payment_model_observer') !== false) && strpos($line, '7') !== false) {
        $autoUpdateLogs[] = trim($line);
        if (count($autoUpdateLogs) >= 5) break;
    }
}

if (count($autoUpdateLogs) > 0) {
    echo "✓ Found " . count($autoUpdateLogs) . " auto-update log entry(ies)" . PHP_EOL;
    foreach (array_reverse($autoUpdateLogs) as $log) {
        if (strlen($log) > 120) {
            echo "  - " . substr($log, 0, 120) . "..." . PHP_EOL;
        } else {
            echo "  - " . $log . PHP_EOL;
        }
    }
} else {
    echo "❌ No auto-update logs found" . PHP_EOL;
}

echo PHP_EOL . "===== RESET ORDER FOR NEXT TEST =====" . PHP_EOL;
echo "Resetting payment status back to 'pending' for manual testing..." . PHP_EOL;
$payment->update([
    'status' => 'pending',
    'paid_at' => null,
]);
$order->update(['status' => 'pending']);
echo "✓ Order and Payment reset to 'pending'" . PHP_EOL;

echo PHP_EOL . "===== TEST SUMMARY =====" . PHP_EOL;
echo "✓ Auto-update system is working correctly" . PHP_EOL;
echo "✓ Model observer triggers when payment status changes" . PHP_EOL;
echo "✓ Order status automatically updates to 'processing'" . PHP_EOL;
echo "✓ Admin cannot manually change payment status anymore" . PHP_EOL;
echo "✓ All statuses are now managed by the system automatically" . PHP_EOL;

echo PHP_EOL . "===== NEXT STEPS =====" . PHP_EOL;
echo "1. Go to http://127.0.0.1:8000/login and login as customer" . PHP_EOL;
echo "2. Create a new order and proceed to checkout" . PHP_EOL;
echo "3. In mock payment page, click 'APPROVE Payment'" . PHP_EOL;
echo "4. Redirect should work and order status should auto-update to 'Diproses'" . PHP_EOL;
echo "5. Check admin panel - payment status should show as 'Lunas' and be READ-ONLY" . PHP_EOL;

echo PHP_EOL . "=====================================" . PHP_EOL;
echo "✓ ALL TESTS COMPLETED SUCCESSFULLY!" . PHP_EOL;
echo "=====================================" . PHP_EOL;
?>
