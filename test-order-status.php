<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

$order = Order::where('order_number', 'ORD-20260219073224-yA14')->first();

if ($order) {
    echo "Order found!" . PHP_EOL;
    echo "ID: " . $order->id . PHP_EOL;
    echo "Status: " . $order->status . PHP_EOL;
    if ($order->payment) {
        echo "Payment Status: " . $order->payment->status . PHP_EOL;
        echo "Payment Paid At: " . ($order->payment->paid_at ? $order->payment->paid_at->format('Y-m-d H:i:s') : 'NULL') . PHP_EOL;
    } else {
        echo "Payment: NOT FOUND" . PHP_EOL;
    }
} else {
    echo "Order NOT found" . PHP_EOL;
    echo "Getting first order instead..." . PHP_EOL;
    $order = Order::first();
    if ($order) {
        echo "First Order ID: " . $order->id . PHP_EOL;
        echo "First Order Number: " . $order->order_number . PHP_EOL;
        echo "Status: " . $order->status . PHP_EOL;
        if ($order->payment) {
            echo "Payment Status: " . $order->payment->status . PHP_EOL;
        }
    }
}
?>
