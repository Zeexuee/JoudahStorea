<x-layouts.app title="Oud Collection - Joudah Store" description="Koleksi wewangian kayu gaharu murni pilihan dari Joudah Store.">
    <style>
        /* Mobile Responsive Optimizations for Oud Page */
        @media (max-width: 768px) {
            .category-products {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px !important;
            }

            .category-products a {
                width: 100%;
            }

            .category-products h3 {
                font-size: 14px !important;
                line-height: 1.25 !important;
                margin-bottom: 2px !important;
            }

            .category-products p {
                font-size: 12px !important;
            }

            .category-products .product-img-wrapper {
                aspect-ratio: 4/5;
                margin-bottom: 8px !important;
            }

            .mobile-value-bar {
                padding-top: 12px !important;
                padding-bottom: 12px !important;
            }
        }
    </style>

    <div class="bg-white min-h-screen">
        <!-- Navbar Spacer -->
        <div class="h-16 md:h-24"></div>

        <!-- HERO SECTION -->
        <div class="relative w-full h-[35vh] sm:h-[45vh] min-h-[280px] sm:min-h-[380px] flex items-center justify-center overflow-hidden bg-gray-900">
            <!-- Hero Image -->
            <img src="{{ asset($category->hero_image ? (Str::startsWith($category->hero_image, 'images/') ? $category->hero_image : 'storage/' . $category->hero_image) : 'images/catalog/oud_hero.png') }}" 
                 alt="Oud Collection" 
                 class="absolute inset-0 w-full h-full object-cover object-center opacity-60">
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/45 to-black/20"></div>

            <!-- Hero Content -->
            <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
                <span class="inline-block text-[10px] sm:text-xs font-bold tracking-[0.2em] uppercase mb-2.5 sm:mb-4 text-amber-300 bg-amber-950/70 border border-amber-500/30 px-3 sm:px-4 py-1 rounded-full backdrop-blur-sm">
                    Royal Reserve Collection
                </span>
                <h1 class="text-3xl sm:text-5xl md:text-7xl font-serif font-thin mb-3 sm:mb-5 tracking-tight text-white">
                    Oud Collection
                </h1>
                <p class="text-xs sm:text-base md:text-xl font-light text-amber-100/90 max-w-2xl mx-auto leading-relaxed px-2">
                    Wewangian kayu gaharu murni pilihan dengan aroma hangat, berkelas dan terjamin keaslian nya.
                </p>
            </div>
        </div>

        <!-- VALUE PROPOSITION HIGHLIGHT BAR (Hidden on Mobile) -->
        <div class="hidden sm:block border-b border-gray-100 bg-amber-50/40 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-3 gap-2 sm:gap-6 text-center">
                    <div class="px-1 sm:px-4">
                        <h4 class="font-serif text-xs sm:text-lg font-semibold text-gray-900 mb-0.5 sm:mb-1">100% Gaharu Alami</h4>
                        <p class="text-[10px] sm:text-xs text-gray-500 font-light line-clamp-2">Ekstrak kayu Oud Alami</p>
                    </div>
                    <div class="px-1 sm:px-4 border-l border-amber-200/60">
                        <h4 class="font-serif text-xs sm:text-lg font-semibold text-gray-900 mb-0.5 sm:mb-1">Aroma Surgawi</h4>
                        <p class="text-[10px] sm:text-xs text-gray-500 font-light line-clamp-2">Keharuman tahan seharian</p>
                    </div>
                    <div class="px-1 sm:px-4 border-l border-amber-200/60">
                        <h4 class="font-serif text-xs sm:text-lg font-semibold text-gray-900 mb-0.5 sm:mb-1">Karakter Karismatik</h4>
                        <p class="text-[10px] sm:text-xs text-gray-500 font-light line-clamp-2">Wewangian istimewa</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCT GRID SECTION -->
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-8 sm:py-24">
            
            <!-- Section Header -->
            <div class="flex justify-between items-center mb-6 sm:mb-12 border-b border-gray-100 pb-3 sm:pb-4">
                <div>
                    <h2 class="text-lg sm:text-2xl font-serif text-gray-900">Koleksi Produk Oud</h2>
                    <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Dipilih secara khusus untuk keharuman terbaik</p>
                </div>
                <span class="text-[10px] sm:text-xs uppercase tracking-wider text-amber-700 font-bold bg-amber-50 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-md border border-amber-200/50 shrink-0">
                    {{ $category->products->count() }} Produk
                </span>
            </div>

            <!-- Grid (2 Columns on Mobile, 3 Columns on Desktop) -->
            <div class="category-products grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-8">
                @foreach($category->products as $product)
                <a href="{{ route('product.detail', $product->slug) }}" class="group block cursor-pointer">
                    <div class="product-img-wrapper relative bg-gray-50 aspect-[4/5] overflow-hidden mb-2 sm:mb-5 rounded-lg border border-gray-100 shadow-sm">
                        @if($product->has_discount)
                            <div class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-amber-600 text-white text-[9px] sm:text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 sm:px-2.5 sm:py-1 shadow-sm rounded">
                                -{{ $product->discount_percent }}%
                            </div>
                        @endif
                        @php
                            $imagePath = $product->images[0] ?? null;
                            $src = $imagePath 
                                ? (Str::startsWith($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath))
                                : asset('images/placeholder.png');
                        @endphp
                        <img src="{{ $src }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center transform group-hover:scale-105 transition duration-700 ease-out">
                        
                        <div class="hidden sm:flex absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors duration-500 items-center justify-center opacity-0 group-hover:opacity-100">
                             <span class="bg-gray-900 text-white px-6 py-3 text-xs uppercase tracking-widest font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition duration-500 rounded">Lihat Detail</span>
                        </div>
                    </div>
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-amber-700 mb-0.5 block">Oud Series</span>
                    <h3 class="font-serif text-sm sm:text-xl text-gray-900 mb-0.5 group-hover:text-amber-700 transition line-clamp-1">{{ $product->name }}</h3>
                    @if($product->has_discount)
                        <p class="font-medium text-gray-400 text-[10px] sm:text-xs line-through">{{ Number::currency($product->price, 'IDR') }}</p>
                        <p class="font-semibold text-amber-700 text-xs sm:text-base">{{ Number::currency($product->price_after_discount, 'IDR') }}</p>
                    @else
                        <p class="font-medium text-gray-900 text-xs sm:text-base">{{ Number::currency($product->price, 'IDR') }}</p>
                    @endif
                </a>
                @endforeach
            </div>

            <!-- Empty State -->
            @if($category->products->count() === 0)
            <div class="text-center py-16 sm:py-24 bg-gray-50 rounded-xl">
                <p class="text-gray-400 font-light text-sm sm:text-lg">Koleksi Oud saat ini belum tersedia.</p>
            </div>
            @endif

            <!-- HERITAGE BANNER -->
            <div class="mt-10 sm:mt-20 bg-stone-900 text-white rounded-xl sm:rounded-2xl p-5 sm:p-12 relative overflow-hidden">
                <div class="max-w-2xl">
                    <span class="text-amber-400 font-bold uppercase tracking-widest text-[10px] sm:text-xs mb-2 sm:mb-3 block">Joudah Craftsmanship</span>
                    <h3 class="text-xl sm:text-3xl font-serif mb-2 sm:mb-4">Keharuman & Kemewahan Oud Sejati</h3>
                    <p class="text-stone-300 text-xs sm:text-base leading-relaxed font-light mb-4 sm:mb-6">
                        Setiap produk Oud Joudah melalui standar kualitas tinggi dari bahan baku kayu gaharu murni pilihan. Memberikan hasil aroma yang hangat, menenangkan, dan elegan.
                    </p>
                    <a href="https://wa.me/6287796715916?text=Halo Joudah Store, Saya ingin bertanya tentang produk Oud Joudah" class="inline-block bg-amber-600 hover:bg-amber-700 text-white text-[11px] sm:text-xs font-bold uppercase tracking-widest px-5 py-2.5 sm:px-6 sm:py-3 rounded-lg transition">
                        Hubungi Kami
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
