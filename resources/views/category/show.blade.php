<x-layouts.app title="{{ $category->name }} - Joudah Store" description="{{ $category->description }}">
    <style>
        /* Mobile responsive product grid */
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
            }

            .category-products p {
                font-size: 12px !important;
            }

            .category-products .aspect-\[4\/5\] {
                aspect-ratio: 4/5;
                margin-bottom: 8px !important;
            }

            .category-products .group-hover\:translate-y-0 {
                font-size: 10px !important;
                padding: 4px 8px !important;
            }
        }
    </style>

    <div class="bg-white min-h-screen">
        <!-- Navbar spacer -->
        <div class="h-24"></div>

        <!-- HERO SECTION -->
        <div class="relative w-full h-[40vh] min-h-[400px] flex items-center justify-center overflow-hidden bg-gray-100">
            <!-- Background Image -->
            <img src="{{ $category->hero_image ? asset('storage/' . $category->hero_image) : asset('images/placeholder.png') }}" alt="{{ $category->name }}" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/30"></div>

            <!-- Content -->
            <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
                <span class="block text-sm font-bold tracking-[0.2em] uppercase mb-4 text-amber-200">The Collection</span>
                <h1 class="text-5xl md:text-7xl font-serif font-thin mb-6 tracking-tight">{{ $category->name }}</h1>
                <p class="text-lg md:text-xl font-light text-white/90 max-w-2xl mx-auto leading-relaxed">
                    {{ $category->description }}
                </p>
            </div>
        </div>

        <!-- PRODUCT GRID -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            
            <!-- Optional Results Count / Filter Bar -->
            <div class="flex justify-between items-center mb-12 border-b border-gray-100 pb-4">
                <span class="text-sm text-gray-400">{{ $category->products->count() }} Products Found</span>
                <div class="flex gap-4">
                    <button class="text-sm font-medium text-gray-500 hover:text-gray-900 transition">Sort by</button>
                    <button class="text-sm font-medium text-gray-500 hover:text-gray-900 transition">Filter</button>
                </div>
            </div>

            <div class="category-products grid grid-cols-1 md:grid-cols-3 gap-y-16 gap-x-8">
                @foreach($category->products as $product)
                <!-- Product Card (Minimalist) -->
                <a href="{{ route('product.detail', $product->slug) }}" class="group block cursor-pointer">
                    <div class="relative bg-gray-50 aspect-[4/5] overflow-hidden mb-6">
                        @if($product->has_discount)
                            <div class="absolute top-3 left-3 z-10 bg-amber-500 text-white text-[10px] font-bold uppercase tracking-[0.2em] px-2 py-1 shadow-md">
                                Diskon {{ $product->discount_percent }}%
                            </div>
                        @endif
                        <img src="{{ asset('storage/' . ($product->images[0] ?? 'images/placeholder.png')) }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center transform group-hover:scale-105 transition duration-700 ease-out grayscale-[0.1] group-hover:grayscale-0">
                        
                        <!-- Quick Add Overlay (Optional) -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-500 flex items-center justify-center opacity-0 group-hover:opacity-100">
                             <span class="bg-white text-gray-900 px-6 py-3 text-xs uppercase tracking-widest font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition duration-500">View Details</span>
                        </div>
                    </div>
                    <h3 class="font-serif text-xl text-gray-900 mb-1 group-hover:text-amber-700 transition">{{ $product->name }}</h3>
                    @if($product->has_discount)
                        <p class="font-medium text-gray-500 text-sm line-through">{{ Number::currency($product->price, 'IDR') }}</p>
                        <p class="font-semibold text-amber-600 text-sm">{{ Number::currency($product->price_after_discount, 'IDR') }}</p>
                    @else
                        <p class="font-medium text-gray-500 text-sm">{{ Number::currency($product->price, 'IDR') }}</p>
                    @endif
                </a>
                @endforeach
            </div>

            <!-- Empty State -->
            @if($category->products->count() === 0)
            <div class="text-center py-24">
                <p class="text-gray-400 font-light text-lg">No products found in this category yet.</p>
            </div>
            @endif

        </div>
    </div>
</x-layouts.app>
