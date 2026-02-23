<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

// Find order from screenshot
$order = Order::where('order_number', 'ORD-20260219080408-C6I1')->first();

if (!$order) {
    echo "❌ Order not found" . PHP_EOL;
    echo "Available recent orders:" . PHP_EOL;
    $orders = Order::orderBy('created_at', 'desc')->take(5)->get();
    foreach ($orders as $o) {
        echo "  - ID: " . $o->id . " | Number: " . $o->order_number . " | Status: " . $o->status . PHP_EOL;
    }
    exit(1);
}

echo "Order found!" . PHP_EOL;
echo "=" . str_repeat("=", 50) . PHP_EOL;
echo "ORDER: " . $order->order_number . PHP_EOL;
echo "=" . str_repeat("=", 50) . PHP_EOL;

echo "\n✓ ORDER DATA:" . PHP_EOL;
echo "  ID: " . $order->id . PHP_EOL;
echo "  Status: " . $order->status . PHP_EOL;
echo "  Total: Rp" . number_format($order->total_amount, 0, ',', '.') . PHP_EOL;
echo "  Created: " . $order->created_at->format('Y-m-d H:i:s') . PHP_EOL;
echo "  Updated: " . $order->updated_at->format('Y-m-d H:i:s') . PHP_EOL;

echo "\n✓ PAYMENT DATA:" . PHP_EOL;
$payment = $order->payment;
if ($payment) {
    echo "  ID: " . $payment->id . PHP_EOL;
    echo "  Status: " . $payment->status . PHP_EOL;
    echo "  Amount: Rp" . number_format($payment->amount, 0, ',', '.') . PHP_EOL;
    echo "  Gateway: " . $payment->payment_gateway . PHP_EOL;
    echo "  Paid At: " . ($payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : 'NULL') . PHP_EOL;
    echo "  External ID: " . $payment->external_id . PHP_EOL;
} else {
    echo "  ❌ NO PAYMENT RECORD!" . PHP_EOL;
}

echo "\n✓ SHIPPING DATA:" . PHP_EOL;
$shipping = $order->shipping;
if ($shipping) {
    echo "  ID: " . $shipping->id . PHP_EOL;
    echo "  Status: " . $shipping->status . PHP_EOL;
    echo "  Courier: " . $shipping->courier . PHP_EOL;
    echo "  Tracking: " . ($shipping->tracking_number ?? 'N/A') . PHP_EOL;
} else {
    echo "  ❌ NO SHIPPING RECORD!" . PHP_EOL;
}

echo "\n✓ ITEMS (" . $order->items()->count() . " items):" . PHP_EOL;
foreach ($order->items as $item) {
    echo "  - " . $item->product?->name . " x" . $item->quantity . " @ Rp" . number_format($item->price, 0, ',', '.') . PHP_EOL;
}

echo "\n" . "=" . str_repeat("=", 50) . PHP_EOL;
echo "STATUS SYNC CHECK:" . PHP_EOL;
echo "=" . str_repeat("=", 50) . PHP_EOL;

if ($payment && $payment->status === 'completed' && $order->status === 'processing') {
    echo "✓ ALL SYNCED! Payment completed → Order processing" . PHP_EOL;
} elseif ($payment && $payment->status === 'completed' && $order->status !== 'processing') {
    echo "❌ NOT SYNCED! Payment is 'completed' but order status is '" . $order->status . "'" . PHP_EOL;
    echo "   (Order status should auto-update to 'processing')" . PHP_EOL;
} else {
    echo "ℹ️  Status: Payment=" . ($payment?->status ?? 'N/A') . " | Order=" . $order->status . PHP_EOL;
}
?>
