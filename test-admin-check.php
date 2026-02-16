<?php

// Simple test script to verify admin middleware security
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Test 1: Check if user table has is_admin column
$users = \App\Models\User::select('id', 'email', 'is_admin')->take(5)->get();

echo "=== Database Check ===\n";
echo "Current users in database:\n";
foreach ($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Is Admin: " . ($user->is_admin ? 'YES' : 'NO') . "\n";
}

echo "\n=== To make a user admin, run: ===\n";
echo "php artisan tinker\n";
echo ">>> \App\Models\User::where('email', 'admin@mail.com')->update(['is_admin' => true])\n";
echo ">>> quit\n";

?>
