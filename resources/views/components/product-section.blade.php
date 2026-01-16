<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-serif text-gray-900 mb-4 tracking-wide">The Collection</h2>
            <p class="text-gray-500 max-w-2xl mx-auto font-light">Discover our signature scents, crafted with the finest ingredients for an unforgettable impression.</p>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 gap-y-12">
            <!-- Product 1 -->
            <div class="group cursor-pointer">
                <div class="relative overflow-hidden aspect-[3/4] mb-4 bg-gray-100">
                    <img src="{{ asset('images/hero-1.jpg') }}" alt="Majestic Oud" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-x-0 bottom-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex justify-center pb-8">
                        <button class="bg-white/90 backdrop-blur text-gray-900 px-6 py-2 text-sm uppercase tracking-widest hover:bg-white transition-colors">
                            View Details
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="font-serif text-lg text-gray-900 mb-1 group-hover:text-amber-600 transition-colors">Majestic Oud</h3>
                    <p class="text-gray-500 text-sm mb-2">Dark, Woody, Intense</p>
                    <span class="block text-gray-900 font-medium">IDR 1.500.000</span>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="group cursor-pointer">
                <div class="relative overflow-hidden aspect-[3/4] mb-4 bg-gray-100">
                    <img src="{{ asset('images/hero-2.jpg') }}" alt="Rose Elixir" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-x-0 bottom-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex justify-center pb-8">
                        <button class="bg-white/90 backdrop-blur text-gray-900 px-6 py-2 text-sm uppercase tracking-widest hover:bg-white transition-colors">
                            View Details
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="font-serif text-lg text-gray-900 mb-1 group-hover:text-amber-600 transition-colors">Rose Elixir</h3>
                    <p class="text-gray-500 text-sm mb-2">Floral, Sweet, Elegant</p>
                    <span class="block text-gray-900 font-medium">IDR 1.250.000</span>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="group cursor-pointer">
                <div class="relative overflow-hidden aspect-[3/4] mb-4 bg-gray-100">
                    <img src="{{ asset('images/hero-3.jpg') }}" alt="Amber Gold" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-x-0 bottom-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex justify-center pb-8">
                        <button class="bg-white/90 backdrop-blur text-gray-900 px-6 py-2 text-sm uppercase tracking-widest hover:bg-white transition-colors">
                            View Details
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="font-serif text-lg text-gray-900 mb-1 group-hover:text-amber-600 transition-colors">Amber Gold</h3>
                    <p class="text-gray-500 text-sm mb-2">Warm, Spicy, Luxurious</p>
                    <span class="block text-gray-900 font-medium">IDR 1.800.000</span>
                </div>
            </div>
        </div>
        
        <!-- View All Button -->
        <div class="mt-16 text-center">
            <a href="#" class="inline-block border-b border-gray-900 pb-1 text-gray-900 uppercase tracking-widest text-sm hover:text-gray-600 hover:border-gray-600 transition-all">
                View All Products
            </a>
        </div>
    </div>
</section>
