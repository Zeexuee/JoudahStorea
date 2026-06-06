<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_distributors_page_loads_successfully_with_data(): void
    {
        // Create sample distributors
        Distributor::create([
            'name' => 'Toko Test 1',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'phone' => '628123456789',
            'address' => 'Jl. Test 1',
            'lat' => -6.2,
            'lng' => 106.8,
            'featured' => true,
        ]);

        Distributor::create([
            'name' => 'Toko Test 2',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'phone' => '628123456780',
            'address' => 'Jl. Test 2',
            'lat' => -6.9,
            'lng' => 107.6,
            'featured' => false,
        ]);

        $response = $this->get('/distributors');

        $response->assertStatus(200);
        $response->assertSee('Toko Test 1');
        $response->assertSee('Toko Test 2');
        $response->assertSee('Jakarta');
        $response->assertSee('Bandung');
        $response->assertSee('628123456789');
    }

    public function test_guests_cannot_access_admin_distributors_resource(): void
    {
        $response = $this->get('/admin/distributors');
        $response->assertStatus(302); // guests are redirected to admin login page
    }

    public function test_non_admin_users_cannot_access_admin_distributors_resource(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $response = $this->actingAs($user)->get('/admin/distributors');
        $response->assertStatus(403);
    }

    public function test_admin_users_can_access_admin_distributors_resource(): void
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/admin/distributors');
        $response->assertStatus(200);
        $response->assertSee('Distributors');
    }
}
