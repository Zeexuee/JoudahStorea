<?php

/**
 * Debug Test: Mock Payment Verification Flow
 * 
 * This script simulates the complete mock payment flow:
 * 1. User adds product to cart
 * 2. User checks out and creates order
 * 3. User approves payment via verify-mock
 * 4. Payment status updates to "completed"
 * 5. Order status auto-updates to "processing"
 * 6. User redirected to order detail page
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\OrderItem;

echo "=== Testing Mock Payment Verification Flow ===\n\n";

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
        'shipping_name' => $user->name,
        'shipping_phone' => $user->phone,
        'shipping_address' => $user->address,
        'shipping_city' => 'Jakarta',
        'shipping_province' => 'DKI Jakarta',
        'shipping_postal_code' => '12345'
    ]);
    echo "[✓] Created test order: {$order->order_number}\n";

    // Add order item
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price_at_purchase' => $product->price
    ]);

    // Create payment record (mock)
    $externalId = 'MOCK-' . time();
    $payment = Payment::create([
        'order_id' => $order->id,
        'amount' => $order->total_price,
        'payment_method' => 'mock',
        'payment_gateway' => 'mock',
        'status' => 'pending',
        'external_id' => $externalId,
        'reference_number' => 'REF-' . time(),
        'metadata' => json_encode(['test' => true])
    ]);
    echo "[✓] Created payment record with external_id: {$externalId}\n";

    echo "\n--- Test URL ---\n";
    echo "http://127.0.0.1:8000/payment/verify-mock?external_id={$externalId}&status=approved\n";

    echo "\n--- Before Approval ---\n";
    $orderBefore = Order::with('payment', 'items.product')->find($order->id);
    echo "Order Status: {$orderBefore->status}\n";
    echo "Payment Status: " . ($orderBefore->payment ? $orderBefore->payment->status : 'N/A') . "\n";
    echo "Payment External ID: " . ($orderBefore->payment ? $orderBefore->payment->external_id : 'N/A') . "\n";
    echo "User ID: {$user->id}\n";
    echo "Order User ID: {$order->user_id}\n";

    // Simulate payment approval (what verifyMock would do)
    echo "\n--- Simulating Payment Approval (what verifyMock does) ---\n";
    
    // 1. Find payment
    $foundPayment = Payment::where('external_id', $externalId)->first();
    if (!$foundPayment) {
        echo "[✗] Payment not found by external_id\n";
        exit(1);
    }
    echo "[✓] Payment found: {$foundPayment->id}\n";

    // 2. Get order
    $foundOrder = $foundPayment->order;
    if (!$foundOrder) {
        echo "[✗] Order not found\n";
        exit(1);
    }
    echo "[✓] Order found: {$foundOrder->id}\n";

    // 3. Check authorization (simulating auth()->id() as user->id)
    if ($foundOrder->user_id !== $user->id) {
        echo "[✗] User not authorized for this order\n";
        exit(1);
    }
    echo "[✓] User authorized\n";

    // 4. Update payment status
    $foundPayment->update([
        'status' => 'completed',
        'paid_at' => now(),
        'metadata' => array_merge($foundPayment->metadata ?? [], [
            'test_result' => 'approved',
            'verified_at' => now()->toIso8601String(),
        ])
    ]);
    echo "[✓] Payment status updated to completed\n";

    // Wait for observer
    sleep(1);

    // 5. Check if order status was updated
    echo "\n--- After Approval ---\n";
    $orderAfter = Order::with('payment', 'items.product')->find($order->id);
    $orderAfter->refresh();
    echo "Order Status: {$orderAfter->status}\n";
    echo "Payment Status: {$orderAfter->payment->status}\n";

    // Validation
    echo "\n--- Validation ---\n";
    $checks = [
        'Order status changed to processing' => $orderAfter->status === 'processing',
        'Payment status is completed' => $orderAfter->payment->status === 'completed',
        'Payment has paid_at timestamp' => $orderAfter->payment->paid_at !== null,
        'Redirect route would work' => route('orders.show', $orderAfter) !== null,
    ];

    $allPassed = true;
    foreach ($checks as $check => $passed) {
        echo ($passed ? '[✓]' : '[✗]') . " {$check}\n";
        if (!$passed) $allPassed = false;
    }

    echo "\n--- Redirect Information ---\n";
    echo "Route: " . route('orders.show', $orderAfter) . "\n";

    // Cleanup
    echo "\n--- Cleaning up ---\n";
    OrderItem::where('order_id', $order->id)->delete();
    Payment::where('order_id', $order->id)->delete();
    Order::where('id', $order->id)->delete();
    echo "[✓] Test data cleaned up\n";

    echo "\n" . ($allPassed ? "✓ All checks passed!\n" : "✗ Some checks failed\n");

} catch (\Exception $e) {
    echo "[✗] Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
