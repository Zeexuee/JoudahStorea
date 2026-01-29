<x-layouts.app tittle="{{ $product->name }} - {{ $product->category->name }}'s" description="{{ $product->description }}">
    <div class="bg-white min-h-screen flex flex-col pt-24">
        
        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
        
        <div class="max-w-[1600px] mx-auto w-full px-6 lg:px-12 flex-grow flex flex-col lg:flex-row gap-12 lg:gap-20">
            
            <!-- LEFT COLUMN: Content & Card -->
            <div class="w-full lg:w-5/12 py-12 flex flex-col">
                
                <!-- Typography: Matching "Marble Statue Collection's" -->
                <div class="mb-8">
                    <h1 class="text-7xl md:text-8xl font-serif font-thin text-gray-900 -ml-1 leading-none tracking-tight">
                        {{ $product->category->name }}'s
                    </h1>
                    <h2 class="text-4xl text-gray-900 font-light mb-0 tracking-wide">{{ $product->name }}</h2>
                </div>

                <p class="text-slate-500 text-lg leading-relaxed font-light mb-5 max-w-lg">
                    {!! $product->description !!}
                </p>

                <!-- THE GOLD CARD -->
                <div class="mt-10 bg-[#F3EAD8] p-8 rounded-none shadow-none relative overflow-hidden">

                    <!-- Price Info Grid -->
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <span class="block text-gray-500 text-xs mb-1">Instant price</span>
                            <span class="block text-3xl font-bold text-gray-900">{{ Number::currency($product->price, 'IDR') }}</span>
                        </div>
                        @if($product->shopee_link || $product->tokopedia_link)
                        <div>
                            <span class="block text-gray-500 text-xs mb-2">Available at</span>
                            <div class="flex gap-8 items-center">
                                <!-- Shopee -->
                                @if($product->shopee_link)
                                <a href="{{ $product->shopee_link }}" target="_blank" class="block hover:opacity-80 transition transform hover:scale-105">
                                    <img src="{{ asset('images/logos/shopee.png') }}" class="h-12 w-auto object-contain" alt="Shopee">
                                </a>
                                @endif
                                <!-- Tokopedia -->
                                @if($product->tokopedia_link)
                                <a href="{{ $product->tokopedia_link }}" target="_blank" class="block hover:opacity-80 transition transform hover:scale-105">
                                    <img src="{{ asset('images/logos/tokopedia.png') }}" class="h-12 w-auto object-contain" alt="Tokopedia">
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Buttons (Likely redundant now, or maybe keep for WhatsApp?) -->
                    <div class="grid grid-cols-1">
                        <a href="https://wa.me/+6287796715916?text={{ urlencode('Halo, saya tertarik dengan produk ' . $product->name . ' ini. Apakah masih ada stok?') }}" target="_blank" class="bg-[#1A1A1A] text-white py-4 px-6 text-sm font-bold tracking-wide hover:bg-black transition text-center flex items-center justify-center gap-2">
                            <span>Contact via WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Gallery (Slider Style to match Reference) -->
            <div class="w-full lg:w-7/12 py-12 flex flex-col justify-between h-full relative">
                
                <!-- Slider Container -->
                <div id="gallery-slider" class="flex gap-8 overflow-x-auto scrollbar-hide snap-x snap-mandatory h-[600px] lg:h-[700px] items-stretch">
                    
                    <!-- Item 1: Front View -->
                    <div class="min-w-[85%] md:min-w-[45%] snap-center flex flex-col h-full">
                        <span class="block text-xs font-bold uppercase tracking-widest text-gray-900 mb-4"></span>
                        <div class="flex-grow bg-gray-100 overflow-hidden relative">
                             <img src="{{ !empty($product->images[0]) ? asset('storage/' . $product->images[0]) : asset('images/placeholder.png') }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>

                    <!-- Item 2: Packaging Detail -->
                    @if(isset($product->images[1]))
                    <div class="min-w-[85%] md:min-w-[45%] snap-center flex flex-col h-full">
                        <span class="block text-xs font-bold uppercase tracking-widest text-gray-900 mb-4"></span>
                        <div class="flex-grow bg-gray-100 overflow-hidden relative">
                             <img src="{{ asset('storage/' . $product->images[1]) }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>
                    @endif

                     <!-- Item 3: Texture -->
                    @if(isset($product->images[2]))
                    <div class="min-w-[85%] md:min-w-[45%] snap-center flex flex-col h-full">
                        <span class="block text-xs font-bold uppercase tracking-widest text-gray-900 mb-4"></span>
                        <div class="flex-grow bg-gray-100 overflow-hidden relative">
                             <img src="{{ asset('storage/' . $product->images[2]) }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>
                    @endif
                    
                    <!-- Item 4: In Context -->
                    @if(isset($product->images[3]))
                    <div class="min-w-[85%] md:min-w-[45%] snap-center flex flex-col h-full">
                        <span class="block text-xs font-bold uppercase tracking-widest text-gray-900 mb-4"></span>
                        <div class="flex-grow bg-gray-100 overflow-hidden relative">
                             <img src="{{ asset('storage/' . $product->images[3]) }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Custom Arrows (Bottom Left relative to slider) -->
                <div class="flex gap-4 mt-6">
                    <button onclick="document.getElementById('gallery-slider').scrollBy({left: -300, behavior: 'smooth'})" class="w-14 h-14 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7 7-7"></path></svg>
                    </button>
                    <button onclick="document.getElementById('gallery-slider').scrollBy({left: 300, behavior: 'smooth'})" class="w-14 h-14 rounded-full border border-gray-300 flex items-center justify-center hover:border-gray-900 hover:bg-gray-50 transition">
                         <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

                </div>
            </div>

        </div>

        <!-- NEW SECTIONS: Reviews & Recommendations -->
        <div class="max-w-[1600px] mx-auto w-full px-6 lg:px-12 pb-24">
            
            <!-- 1. Customer Reviews -->
            <div class="mb-32 max-w-4xl">
                <h3 class="text-3xl font-serif text-gray-900 mb-12">Ulasan Pelanggan</h3>
                
                <div class="space-y-12">
                    @forelse($product->reviews->where('is_approved', true) as $review)
                    <!-- Review Item -->
                    <div class="border-b border-gray-100 pb-12">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex text-amber-500 text-xs">
                                @for($i = 0; $i < $review->rating; $i++)
                                    <i class="fas fa-star">★</i>
                                @endfor
                                @for($i = $review->rating; $i < 5; $i++)
                                    <i class="far fa-star text-gray-300">★</i>
                                @endfor
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Pembeli Terpercaya</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">{{ $review->name }}</h4>
                        <p class="text-gray-500 font-light leading-relaxed">"{{ $review->comment }}"</p>
                        <span class="block mt-4 text-xs font-serif italic text-gray-400">— {{ $review->created_at->format('F d, Y') }}</span>
                    </div>
                    @empty
                    <div class="text-gray-500 italic">Belum ada ulasan, nantikan ulasan dari pembeli lainnya</div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Recommendations (You May Also Like) -->
            <div>
                <h3 class="text-3xl font-serif text-gray-900 mb-12">Kamu Mungkin Suka</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($relatedProducts as $related)
                    <a href="{{ route('product.detail', $related->slug) }}" class="group block cursor-pointer">
                        <div class="relative bg-gray-50 aspect-[4/5] overflow-hidden mb-4">
                            <img src="{{ asset('storage/' . ($related->images[0] ?? 'images/placeholder.png')) }}" alt="{{ $related->name }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-out grayscale-[0.1] group-hover:grayscale-0">
                        </div>
                        <h4 class="font-serif text-lg text-gray-900 mb-1 group-hover:text-amber-700 transition">{{ $related->name }}</h4>
                        <p class="text-sm text-gray-500">{{ Number::currency($related->price, 'IDR') }}</p>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
    <!-- Image Modal -->
    <div id="image-modal" class="fixed inset-0 z-[100] hidden bg-black/95 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 ease-out backdrop-blur-sm">
        <button id="modal-close" class="absolute top-6 right-6 text-white/50 hover:text-white transition focus:outline-none bg-black/20 p-2 rounded-full hover:bg-white/10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <img id="modal-image" src="" class="max-w-full max-h-[90vh] object-contain shadow-2xl rounded-sm transform scale-95 transition-transform duration-300 ease-out select-none">
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('image-modal');
            const modalImg = document.getElementById('modal-image');
            const closeBtn = document.getElementById('modal-close');
            const images = document.querySelectorAll('.gallery-image');
            
            // Function to open modal
            const openModal = (src) => {
                modalImg.src = src;
                modal.classList.remove('hidden');
                // Force reflow
                void modal.offsetWidth; 
                modal.classList.remove('opacity-0');
                modalImg.classList.remove('scale-95');
                modalImg.classList.add('scale-100');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            };

            // Function to close modal
            const closeModal = () => {
                modal.classList.add('opacity-0');
                modalImg.classList.remove('scale-100');
                modalImg.classList.add('scale-95');
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modalImg.src = '';
                    document.body.style.overflow = ''; // Restore scrolling
                }, 300);
            };

            // Add click event to all gallery images
            images.forEach(img => {
                img.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openModal(img.src);
                });
            });

            // Close events
            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => {
                if(e.target === modal) closeModal();
            });
            
            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    </script>
    @endpush

</x-layouts.app>
