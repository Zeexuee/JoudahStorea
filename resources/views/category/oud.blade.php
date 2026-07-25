<x-layouts.app title="Oud Collection - Joudah Store" description="Koleksi wewangian kayu gaharu murni pilihan dari Joudah Store.">
    <div class="bg-white min-h-screen">
        <!-- Navbar Spacer -->
        <div class="h-20 md:h-24"></div>

        <!-- HERO SECTION -->
        <div class="relative w-full h-[45vh] min-h-[380px] flex items-center justify-center overflow-hidden bg-gray-900">
            <!-- Hero Image -->
            <img src="{{ asset($category->hero_image ? (Str::startsWith($category->hero_image, 'images/') ? $category->hero_image : 'storage/' . $category->hero_image) : 'images/catalog/oud_hero.png') }}" 
                 alt="Oud Collection" 
                 class="absolute inset-0 w-full h-full object-cover object-center opacity-55">
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>

            <!-- Hero Content -->
            <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
                <span class="inline-block text-xs font-bold tracking-[0.25em] uppercase mb-4 text-amber-300 bg-amber-950/70 border border-amber-500/30 px-4 py-1.5 rounded-full backdrop-blur-sm">
                    Royal Reserve Collection
                </span>
                <h1 class="text-4xl sm:text-5xl md:text-7xl font-serif font-thin mb-5 tracking-tight text-white">
                    Oud Collection
                </h1>
                <p class="text-base sm:text-lg md:text-xl font-light text-amber-100/90 max-w-2xl mx-auto leading-relaxed">
                    Wewangian kayu gaharu murni pilihan dengan aroma hangat, berkelas dan terjamin keaslian nya.
                </p>
            </div>
        </div>

        <!-- VALUE PROPOSITION HIGHLIGHT BAR -->
        <div class="border-b border-gray-100 bg-amber-50/40 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="px-4">
                        <h4 class="font-serif text-lg font-semibold text-gray-900 mb-1">100% Gaharu Alami</h4>
                        <p class="text-xs text-gray-500 font-light">Kemurnian ekstrak kayu Oud Alami</p>
                    </div>
                    <div class="px-4 border-t md:border-t-0 md:border-l border-amber-200/60 pt-4 md:pt-0">
                        <h4 class="font-serif text-lg font-semibold text-gray-900 mb-1">Aroma Surgawi</h4>
                        <p class="text-xs text-gray-500 font-light">Aroma Wewangian Surgawi yang bertahan sepanjang hari</p>
                    </div>
                    <div class="px-4 border-t md:border-t-0 md:border-l border-amber-200/60 pt-4 md:pt-0">
                        <h4 class="font-serif text-lg font-semibold text-gray-900 mb-1">Karakter Karismatik</h4>
                        <p class="text-xs text-gray-500 font-light">Wewangian khas dengan keistimewaan tersendiri</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCT GRID SECTION -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            
            <!-- Section Header -->
            <div class="flex justify-between items-center mb-12 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-2xl font-serif text-gray-900">Koleksi Produk Oud</h2>
                    <p class="text-xs text-gray-400 mt-1">Dipilih secara khusus untuk keharuman terbaik</p>
                </div>
                <span class="text-xs uppercase tracking-widest text-amber-700 font-bold bg-amber-50 px-3 py-1.5 rounded-md border border-amber-200/50">
                    {{ $category->products->count() }} Produk Tersedia
                </span>
            </div>

            <!-- Grid -->
            <div class="category-products grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-14 gap-x-8">
                @foreach($category->products as $product)
                <a href="{{ route('product.detail', $product->slug) }}" class="group block cursor-pointer">
                    <div class="relative bg-gray-50 aspect-[4/5] overflow-hidden mb-5 rounded-lg border border-gray-100 shadow-sm">
                        @if($product->has_discount)
                            <div class="absolute top-3 left-3 z-10 bg-amber-600 text-white text-[10px] font-bold uppercase tracking-[0.2em] px-2.5 py-1 shadow-sm rounded">
                                Diskon {{ $product->discount_percent }}%
                            </div>
                        @endif
                        @php
                            $imagePath = $product->images[0] ?? null;
                            $src = $imagePath 
                                ? (Str::startsWith($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath))
                                : asset('images/placeholder.png');
                        @endphp
                        <img src="{{ $src }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center transform group-hover:scale-105 transition duration-700 ease-out">
                        
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors duration-500 flex items-center justify-center opacity-0 group-hover:opacity-100">
                             <span class="bg-gray-900 text-white px-6 py-3 text-xs uppercase tracking-widest font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition duration-500 rounded">Lihat Detail</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-700 mb-1 block">Oud Series</span>
                    <h3 class="font-serif text-xl text-gray-900 mb-1 group-hover:text-amber-700 transition">{{ $product->name }}</h3>
                    @if($product->has_discount)
                        <p class="font-medium text-gray-400 text-xs line-through">{{ Number::currency($product->price, 'IDR') }}</p>
                        <p class="font-semibold text-amber-700 text-base">{{ Number::currency($product->price_after_discount, 'IDR') }}</p>
                    @else
                        <p class="font-medium text-gray-900 text-base">{{ Number::currency($product->price, 'IDR') }}</p>
                    @endif
                </a>
                @endforeach
            </div>

            <!-- Empty State -->
            @if($category->products->count() === 0)
            <div class="text-center py-24 bg-gray-50 rounded-xl">
                <p class="text-gray-400 font-light text-lg">Koleksi Oud saat ini belum tersedia.</p>
            </div>
            @endif

            <!-- HERITAGE BANNER -->
            <div class="mt-20 bg-stone-900 text-white rounded-2xl p-8 sm:p-12 relative overflow-hidden">
                <div class="max-w-2xl">
                    <span class="text-amber-400 font-bold uppercase tracking-widest text-xs mb-3 block">Joudah Craftsmanship</span>
                    <h3 class="text-3xl font-serif mb-4">Keharuman & Kemewahan Oud Sejati</h3>
                    <p class="text-stone-300 text-sm sm:text-base leading-relaxed font-light mb-6">
                        Setiap produk Oud Joudah melalui standar kualitas tinggi dari bahan baku kayu gaharu murni pilihan. Memberikan hasil aroma yang hangat, menenangkan, dan elegan.
                    </p>
                    <a href="https://wa.me/6287796715916?text=Halo Joudah Store, Saya ingin bertanya tentang produk Oud Joudah" class="inline-block bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-lg transition">
                        Hubungi Kami
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
