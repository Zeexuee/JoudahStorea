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
        User::updateOrCreate([
            'email' => 'admin@joudahstore.com',
        ], [
            'name' => 'Admin Joudah',
            'email' => 'admin@joudahstore.com',
            'password' => bcrypt('password'),
        ]);

        // Categories & Products
        $categories = [
            'j-scent' => [
                'name' => 'J Scent',
                'title' => 'J Scent',
                'description' => 'Koleksi parfume khas Joudah dengan karakter yang elegan dan tahan lama.',
                'banner' => 'images/catalog/parfum.png',
                'products' => [
                    ['name' => 'Majestic Oud', 'price' => 210000, 'image' => 'images/catalog/parfum.png'],
                    ['name' => 'Beauty', 'price' => 160000, 'image' => 'images/catalog/parfum.png'],
                    ['name' => 'Midnight', 'price' => 180000, 'image' => 'images/catalog/parfum.png'],
                ]
            ],
            'j-skin' => [
                'name' => 'J Skin',
                'title' => 'J Skin',
                'description' => 'Deo yang nyaman dipakai harian dengan aroma bersih dan ringan.',
                'banner' => 'images/catalog/dupa-deo-car.png',
                'products' => [
                    ['name' => 'Oud Fresh Deo', 'price' => 60000, 'image' => 'images/catalog/dupa-deo-car.png'],
                    ['name' => 'Musk Deo', 'price' => 60000, 'image' => 'images/catalog/dupa-deo-car.png'],
                ]
            ],
            'bukhur' => [
                'name' => 'Bukhur',
                'title' => 'Bukhur',
                'description' => 'Racikan bukhur hangat untuk rumah, kain, dan momen ibadah.',
                'banner' => 'images/catalog/bukhur-box.png',
                'products' => [
                    ['name' => 'Bukhur Maghribi', 'price' => 150000, 'image' => 'images/catalog/bukhur-box.png'],
                    ['name' => 'Bukhur Royal', 'price' => 200000, 'image' => 'images/catalog/bukhur-box.png'],
                    ['name' => 'Oud Mood', 'price' => 120000, 'image' => 'images/catalog/bukhur-box.png'],
                ]
            ],
            'solid' => [
                'name' => 'Solid',
                'title' => 'Solid',
                'description' => 'Solid fragrance untuk pemakaian praktis dan tahan lama.',
                'banner' => 'images/catalog/accessories.png',
                'products' => [
                    ['name' => 'Solid Amber', 'price' => 95000, 'image' => 'images/catalog/accessories.png'],
                    ['name' => 'Solid Musk', 'price' => 95000, 'image' => 'images/catalog/accessories.png'],
                ]
            ],
            'tasbih' => [
                'name' => 'Tasbih',
                'title' => 'Tasbih',
                'description' => 'Tasbih pilihan untuk menemani dzikir dan koleksi hadiah.',
                'banner' => 'images/catalog/accessories.png',
                'products' => [
                    ['name' => 'Tasbih Premium', 'price' => 75000, 'image' => 'images/catalog/accessories.png'],
                    ['name' => 'Tasbih Kayu', 'price' => 65000, 'image' => 'images/catalog/accessories.png'],
                ]
            ],
            'linen' => [
                'name' => 'Linen',
                'title' => 'Linen',
                'description' => 'Pewangi linen dan ruangan dengan karakter aroma yang bersih.',
                'banner' => 'images/catalog/linen-spray.png',
                'products' => [
                    ['name' => 'Kiswah', 'price' => 85000, 'image' => 'images/catalog/linen-spray.png'],
                    ['name' => 'Raudhah', 'price' => 85000, 'image' => 'images/catalog/linen-spray.png'],
                    ['name' => 'Soft', 'price' => 85000, 'image' => 'images/catalog/linen-spray.png'],
                ]
            ],
            'mobil' => [
                'name' => 'Mobil',
                'title' => 'Mobil',
                'description' => 'Aroma untuk kabin mobil yang nyaman, bersih, dan tidak berlebihan.',
                'banner' => 'images/catalog/dupa-deo-car.png',
                'products' => [
                    ['name' => 'Car Fresh Oud', 'price' => 70000, 'image' => 'images/catalog/dupa-deo-car.png'],
                    ['name' => 'Car Fresh Musk', 'price' => 70000, 'image' => 'images/catalog/dupa-deo-car.png'],
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

        $this->call(DistributorSeeder::class);
        $this->call(HomeVideoSeeder::class);
    }
}
