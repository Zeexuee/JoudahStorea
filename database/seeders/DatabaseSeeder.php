<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Admin Joudah',
            'email' => 'admin@joudahstore.com',
            'password' => bcrypt('password'),
        ]);

        // Categories & Products
        $categories = [
            'kayu-gaharu' => [
                'name' => 'Kayu Gaharu', // Fixed naming consistency
                'title' => 'Kayu Gaharu Collection',
                'description' => 'Experience the raw, grounding essence of premium Agarwood. Sourced ethically for the finest aromatic experience.',
                'banner' => 'images/catalog/dupa-deo-car.png',
                'products' => [
                    ['name' => 'Gaharu Kalimantan', 'price' => 500000, 'image' => 'images/catalog/dupa-deo-car.png'],
                    ['name' => 'Gaharu Merauke', 'price' => 750000, 'image' => 'images/catalog/dupa-deo-car.png'],
                    ['name' => 'Chips Super', 'price' => 300000, 'image' => 'images/catalog/dupa-deo-car.png'],
                ]
            ],
            'bukhur-gaharu' => [
                'name' => 'Bukhur Gaharu',
                'title' => 'Bukhur & Incense',
                'description' => 'Traditional blends of wood chips soaked in fragrant oils. Perfect for perfuming your home and clothes.',
                'banner' => 'images/catalog/bukhur-box.png',
                'products' => [
                    ['name' => 'Bukhur Maghribi', 'price' => 150000, 'image' => 'images/catalog/bukhur-box.png'],
                    ['name' => 'Bukhur Royal', 'price' => 200000, 'image' => 'images/catalog/bukhur-box.png'],
                    ['name' => 'Oud Mood', 'price' => 120000, 'image' => 'images/catalog/bukhur-box.png'],
                ]
            ],
            'perfume' => [
                'name' => 'Perfume',
                'title' => 'Fine Fragrances',
                'description' => 'A curated selection of long-lasting Eau de Parfums designed to make a statement.',
                'banner' => 'images/catalog/parfum.png',
                'products' => [
                    ['name' => 'Majestic Oud', 'price' => 210000, 'image' => 'images/catalog/parfum.png'],
                    ['name' => 'Beauty', 'price' => 160000, 'image' => 'images/catalog/parfum.png'],
                    ['name' => 'Midnight', 'price' => 180000, 'image' => 'images/catalog/parfum.png'],
                ]
            ],
            'linen-spray' => [
                'name' => 'Linen Spray',
                'title' => 'Linen & Room Spray',
                'description' => 'Refresh your fabrics and living spaces with our light, airy, and purifying sprays.',
                'banner' => 'images/catalog/linen-spray.png',
                'products' => [
                    ['name' => 'Kiswah', 'price' => 85000, 'image' => 'images/catalog/linen-spray.png'],
                    ['name' => 'Raudhah', 'price' => 85000, 'image' => 'images/catalog/linen-spray.png'],
                    ['name' => 'Soft', 'price' => 85000, 'image' => 'images/catalog/linen-spray.png'],
                ]
            ],
            'deodorant' => [
                'name' => 'Deodorant',
                'title' => 'Natural Deodorant',
                'description' => 'Stay fresh naturally with our oud-infused deodorant sprays.',
                'banner' => 'images/catalog/dupa-deo-car.png',
                'products' => [
                    ['name' => 'Oud Fresh Deo', 'price' => 60000, 'image' => 'images/catalog/dupa-deo-car.png'],
                    ['name' => 'Musk Deo', 'price' => 60000, 'image' => 'images/catalog/dupa-deo-car.png'],
                ]
            ],
            'premium-series' => [
                'name' => 'Premium Series',
                'title' => 'The Premium Series',
                'description' => 'Our most exclusive offerings for the discerning collector.',
                'banner' => 'images/catalog/hampers.png',
                'products' => [
                    ['name' => 'Royal Hamper', 'price' => 1500000, 'image' => 'images/catalog/hampers.png'],
                    ['name' => 'Collector Oud', 'price' => 2000000, 'image' => 'images/catalog/hampers.png'],
                ]
            ],
            'produk-luar' => [
                'name' => 'Produk Luar Joudah',
                'title' => 'Curated Imports',
                'description' => 'Special selections from global partners tailored for the Joudah lifestyle.',
                'banner' => 'images/catalog/accessories.png',
                'products' => [
                    ['name' => 'Imported Burner', 'price' => 450000, 'image' => 'images/catalog/accessories.png'],
                    ['name' => 'Saudi Charcoal', 'price' => 50000, 'image' => 'images/catalog/accessories.png'],
                ]
            ],
        ];

        foreach ($categories as $slug => $data) {
            $category = \App\Models\Category::create([
                'name' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'hero_image' => $data['banner'],
            ]);

            foreach ($data['products'] as $productData) {
                \App\Models\Product::create([
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'slug' => \Illuminate\Support\Str::slug($productData['name']),
                    'price' => $productData['price'],
                    'description' => $data['description'], // Inherit for now
                    'images' => [$productData['image']],
                    'is_featured' => true,
                ]);
            }
        }
    }
}
