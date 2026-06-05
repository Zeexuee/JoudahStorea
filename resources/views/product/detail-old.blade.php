<x-layouts.app title="{{ $product->name }} - {{ $product->category->name }}'s" description="{{ $product->description }}">
    <div class="bg-white min-h-screen flex pt-24 overflow-hidden">
        
        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
            
            /* Split screen layout */
            .split-screen {
                display: flex;
                height: calc(100vh - 96px);
            }
            
            .split-section {
                flex: 1;
                overflow-y: auto;
                overflow-x: hidden;
            }
            
            .split-left {
                padding: 48px;
                background: #ffffff;
                border-right: 1px solid #f0f0f0;
            }
            
            .split-right {
                padding: 48px;
                background: #fafafa;
            }
            
            /* Mobile responsive */
            @media (max-width: 1024px) {
                .split-screen {
                    flex-direction: column;
                    height: auto;
                }
                
                .split-section {
                    height: auto;
                    overflow: visible;
                }
                
                .split-left, .split-right {
                    border-right: none;
                    border-bottom: 1px solid #f0f0f0;
                }
            }
        </style>
        
        <div class="split-screen w-full">
            <!-- LEFT SECTION: Gallery -->
            <div id="scroll-right" class="split-section split-right scrollbar-hide order-1 lg:order-2">
                <!-- Gallery Container - Vertical Stack -->
                <div id="gallery-slider" class="flex flex-col gap-8 w-full max-w-2xl mx-auto">

                <!-- THE GOLD CARD -->
                <div class="mb-8 bg-[#F3EAD8] p-5 rounded-none shadow-none relative overflow-hidden max-w-md">

                    <!-- Price Info Grid -->
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <span class="block text-gray-500 text-xs mb-1">Instant price</span>
                            <span class="block text-2xl font-bold text-gray-900">{{ Number::currency($product->price, 'IDR') }}</span>
                        </div>
                        @if($product->shopee_link || $product->tokopedia_link)
                        <div>
                            <span class="block text-gray-500 text-xs mb-2">Available at</span>
                            <div class="flex gap-6 items-center">
                                <!-- Shopee -->
                                @if($product->shopee_link)
                                <a href="{{ $product->shopee_link }}" target="_blank" class="block hover:opacity-80 transition transform hover:scale-105">
                                    <img src="{{ asset('images/logos/shopee.png') }}" class="h-10 w-auto object-contain" alt="Shopee">
                                </a>
                                @endif
                                <!-- Tokopedia -->
                                @if($product->tokopedia_link)
                                <a href="{{ $product->tokopedia_link }}" target="_blank" class="block hover:opacity-80 transition transform hover:scale-105">
                                    <img src="{{ asset('images/logos/tokopedia.png') }}" class="h-10 w-auto object-contain" alt="Tokopedia">
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Buttons (Likely redundant now, or maybe keep for WhatsApp?) -->
                    <div class="grid grid-cols-1">
                        <a href="https://wa.me/+6287796715916?text={{ urlencode('Halo, saya tertarik dengan produk ' . $product->name . ' ini. Apakah masih ada stok?') }}" target="_blank" class="bg-[#1A1A1A] text-white py-3 px-4 text-sm font-bold tracking-wide hover:bg-black transition text-center flex items-center justify-center gap-2">
                            <span>Contact via WhatsApp</span>
                        </a>
                    </div>
                </div>

                <div id="product-description-container" class="relative">
                    <div id="product-description" class="text-slate-500 text-base leading-relaxed text-justify mb-5 max-w-xl transition-all duration-500 ease-in-out max-h-[200px] overflow-hidden [&_p]:!mb-6 [&_p]:leading-loose [&_strong]:font-bold [&_strong]:text-gray-900 [&_b]:font-bold [&_b]:text-gray-900 [&_ul]:list-disc [&_ul]:ml-5 [&_ul]:!mb-6 [&_li]:mb-2 [&_br]:block [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:!mb-4 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:!mb-4 [&_h3]:text-lg [&_h3]:font-bold [&_h3]:!mb-4">
                        {!! $product->description !!}
                    </div>
                    <div id="description-overlay" class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                </div>
                <button id="toggle-description" class="text-amber-600 font-bold text-sm tracking-widest uppercase hover:text-amber-800 transition mb-8 focus:outline-none">
                    Read More
                </button>

                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const container = document.getElementById('product-description');
                        const toggleBtn = document.getElementById('toggle-description');
                        const overlay = document.getElementById('description-overlay');
                        const fullHeight = container.scrollHeight;
                        
                        // If content is short, hide button and overlay
                        if (fullHeight <= 200) {
                            toggleBtn.style.display = 'none';
                            overlay.style.display = 'none';
                            container.classList.remov00000000000000000000e('max-h-[200px]', 'overflow-hidden');
                        }

                        let isExpanded = false;

                        toggleBtn.addEventListener('click', () => {
                            isExpanded = !isExpanded;
                            
                            if (isExpanded) {
                                container.style.maxHeight = fullHeight + 'px';
                                container.classList.remove('overflow-hidden');
                                overlay.classList.add('opacity-0');
                                toggleBtn.textContent = 'Read Less';
                            } else {
                                container.style.maxHeight = '200px';
                                container.classList.add('overflow-hidden');
                                overlay.classList.remove('opacity-0');
                                toggleBtn.textContent = 'Read More';
                            }
                        });
                    });
                </script>
                @endpush
            </div>

            <!-- RIGHT COLUMN: Gallery (Slider Style to match Reference) -->
            <div class="w-full lg:w-6/12 py-12 flex flex-col justify-between h-full relative order-1 lg:order-2">
                
                <!-- Gallery Container - Vertical Stack -->
                <div id="gallery-slider" class="flex flex-col gap-8 w-full">
                    
                    <!-- Item 1: Front View -->
                    <div class="w-full flex flex-col">
                        <div class="w-full bg-gray-100 overflow-hidden relative aspect-square">
                             @php
                                $imagePath = $product->images[0] ?? null;
                                $src = $imagePath 
                                    ? (Str::startsWith($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath))
                                    : asset('images/placeholder.png');
                             @endphp
                             <img src="{{ $src }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>

                    <!-- Item 2: Packaging Detail -->
                    @if(isset($product->images[1]))
                    <div class="w-full flex flex-col">
                        <div class="w-full bg-gray-100 overflow-hidden relative aspect-square">
                             @php
                                $img1 = $product->images[1];
                                $src1 = Str::startsWith($img1, 'images/') ? asset($img1) : asset('storage/' . $img1);
                             @endphp
                             <img src="{{ $src1 }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>
                    @endif

                     <!-- Item 3: Texture -->
                    @if(isset($product->images[2]))
                    <div class="w-full flex flex-col">
                        <div class="w-full bg-gray-100 overflow-hidden relative aspect-square">
                             @php
                                $img2 = $product->images[2];
                                $src2 = Str::startsWith($img2, 'images/') ? asset($img2) : asset('storage/' . $img2);
                             @endphp
                             <img src="{{ $src2 }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>
                    @endif
                    
                    <!-- Item 4: In Context -->
                    @if(isset($product->images[3]))
                    <div class="w-full flex flex-col">
                        <div class="w-full bg-gray-100 overflow-hidden relative aspect-square">
                             @php
                                $img3 = $product->images[3];
                                $src3 = Str::startsWith($img3, 'images/') ? asset($img3) : asset('storage/' . $img3);
                             @endphp
                             <img src="{{ $src3 }}" class="w-full h-full object-cover grayscale-[0.1] cursor-pointer gallery-image hover:grayscale-0 transition duration-300">
                        </div>
                    </div>
                    @endif

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
                            @php
                                $relImg = $related->images[0] ?? null;
                                $relSrc = $relImg 
                                    ? (Str::startsWith($relImg, 'images/') ? asset($relImg) : asset('storage/' . $relImg))
                                    : asset('images/placeholder.png');
                            @endphp
                            <img src="{{ $relSrc }}" alt="{{ $related->name }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-out grayscale-[0.1] group-hover:grayscale-0">
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
