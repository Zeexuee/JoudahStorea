@props(['categories'])

<section id="catalog" class="py-32 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-24">
            <span class="text-amber-600 uppercase tracking-[0.2em] text-xs font-bold mb-3 block">Discover Joudah</span>
            <h2 class="text-4xl md:text-6xl font-serif text-gray-900 mb-6 tracking-tight">The Collection</h2>
           <div class="w-12 h-0.5 bg-gray-900 mx-auto"></div>
        </div>

        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
            .slider-btn {
                position: absolute; top: 50%; transform: translateY(-50%); z-index: 10;
                background: white; border: 1px solid #f3f4f6; width: 44px; height: 44px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 20px rgba(0,0,0,0.08); opacity: 0;
            }
            .group:hover .slider-btn { opacity: 1; }
            .slider-btn:hover { background: #1a1a1a; color: white; border-color: #1a1a1a; }
            .prev-btn { left: -22px; }
            .next-btn { right: -22px; }

            /* Mobile slider drag styling */
            .slider-scroll {
                cursor: grab;
                user-select: none;
            }

            .slider-scroll.dragging {
                cursor: grabbing;
                scroll-behavior: auto;
            }

            .slider-scroll::-webkit-scrollbar {
                display: none;
            }

            /* J Scent section responsive styles */
            @media (max-width: 768px) {
                .j-scent-section .slider-scroll {
                    padding: 0 16px;
                    gap: 12px;
                    margin: 0 -16px;
                }

                .j-scent-section .slider-scroll a {
                    width: 220px !important;
                }

                .j-scent-section .slider-scroll a h4 {
                    font-size: 16px;
                }

                .j-scent-section .slider-scroll a span,
                .j-scent-section .slider-scroll a p {
                    font-size: 12px;
                }
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all slider scroll containers
            const sliderScrolls = document.querySelectorAll('.slider-scroll');

            sliderScrolls.forEach(slider => {
                let isDown = false;
                let startX;
                let scrollLeft;
                let isDragging = false;

                const startDrag = (e) => {
                    isDown = true;
                    isDragging = false;
                    startX = e.pageX || e.touches[0].pageX;
                    scrollLeft = slider.scrollLeft;
                    slider.classList.add('dragging');
                };

                const endDrag = () => {
                    isDown = false;
                    slider.classList.remove('dragging');
                };

                const drag = (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    
                    const x = e.pageX || e.touches[0].pageX;
                    const walk = (x - startX) * 1;
                    const newScrollLeft = scrollLeft - walk;
                    
                    if (Math.abs(walk) > 5) {
                        isDragging = true;
                    }
                    
                    slider.scrollLeft = newScrollLeft;
                };

                // Mouse events
                slider.addEventListener('mousedown', startDrag);
                slider.addEventListener('mouseleave', endDrag);
                slider.addEventListener('mouseup', endDrag);
                slider.addEventListener('mousemove', drag);

                // Touch events
                slider.addEventListener('touchstart', startDrag);
                slider.addEventListener('touchend', endDrag);
                slider.addEventListener('touchmove', drag);

                // Prevent product link click when dragging
                const links = slider.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        if (isDragging) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });

                // Prevent image drag
                const images = slider.querySelectorAll('img');
                images.forEach(img => {
                    img.addEventListener('dragstart', (e) => e.preventDefault());
                });
            });

            // Navigation buttons for desktop
            const navButtons = document.querySelectorAll('.slider-btn');
            navButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const container = this.closest('.slider-container');
                    const slider = container.querySelector('.slider-scroll');
                    const isNext = this.classList.contains('next-btn');
                    
                    if (slider) {
                        const scrollAmount = 300;
                        const newScrollLeft = isNext 
                            ? slider.scrollLeft + scrollAmount 
                            : slider.scrollLeft - scrollAmount;
                        
                        slider.scrollTo({
                            left: newScrollLeft,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
        </script>

        <!-- 1. FEATURED: Parfum Joudah (Grid Layout) -->
        @if($category = $categories['j-scent'] ?? null)
        <div class="mb-40 j-scent-section">
             <div class="flex flex-col items-center justify-center text-center md:flex-row md:items-end md:justify-between md:text-left mb-12 px-4 md:px-0">
                 <div class="max-w-xl">
                    <h3 class="text-3xl font-serif text-gray-900 mb-4">{{ $category->name }}</h3>
                    <p class="text-gray-500 font-light leading-relaxed">{{ $category->description }}</p>
                </div>
                <a href="{{ route('category.show', 'j-scent') }}" class="hidden md:inline-block text-sm uppercase tracking-widest border-b border-gray-900 pb-1 hover:text-amber-600 hover:border-amber-600 transition">Lihat Semua J Scent</a>
            </div>
            
            <div class="slider-scroll flex md:grid md:grid-cols-3 gap-y-16 gap-x-8 pb-8 scrollbar-hide md:pb-0 md:gap-8 overflow-x-auto md:overflow-x-visible snap-x snap-mandatory">
                @foreach($category->products->take(3) as $product)
                <a href="{{ route('product.detail', $product->slug) }}" class="group cursor-pointer block snap-start shrink-0 w-56 md:w-auto md:snap-start md:shrink-0">
                    <div class="relative bg-gray-50 aspect-[4/5] overflow-hidden mb-6">
                         @php
                             $imagePath = $product->images[0] ?? null;
                             $src = $imagePath 
                                 ? (Str::startsWith($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath))
                                 : asset('images/placeholder.png');
                         @endphp
                         <img src="{{ $src }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center transform group-hover:scale-105 transition duration-700 ease-out">
                         @if($product->is_featured)
                         <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 text-[10px] uppercase tracking-widest font-bold text-gray-900">Featured</div>
                         @endif
                    </div>
                    <h4 class="font-serif text-xl text-gray-900 mb-1">{{ $product->name }}</h4>
                    <span class="text-sm text-gray-500 mb-2 block">{{ $category->name }}</span>
                    <p class="font-medium text-gray-900">{{ Number::currency($product->price, 'IDR') }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 2. COLLECTION: Bukhur Box (Slider) -->
        @if($category = $categories['j-skin'] ?? null)
        <div class="mb-40 slider-container relative group">
            <div class="border-t border-gray-100 pt-10 mb-12 flex justify-between items-center px-2">
                 <h3 class="text-2xl font-serif text-gray-900">{{ $category->name }}</h3>
                 <span class="text-sm text-gray-400">Swipe to Explore</span>
            </div>
            
            <button class="slider-btn prev-btn"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"></path></svg></button>
            <button class="slider-btn next-btn"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"></path></svg></button>

            <div class="slider-scroll flex overflow-x-auto snap-x snap-mandatory gap-8 pb-8 scrollbar-hide px-2">
                @foreach($category->products as $product)
                <div class="snap-start shrink-0 w-72 group/card cursor-pointer">
                    <a href="{{ route('product.detail', $product->slug) }}" class="block">
                    <div class="relative aspect-square bg-gray-50 mb-6 overflow-hidden">
                            @php
                                $imagePath = $product->images[0] ?? null;
                                $src = $imagePath 
                                    ? (Str::startsWith($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath))
                                    : asset('images/placeholder.png');
                            @endphp
                            <img src="{{ $src }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center transform group-hover/card:scale-105 transition duration-500">
                        </div>
                        <h4 class="font-serif text-lg text-gray-900 mb-1 group-hover/card:text-amber-700 transition">{{ $product->name }}</h4>
                        <p class="text-gray-500 text-sm">{{ Number::currency($product->price, 'IDR') }}</p>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

         <!-- 4. CATEGORY: Linen Spray (Slider) -->
         @if($category = $categories['bukhur'] ?? null)
        <div class="mb-40 slider-container relative group">
            <div class="text-center mb-12">
                 <h3 class="text-3xl font-serif text-gray-900 mb-2">{{ $category->name }}</h3>
                 <p class="text-gray-500 font-light">{{ $category->description }}</p>
            </div>
            
            <button class="slider-btn prev-btn"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"></path></svg></button>
            <button class="slider-btn next-btn"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"></path></svg></button>

            <div class="slider-scroll flex overflow-x-auto snap-x snap-mandatory gap-6 pb-8 scrollbar-hide px-4 md:px-0">
                @foreach($category->products as $product)
                <div class="snap-center shrink-0 w-56 group/card cursor-pointer text-center">
                    <a href="{{ route('product.detail', $product->slug) }}" class="block">
                        <div class="relative aspect-[3/4] bg-gray-50 mb-4 overflow-hidden rounded-lg">
                            @php
                                $imagePath = $product->images[0] ?? null;
                                $src = $imagePath 
                                    ? (Str::startsWith($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath))
                                    : asset('images/placeholder.png');
                            @endphp
                            <img src="{{ $src }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center transform group-hover/card:scale-105 transition duration-500">
                        </div>
                        <h4 class="font-serif text-gray-900 text-lg group-hover/card:text-amber-600 transition">{{ $product->name }}</h4>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mt-1">{{ Number::currency($product->price, 'IDR') }}</p>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 5. FEATURED: Gifting & Hampers (Split Layout) -->
        @if($category = $categories['premium-series'] ?? null)
        <div class="mb-40 bg-warm-gray-50 rounded-2xl overflow-hidden shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                <div class="relative bg-gray-100 flex items-center justify-center p-8 lg:p-0 min-h-[400px]">
                    @php
                        $heroImage = $category->hero_image;
                        $heroSrc = $heroImage
                            ? (Str::startsWith($heroImage, 'images/') ? asset($heroImage) : asset('storage/' . $heroImage))
                            : asset('images/placeholder.png');
                    @endphp
                    <img src="{{ $heroSrc }}" alt="Exclusive Gift Sets" class="w-full h-full object-cover">
                </div>
                <div class="p-12 lg:p-20 flex flex-col justify-center bg-white">
                    <span class="text-amber-600 font-bold tracking-widest uppercase text-xs mb-4">The Art of Gifting</span>
                    <h3 class="text-4xl font-serif text-gray-900 mb-8">{{ $category->name }}</h3>
                    
                    <div class="space-y-12">
                        @foreach($category->products->take(2) as $product)
                        <div class="group cursor-pointer">
                            <a href="{{ route('product.detail', $product->slug) }}" class="block">
                                <div class="flex justify-between items-baseline mb-2 border-b border-gray-200 pb-2">
                                    <h4 class="text-xl font-serif text-gray-900 group-hover:text-amber-700 transition">{{ $product->name }}</h4>
                                    <span class="font-medium text-gray-900">{{ Number::currency($product->price, 'IDR') }}</span>
                                </div>
                                <p class="text-gray-500 text-sm leading-relaxed mb-3 font-light">{{ Str::limit(strip_tags($product->description), 100) }}</p>
                                <span class="text-xs uppercase tracking-wider font-bold text-amber-600 opacity-0 group-hover:opacity-100 transition transform translate-x-[-10px] group-hover:translate-x-0 inline-block">Lihat Detail</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</section>
