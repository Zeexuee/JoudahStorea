<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\Payment;

$order = Order::find(7);

if (!$order) {
    echo "Order not found!" . PHP_EOL;
    exit(1);
}

echo "===== BEFORE AUTO-UPDATE =====" . PHP_EOL;
echo "Order ID: " . $order->id . PHP_EOL;
echo "Order Status: " . $order->status . PHP_EOL;
echo "Payment Status: " . ($order->payment?->status ?? 'NO PAYMENT') . PHP_EOL;

// Update payment status to completed - This should trigger model observer
echo PHP_EOL . "===== UPDATING PAYMENT STATUS =====" . PHP_EOL;
echo "Updating payment status from 'pending' to 'completed'..." . PHP_EOL;

$payment = $order->payment;
$payment->update([
    'status' => 'completed',
    'paid_at' => now(),
]);

echo "Payment updated!" . PHP_EOL;

// Refresh both to get new values
$payment->refresh();
$order->refresh();

echo PHP_EOL . "===== AFTER AUTO-UPDATE =====" . PHP_EOL;
echo "Order ID: " . $order->id . PHP_EOL;
echo "Order Status: " . $order->status . PHP_EOL;
echo "Payment Status: " . $payment->status . PHP_EOL;
echo "Payment Paid At: " . $payment->paid_at->format('Y-m-d H:i:s') . PHP_EOL;

echo PHP_EOL . "===== RESULT =====" . PHP_EOL;
if ($order->status === 'processing') {
    echo "✓ SUCCESS! Order status auto-updated to 'processing'" . PHP_EOL;
} else {
    echo "✗ FAILED! Order status is still '" . $order->status . "'" . PHP_EOL;
    echo "Expected: 'processing'" . PHP_EOL;
}

// Check logs
echo PHP_EOL . "===== CHECKING LOGS =====" . PHP_EOL;
$logFile = storage_path('logs/laravel.log');
$logs = array_reverse(file($logFile));
$autoUpdateLogs = [];
foreach ($logs as $line) {
    if (strpos($line, 'Auto-update') !== false && strpos($line, '7') !== false) {
        $autoUpdateLogs[] = trim($line);
        if (count($autoUpdateLogs) >= 5) break;
    }
}

if (count($autoUpdateLogs) > 0) {
    echo "Found " . count($autoUpdateLogs) . " auto-update log(s):" . PHP_EOL;
    foreach ($autoUpdateLogs as $log) {
        echo "- " . substr($log, 0, 150) . "..." . PHP_EOL;
    }
} else {
    echo "No auto-update logs found for order 7" . PHP_EOL;
}
?>
