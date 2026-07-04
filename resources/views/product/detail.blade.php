<x-layouts.app title="{{ $product->name }} - {{ $product->category->name }}'s" description="{{ $product->description }}">
    <div class="bg-white min-h-screen pt-24 pb-32 lg:pb-0">
        
        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
            
            /* Desktop Split screen layout */
            .split-screen {
                display: flex;
                height: calc(100vh - 96px);
                width: 100%;
            }
            
            .split-section {
                flex: 1;
                overflow-y: scroll;
                overflow-x: hidden;
            }
            
            .split-left {
                padding: 48px 40px;
                background: #ffffff;
                border-right: 1px solid #f0f0f0;
                order: 2;
            }
            
            .split-right {
                padding: 48px 40px;
                background: #fafafa;
                order: 1;
            }
            
            /* Mobile responsive - Stacked vertical layout */
            @media (max-width: 1024px) {
                .split-screen {
                    flex-direction: column;
                    height: auto;
                    overflow: visible;
                }
                
                .split-section {
                    height: auto;
                    overflow: visible !important;
                    flex: none;
                    padding: 0;
                    background: transparent;
                    border: none;
                }
                
                .split-right {
                    order: 1;
                    padding: 0;
                    overflow-x: auto;
                    overflow-y: hidden;
                    -webkit-overflow-scrolling: touch;
                    scroll-behavior: smooth;
                }

                /* Gallery horizontal scroll on mobile */
                #gallery-slider {
                    flex-direction: row !important;
                    flex-wrap: nowrap !important;
                    gap: 12px !important;
                    padding: 16px;
                    width: 100%;
                    max-width: 100% !important;
                    overflow-x: auto;
                    overflow-y: hidden;
                    -webkit-overflow-scrolling: touch;
                    scroll-behavior: smooth;
                    user-select: none;
                    min-height: auto;
                }

                #gallery-slider > div {
                    flex-shrink: 0;
                    width: 280px;
                    height: 280px;
                }

                #gallery-slider .w-full {
                    width: 100%;
                    height: 100%;
                }

                #gallery-slider .aspect-square {
                    aspect-ratio: 1 / 1;
                }

                #gallery-slider > div:last-child {
                    margin-right: 16px;
                }
                
                .split-left {
                    order: 2;
                    padding: 24px 16px;
                    background: #ffffff;
                }
                
                /* Sticky action button at bottom on mobile */
                .sticky-action-btn {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    padding: 16px;
                    background: #ffffff;
                    border-top: 1px solid #f0f0f0;
                    z-index: 40;
                }
                
                .sticky-action-btn a {
                    min-height: 48px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                /* Accordion for details */
                .accordion-header {
                    padding: 16px 0;
                    border-bottom: 1px solid #f0f0f0;
                    cursor: pointer;
                    user-select: none;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .accordion-header:hover {
                    background-color: #fafafa;
                }
                
                .accordion-content {
                    max-height: 0;
                    overflow: hidden;
                    transition: max-height 0.3s ease-out;
                }
                
                .accordion-content.active {
                    max-height: 1000px;
                }
                
                .accordion-icon {
                    transition: transform 0.3s ease-out;
                }
                
                .accordion-icon.active {
                    transform: rotate(180deg);
                }
            }
```        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gallerySlider = document.getElementById('gallery-slider');
            if (!gallerySlider) return;

            // JS Dragging removed to allow flawless native scrolling on mobile

            // Prevent image drag and handle modal opening
            const images = gallerySlider.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('dragstart', (e) => e.preventDefault());
                img.addEventListener('click', function(e) {
                    // Open image modal
                    const modal = document.getElementById('gallery-modal');
                    const modalImg = document.getElementById('modal-gallery-image');
                    if (modal && modalImg) {
                        modalImg.src = this.src;
                        modal.classList.add('active');
                    }
                });
            });
        });
        </script>        
        <div class="split-screen">
            <!-- RIGHT SECTION: Gallery (Order 1 - Left on Desktop) -->
            <div id="scroll-right" class="split-section split-right scrollbar-hide">
                <!-- Gallery Container - Vertical Stack -->
                <div id="gallery-slider" class="flex flex-col gap-8 w-full max-w-2xl mx-auto">
                    
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

            <!-- LEFT SECTION: Product Details (Order 2 - Right on Desktop) -->
            <div id="scroll-left" class="split-section split-left scrollbar-hide">
                <div class="max-w-2xl mx-auto">
                    <!-- Typography: Product Name -->
                    <div class="mb-8 pb-8 border-b-2 border-gray-300">
                        <h2 class="text-4xl md:text-5xl text-gray-900 font-light mb-0 tracking-wide">{{ $product->name }}</h2>
                    </div>

                    <!-- THE GOLD CARD -->
                        <div class="mb-8 bg-[#F3EAD8] p-5 rounded-none shadow-none relative overflow-hidden max-w-md">

                        <!-- Price Info Grid -->
                            <div class="flex flex-wrap justify-between gap-x-6 gap-y-4 mb-6">
                            <div class="flex-1 min-w-[140px]">
                                <span class="block text-gray-500 text-xs mb-1">Instant price</span>
                                @if($product->has_discount)
                                    <div class="flex items-baseline gap-3">
                                        <span class="text-sm text-gray-500 line-through">{{ Number::currency($product->price, 'IDR') }}</span>
                                        <span class="text-2xl font-bold text-amber-600">{{ Number::currency($product->price_after_discount, 'IDR') }}</span>
                                    </div>
                                    <div class="text-sm text-green-600 mt-1">Diskon {{ $product->discount_percent }}% off</div>
                                    @if($product->discount_ends_at)
                                        <div class="text-xs text-gray-600 mt-1">
                                            Berlaku sampai {{ $product->discount_ends_at->format('d M Y H:i') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="block text-2xl font-bold text-gray-900">{{ Number::currency($product->price, 'IDR') }}</span>
                                @endif
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

                        <!-- Buttons -->
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Left Button: Add to Cart (Icon only) -->
                            <button class="bg-[#F3EAD8] text-gray-900 py-2 px-3 text-xs font-bold tracking-wide hover:bg-[#E8DCC8] transition text-center flex items-center justify-center gap-1 border border-gray-300 rounded-sm" title="Add to Cart">
                                <i class="fa-solid fa-cart-plus text-lg"></i>
                            </button>
                            
                            <!-- Right Button: Buy Now -->
                            <button class="bg-[#1A1A1A] text-white py-3 px-4 text-sm font-bold tracking-wide hover:bg-black transition text-center flex items-center justify-center gap-2" title="Buy Now" id="buy-now-btn">
                                <span>Buy Now</span>
                            </button>
                        </div>
                    </div>

                    <div id="product-description-container" class="relative mb-8">
                        <div id="product-description" class="text-slate-500 text-base leading-relaxed text-justify mb-5 max-w-xl transition-all duration-500 ease-in-out max-h-[200px] overflow-hidden [&_p]:!mb-6 [&_p]:leading-loose [&_strong]:font-bold [&_strong]:text-gray-900 [&_b]:font-bold [&_b]:text-gray-900 [&_ul]:list-disc [&_ul]:ml-5 [&_ul]:!mb-6 [&_li]:mb-2 [&_br]:block [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:!mb-4 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:!mb-4 [&_h3]:text-lg [&_h3]:font-bold [&_h3]:!mb-4">
                            {!! $product->description !!}
                        </div>
                        <div id="description-overlay" class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                    </div>
                    <button id="toggle-description" class="text-amber-600 font-bold text-sm tracking-widest uppercase hover:text-amber-800 transition mb-8 focus:outline-none">
                        Read More
                    </button>
                    <div class="w-full border-b-2 border-gray-300 mb-8"></div>

                    <!-- Customer Reviews Section -->
                    <div class="mb-20 pb-8 border-b-2 border-gray-300">
                        <h3 class="text-2xl font-serif text-gray-900 mb-8">Ulasan Pelanggan</h3>
                        
                        <div class="space-y-8">
                            @forelse($product->reviews->where('is_approved', true) as $review)
                            <!-- Review Item -->
                            <div class="border-b border-gray-100 pb-8">
                                <div class="flex items-center gap-4 mb-4">
                                    @if(!is_null($review->rating))
                                        <div class="flex text-xs items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $review->rating >= $i ? 'text-amber-500' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1 1 0 00.95.69h4.173c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.286 3.966c.3.921-.755 1.688-1.54 1.118l-3.377-2.455a1 1 0 00-1.175 0L5.58 17.03c-.785.57-1.84-.197-1.54-1.118l1.286-3.966a1 1 0 00-.364-1.118L1.585 8.373c-.783-.57-.38-1.81.588-1.81h4.173a1 1 0 00.95-.69L9.049 2.927z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    @else
                                        <div class="text-xs font-medium uppercase tracking-wider text-gray-400">Belum ada rating</div>
                                    @endif
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Pembeli Terpercaya</span>
                                    @if($review->is_verified_purchase ?? false)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-green-700">
                                            Pembelian Terverifikasi
                                        </span>
                                    @endif
                                </div>
                                <h4 class="text-lg font-bold text-gray-900 mb-2">{{ $review->name }}</h4>
                                @if(!is_null($review->rating))
                                    <p class="text-gray-500 font-light leading-relaxed">"{{ $review->comment }}"</p>
                                @else
                                    <p class="text-gray-500 font-light leading-relaxed">{{ $review->comment }}</p>
                                @endif
                                <span class="block mt-4 text-xs font-serif italic text-gray-400">— {{ $review->created_at->format('F d, Y') }}</span>
                            </div>
                            @empty
                            <div class="text-gray-500 italic">Belum ada ulasan, nantikan ulasan dari pembeli lainnya</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Related Products Section -->
                    <div class="mb-8 pt-8">
                        <h3 class="text-2xl font-serif text-gray-900 mb-8">Kamu Mungkin Suka</h3>
                        
                        <div class="grid grid-cols-2 gap-6">
                            @foreach($relatedProducts->shuffle()->take(4) as $related)
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
                                <h4 class="font-serif text-sm text-gray-900 mb-1 group-hover:text-amber-700 transition">{{ $related->name }}</h4>
                                <p class="text-xs text-gray-500">{{ Number::currency($related->price, 'IDR') }}</p>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky Action Button (Mobile Only) -->
    <div class="sticky-action-btn hidden lg:hidden">
        <div class="flex gap-3">
            <!-- Add to Cart Button -->
            <button class="bg-[#F3EAD8] text-gray-900 py-2 px-3 text-xs font-bold tracking-wide hover:bg-[#E8DCC8] transition flex items-center justify-center gap-1 border border-gray-300 rounded-sm flex-shrink-0" title="Add to Cart">
                <i class="fa-solid fa-cart-plus text-lg"></i>
            </button>
            
            <!-- WhatsApp Button -->
            <a href="https://wa.me/+6287796715916?text={{ urlencode('Halo, saya tertarik dengan produk ' . $product->name . ' ini. Apakah masih ada stok?') }}" target="_blank" class="bg-[#1A1A1A] text-white py-3 px-4 text-sm font-bold tracking-wide hover:bg-black transition text-center flex items-center justify-center gap-2 rounded-none flex-1">
                <span>WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="image-modal" class="fixed inset-0 z-[100] hidden bg-black/95 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 ease-out backdrop-blur-sm">
        <button id="modal-close" class="absolute top-6 right-6 text-white/50 hover:text-white transition focus:outline-none bg-black/20 p-2 rounded-full hover:bg-white/10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <img id="modal-image" src="" class="max-w-full max-h-[90vh] object-contain shadow-2xl rounded-sm transform scale-95 transition-transform duration-300 ease-out select-none">
    </div>

    <!-- Login Reminder Modal -->
    <div id="login-reminder-modal" class="fixed inset-0 z-[90] hidden bg-black/50 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 ease-out">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md transform scale-95 transition-transform duration-300 ease-out overflow-hidden border border-gray-200">
            <!-- Header -->
            <div class="bg-gray-50 px-6 py-8 text-center border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Login Diperlukan</h2>
            </div>

            <!-- Body -->
            <div class="px-6 py-6">
                <p class="text-gray-600 text-center text-sm mb-6 leading-relaxed">
                    Untuk menambahkan produk ke keranjang, silakan login terlebih dahulu.
                </p>

                <div class="space-y-3">
                    <button onclick="openAuthModal(); closeLoginReminderModal();" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                        Login Sekarang
                    </button>
                    <button onclick="closeLoginReminderModal();" class="w-full bg-white hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-300 border border-gray-300">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle description - works on both desktop and mobile
        function toggleDescription() {
            const container = document.getElementById('product-description');
            const toggleBtn = document.getElementById('toggle-description');
            const overlay = document.getElementById('description-overlay');
            const fullHeight = container.scrollHeight;
            
            let isExpanded = toggleBtn.getAttribute('data-expanded') === 'true';
            isExpanded = !isExpanded;
            
            if (isExpanded) {
                container.style.maxHeight = fullHeight + 'px';
                container.classList.remove('overflow-hidden');
                overlay.classList.add('opacity-0');
                toggleBtn.textContent = 'Read Less';
                toggleBtn.setAttribute('data-expanded', 'true');
            } else {
                container.style.maxHeight = '200px';
                container.classList.add('overflow-hidden');
                overlay.classList.remove('opacity-0');
                toggleBtn.textContent = 'Read More';
                toggleBtn.setAttribute('data-expanded', 'false');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('product-description');
            const toggleBtn = document.getElementById('toggle-description');
            const overlay = document.getElementById('description-overlay');
            const fullHeight = container.scrollHeight;
            
            toggleBtn.setAttribute('data-expanded', 'false');
            
            // If content is short, hide button and overlay
            if (fullHeight <= 200) {
                toggleBtn.style.display = 'none';
                overlay.style.display = 'none';
                container.classList.remove('max-h-[200px]', 'overflow-hidden');
            }

            toggleBtn.addEventListener('click', toggleDescription);
        });

        // Toggle accordion for product description
        function toggleAccordion(headerElement) {
            const content = headerElement.nextElementSibling;
            const icon = headerElement.querySelector('.accordion-icon');
            
            content.classList.toggle('active');
            icon.classList.toggle('active');
        }

        // Independent scroll - no chaining
        // Each section scrolls independently without any restrictions

        // Image modal
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

            // Add to Cart functionality
            const productId = {{ $product->id }};
            const addToCartButtons = document.querySelectorAll('button[title="Add to Cart"]');
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    try {
                        // Check if user is authenticated
                        const userResponse = await fetch('/auth/user');
                        const userData = await userResponse.json();
                        
                        if (!userData.authenticated) {
                            // User is not authenticated, show modern login reminder modal
                            showLoginReminderModal();
                            return;
                        }
                        
                        // User is authenticated, add to cart
                        const response = await fetch('/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                product_id: productId,
                                quantity: 1
                            })
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();
                        
                        // Update cart counts in navbar if function exists
                        if (typeof window.updateCartCount !== 'undefined') {
                            window.updateCartCount(data.cartCount);
                        }

                        // Show success message
                        showToast(`${data.productName} ditambahkan ke keranjang!`);
                        
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                        showToast('Gagal menambahkan ke keranjang', 'error');
                    }
                });
            });

            // Buy Now functionality
            const buyNowBtn = document.getElementById('buy-now-btn');
            if (buyNowBtn) {
                buyNowBtn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    try {
                        // Check if user is authenticated
                        const userResponse = await fetch('/auth/user');
                        const userData = await userResponse.json();
                        
                        if (!userData.authenticated) {
                            // User is not authenticated, show modern login reminder modal
                            showLoginReminderModal();
                            return;
                        }
                        
                        // User is authenticated, add to cart
                        const response = await fetch('/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                product_id: productId,
                                quantity: 1
                            })
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();
                        
                        // Update cart counts in navbar if function exists
                        if (typeof window.updateCartCount !== 'undefined') {
                            window.updateCartCount(data.cartCount);
                        }

                        // Show success message and show maintenance modal
                        showToast(`${data.productName} ditambahkan ke keranjang!`);
                        
                        // Show checkout maintenance modal after a short delay
                        setTimeout(() => {
                            window.showCheckoutMaintenanceModal();
                        }, 500);
                        
                    } catch (error) {
                        console.error('Error with Buy Now:', error);
                        showToast('Gagal memproses pesanan', 'error');
                    }
                });
            }

            // Login Reminder Modal Functions
            function showLoginReminderModal() {
                const modal = document.getElementById('login-reminder-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.style.opacity = '1';
                        const content = modal.querySelector('div');
                        content.style.transform = 'scale(1)';
                    }, 10);
                }
            }

            window.closeLoginReminderModal = function() {
                const modal = document.getElementById('login-reminder-modal');
                if (modal) {
                    modal.style.opacity = '0';
                    const content = modal.querySelector('div');
                    content.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }
            };

            // Close modal when clicking outside
            document.getElementById('login-reminder-modal')?.addEventListener('click', (e) => {
                if (e.target.id === 'login-reminder-modal') {
                    window.closeLoginReminderModal();
                }
            });

            // Toast notification function
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-6 right-6 px-6 py-4 rounded-sm text-white font-bold text-sm z-50 transition-all duration-300 ${
                    type === 'success' ? 'bg-green-600' : 'bg-red-600'
                }`;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        });
    </script>
    @endpush

</x-layouts.app>
