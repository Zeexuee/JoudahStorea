<?php

/**
 * Test Script: Order List Status Update After Payment
 * 
 * This script tests the complete flow:
 * 1. Order is created with payment
 * 2. Payment status is updated to "completed"
 * 3. Order status should auto-update to "processing"
 * 4. Order list should display updated status with eager-loaded payment/shipping
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

echo "=== Testing Order List Status Sync After Payment ===\n\n";

try {
    // Find or create test user
    $user = User::where('email', 'test@example.com')->first();
    if (!$user) {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'phone' => '081234567890',
            'address' => 'Test Address'
        ]);
        echo "[✓] Created test user: {$user->email}\n";
    } else {
        echo "[✓] Using existing test user: {$user->email}\n";
    }

    // Get first product for testing
    $product = Product::first();
    if (!$product) {
        echo "[✗] No products found in database\n";
        exit(1);
    }
    echo "[✓] Using product: {$product->name}\n";

    // Create a test order
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'TEST-' . time(),
        'total_price' => 50000,
        'total_quantity' => 1,
        'status' => 'pending',
        'payment_method' => 'mock',
        'payment_status' => 'pending',
        'shipping_status' => 'pending',
        'notes' => 'Test order for order list sync'
    ]);
    echo "[✓] Created test order: {$order->order_number} (Status: {$order->status})\n";

    // Add order item
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price_at_purchase' => $product->price
    ]);
    echo "[✓] Added order item\n";

    // Create payment record
    $payment = Payment::create([
        'order_id' => $order->id,
        'amount' => $order->total_price,
        'method' => 'mock',
        'status' => 'pending',
        'external_id' => 'MOCK-' . time(),
        'response' => json_encode(['test' => true])
    ]);
    echo "[✓] Created payment record (Status: {$payment->status})\n";

    echo "\n--- Before Payment Approval ---\n";
    $orderBefore = Order::with('payment', 'shipping')->find($order->id);
    echo "Order Status: {$orderBefore->status}\n";
    echo "Payment Status: " . ($orderBefore->payment ? $orderBefore->payment->status : 'N/A') . "\n";
    echo "Shipping Status: " . ($orderBefore->shipping ? $orderBefore->shipping->status : 'N/A') . "\n";

    // Simulate payment approval
    echo "\n--- Approving Payment ---\n";
    $payment->update(['status' => 'completed']);
    echo "[✓] Payment status updated to 'completed'\n";

    // Give model observers a moment to process
    sleep(1);

    echo "\n--- After Payment Approval ---\n";
    // Refresh the order from database with fresh() method
    $orderAfter = Order::with('payment', 'shipping')->find($order->id);
    echo "Order Status (fresh): {$orderAfter->status}\n";
    echo "Payment Status: " . ($orderAfter->payment ? $orderAfter->payment->status : 'N/A') . "\n";
    echo "Shipping Status: " . ($orderAfter->shipping ? $orderAfter->shipping->status : 'N/A') . "\n";

    // Test that orders query in ProfileController would show updated data
    echo "\n--- Testing ProfileController orders() Query ---\n";
    $ordersFromQuery = $user->orders()
        ->with('items.product', 'payment', 'shipping')
        ->orderByDesc('created_at')
        ->get();
    
    foreach ($ordersFromQuery as $o) {
        if ($o->id === $order->id) {
            echo "Order from query: {$o->order_number}\n";
            echo "  - Status: {$o->status}\n";
            echo "  - Payment Status: " . ($o->payment ? $o->payment->status : 'N/A') . "\n";
            echo "  - Has Payment Relationship: " . ($o->payment ? 'YES' : 'NO') . "\n";
            echo "  - Has Items Relationship: " . ($o->items->count() > 0 ? 'YES' : 'NO') . "\n";
        }
    }

    // Validation checks
    echo "\n--- Validation Checks ---\n";
    $checks = [
        'Order status changed to processing' => $orderAfter->status === 'processing',
        'Payment status is completed' => $orderAfter->payment && $orderAfter->payment->status === 'completed',
        'Payment relationship eager-loaded' => $ordersFromQuery[0]->payment !== null,
        'Items relationship eager-loaded' => $ordersFromQuery[0]->items->count() > 0,
    ];

    $allPassed = true;
    foreach ($checks as $check => $passed) {
        echo ($passed ? '[✓]' : '[✗]') . " {$check}\n";
        if (!$passed) $allPassed = false;
    }

    echo "\n" . ($allPassed ? "[✓] All checks passed! Order list will show updated status.\n" : "[✗] Some checks failed.\n");

    // Cleanup test data
    echo "\n--- Cleaning up test data ---\n";
    OrderItem::where('order_id', $order->id)->delete();
    Payment::where('order_id', $order->id)->delete();
    Order::where('id', $order->id)->delete();
    echo "[✓] Test data cleaned up\n";

} catch (\Exception $e) {
    echo "[✗] Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
