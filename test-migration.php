<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

// Test database
echo "=== MySQL Migration Verification ===\n\n";

use Illuminate\Support\Facades\DB;

try {
    $connection = DB::getDefaultConnection();
    echo "✓ Database Connection: $connection\n";
    
    $users = DB::table('users')->count();
    $orders = DB::table('orders')->count();
    $products = DB::table('products')->count();
    $payments = DB::table('payments')->count();
    
    echo "✓ Users: $users\n";
    echo "✓ Orders: $orders\n";
    echo "✓ Products: $products\n";
    echo "✓ Payments: $payments\n";
    
    $admin = DB::table('users')->where('is_admin', 1)->first();
    if ($admin) {
        echo "\n✓ Admin User Found: {$admin->email}\n";
    } else {
        echo "\n⚠ No admin user found\n";
    }
    
    echo "\n=== MIGRATION SUCCESSFUL ===\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
