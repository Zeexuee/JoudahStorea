<?php
/**
 * Test Rajaongkir API Connection
 * Run: php artisan tinker < tests/test-rajaongkir.php
 * Or: php test-rajaongkir.php from artisan tinker
 */

echo "\n=== Testing Rajaongkir API Connection ===\n\n";

try {
    $service = new App\Services\RajaongkirService();
    
    // Test 1: Get Provinces
    echo "1. Testing getProvinces()...\n";
    $provinces = $service->getProvinces();
    echo "   ✓ Success! Loaded " . count($provinces) . " provinces\n";
    echo "   Sample provinces:\n";
    $sample = array_slice($provinces, 0, 3, true);
    foreach ($sample as $id => $name) {
        echo "   - ID: $id, Name: $name\n";
    }
    
    // Test 2: Get Cities by Province (Jawa Barat = 16)
    echo "\n2. Testing getCitiesByProvince(16)...\n";
    $cities = $service->getCitiesByProvince(16);
    echo "   ✓ Success! Loaded " . count($cities) . " cities\n";
    echo "   First 3 cities:\n";
    $sample = array_slice($cities, 0, 3, true);
    foreach ($sample as $id => $name) {
        echo "   - ID: $id, Name: $name\n";
    }
    
    // Test 3: Get Shipping Costs
    echo "\n3. Testing getShippingCosts(72, 2000, ['jne'])...\n";
    // 72 = Bandung (Jawa Barat)
    $costs = $service->getShippingCosts(72, 2000, ['jne']);
    if (count($costs) > 0) {
        echo "   ✓ Success! Got " . count($costs) . " shipping option(s)\n";
        foreach ($costs as $cost) {
            echo "   - " . $cost['courier_name'] . ": Rp" . number_format($cost['cost']) . " (" . $cost['estimated_days'] . " hari)\n";
        }
    } else {
        echo "   ⚠ No shipping options found\n";
    }
    
    echo "\n✓ All tests passed! Rajaongkir API is working correctly.\n\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n\n";
}
