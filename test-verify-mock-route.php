<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\Payment;

// Get order 10
$order = Order::find(10);

if (!$order) {
    echo "❌ Order 10 not found" . PHP_EOL;
    echo "Available orders:" . PHP_EOL;
    $orders = Order::all();
    foreach ($orders as $o) {
        echo "  - Order ID: " . $o->id . " | Order Number: " . $o->order_number . PHP_EOL;
    }
    exit(1);
}

$payment = $order->payment;

echo "Order found!" . PHP_EOL;
echo "Order ID: " . $order->id . PHP_EOL;
echo "Order Number: " . $order->order_number . PHP_EOL;
echo "User ID: " . $order->user_id . PHP_EOL;

if ($payment) {
    echo "Payment external_id: " . $payment->external_id . PHP_EOL;
    echo "Payment status: " . $payment->status . PHP_EOL;
} else {
    echo "No payment record found" . PHP_EOL;
}

echo PHP_EOL . "Check route:" . PHP_EOL;
echo "Route: /payment/verify-mock?external_id=" . ($payment->external_id ?? 'UNKNOWN') . "&status=approved" . PHP_EOL;
echo PHP_EOL . "The 404 error is likely because:" . PHP_EOL;
echo "1. User is NOT authenticated (auth middleware requires login)" . PHP_EOL;
echo "2. OR payment not found with that external_id" . PHP_EOL;
?>
