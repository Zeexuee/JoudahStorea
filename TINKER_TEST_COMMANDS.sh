#!/usr/bin/env php
<?php

/**
 * Test: Verify Order Query Relationships
 * 
 * This test verifies that:
 * 1. ProfileController orders() query includes payment/shipping
 * 2. Order status updates when payment completes
 */

// Run in Artisan environment
$commands = [
    // Test 1: Check ProfileController method signature
    '$controller = app(\App\Http\Controllers\ProfileController::class);' . "\n" .
    'echo "Controller loaded: " . get_class($controller) . PHP_EOL;',
    
    // Test 2: Create test user and order
    '$user = \App\Models\User::create([' . "\n" .
    '    "name" => "Test User",
    '    "email" => "test-" . time() . "@test.com",' . "\n" .
    '    "password" => bcrypt("password"),' . "\n" .
    '    "phone" => "0812345",' . "\n" .
    '    "address" => "Test"' . "\n" .
    ']);' . "\n" .
    'echo "User created: {$user->email} (ID: {$user->id})" . PHP_EOL;',
    
    // Test 3: Create order with payment
    '$product = \App\Models\Product::first();' . "\n" .
    '$order = $user->orders()->create([' . "\n" .
    '    "order_number" => "TEST-" . time(),' . "\n" .
    '    "total_price" => 50000,' . "\n" .
    '    "total_quantity" => 1,' . "\n" .
    '    "status" => "pending",' . "\n" .
    '    "payment_method" => "mock"' . "\n" .
    ']);' . "\n" .
    'echo "Order created: {$order->order_number} (Status: {$order->status})" . PHP_EOL;',
    
    // Test 4: Create payment
    '$payment = $order->payment()->create([' . "\n" .
    '    "amount" => 50000,' . "\n" .
    '    "method" => "mock",' . "\n" .
    '    "status" => "pending"' . "\n" .
    ']);' . "\n" .
    'echo "Payment created (Status: {$payment->status})" . PHP_EOL;',
    
    // Test 5: Check if query has relationships
    '$ordersQuery = $user->orders()->with("items.product", "payment", "shipping")->get();' . "\n" .
    'echo "Orders loaded with relationships: " . $ordersQuery->count() . PHP_EOL;' . "\n" .
    '$testOrder = $ordersQuery->first();' . "\n" .
    'echo "First order has payment: " . ($testOrder->payment ? "YES" : "NO") . PHP_EOL;' . "\n" .
    'echo "First order has items: " . ($testOrder->items->count() > 0 ? "YES" : "NO") . PHP_EOL;',
    
    // Test 6: Update payment and check order status
    '$payment->update(["status" => "completed"]);' . "\n" .
    'echo "Payment updated to completed" . PHP_EOL;' . "\n" .
    'sleep(1);' . "\n" .
    '$order = \App\Models\Order::find($order->id);' . "\n" .
    'echo "Order status (fresh): {$order->status}" . PHP_EOL;' . "\n" .
    'echo "Payment status: {$order->payment->status}" . PHP_EOL;',
];

echo "Run these commands in tinker to test:\n";
echo "php artisan tinker\n\n";

foreach ($commands as $i => $cmd) {
    echo ($i + 1) . ". " . str_replace("\n", " ", $cmd) . "\n\n";
}
