<?php

namespace Database\Seeders;

use App\Models\Distributor;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = config('distributors.locations', []);

        foreach ($locations as $location) {
            Distributor::updateOrCreate(
                [
                    'name' => $location['name'],
                    'city' => $location['city'],
                ],
                [
                    'province' => $location['province'],
                    'phone' => $location['phone'],
                    'address' => $location['address'],
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                    'featured' => $location['featured'] ?? false,
                ]
            );
        }
    }
}
