<x-layouts.app title="Shopping Cart - Joudah Store" description="View your shopping cart items">
    <div class="bg-white min-h-screen pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-4xl md:text-5xl font-serif text-gray-900 mb-2">Keranjang Belanja</h1>
                <p class="text-gray-500 font-light">Tinjau dan kelola item belanja Anda</p>
            </div>

            @if($cartItems->isEmpty())
                <!-- Empty Cart State -->
                <div class="text-center py-24">
                    <div class="mb-6">
                        <i class="fa-solid fa-shopping-cart text-6xl text-gray-300"></i>
                    </div>
                    <h2 class="text-2xl font-serif text-gray-900 mb-2">Keranjang Anda Kosong</h2>
                    <p class="text-gray-500 mb-8">Belum ada item di keranjang Anda. Mari mulai berbelanja!</p>
                    <a href="/" class="inline-block bg-[#1A1A1A] text-white py-3 px-8 font-bold tracking-wide hover:bg-black transition">
                        Lanjutkan Belanja
                    </a>
                </div>
            @else
                <!-- Cart Items -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Items List -->
                    <div class="lg:col-span-2">
                        <div class="space-y-6">
                            @foreach($cartItems as $cartItem)
                                <div class="flex gap-4 pb-6 border-b border-gray-200">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0 w-24 h-24">
                                        @if($cartItem->product->main_image)
                                            <img src="{{ Storage::url($cartItem->product->main_image) }}" 
                                                 alt="{{ $cartItem->product->name }}"
                                                 class="w-full h-full object-cover rounded-sm">
                                        @else
                                            <div class="w-full h-full bg-gray-100 rounded-sm flex items-center justify-center">
                                                <i class="fa-solid fa-image text-2xl text-gray-300"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Product Info -->
                                    <div class="flex-1">
                                        <a href="{{ route('product.detail', $cartItem->product->slug) }}" class="text-lg font-serif text-gray-900 hover:text-amber-600 transition">
                                            {{ $cartItem->product->name }}
                                        </a>
                                        <p class="text-sm text-gray-500 mt-1">{{ $cartItem->product->category->name }}</p>
                                        
                                        <!-- Price -->
                                        <div class="mt-3 text-lg font-bold text-gray-900">
                                            Rp {{ number_format($cartItem->product->price * $cartItem->quantity, 0, ',', '.') }}
                                        </div>
                                        
                                        <!-- Quantity Controls -->
                                        <div class="mt-4 flex items-center gap-3">
                                            <button class="decrease-qty px-3 py-1 border border-gray-300 hover:bg-gray-100 transition" data-product-id="{{ $cartItem->product->id }}">-</button>
                                            <input type="number" class="qty-input w-12 text-center border border-gray-300 py-1" value="{{ $cartItem->quantity }}" min="1" data-product-id="{{ $cartItem->product->id }}">
                                            <button class="increase-qty px-3 py-1 border border-gray-300 hover:bg-gray-100 transition" data-product-id="{{ $cartItem->product->id }}">+</button>
                                            <button class="remove-item ml-auto text-red-600 hover:text-red-800 text-sm font-bold transition" data-product-id="{{ $cartItem->product->id }}">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Continue Shopping -->
                        <div class="mt-8">
                            <a href="/" class="text-amber-600 font-bold hover:text-amber-800 transition">
                                ← Lanjutkan Belanja
                            </a>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-[#F3EAD8] p-8 rounded-sm sticky top-24">
                            <h3 class="text-xl font-serif text-gray-900 mb-6">Ringkasan Pesanan</h3>
                            
                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Subtotal</span>
                                    <span class="font-bold text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Pengiriman</span>
                                    <span class="font-bold text-gray-900">Gratis</span>
                                </div>
                            </div>

                            <div class="border-t-2 border-gray-400 pt-4 mb-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-gray-900">Total</span>
                                    <span class="text-2xl font-bold text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if(auth()->check())
                                <button type="button" onclick="showCheckoutMaintenanceModal();"
                                   class="block w-full bg-amber-600 hover:bg-amber-700 text-white py-3 px-4 font-bold tracking-wide transition text-center rounded-sm mb-3">
                                    Lanjut ke Checkout
                                </button>
                            @else
                                <button type="button" onclick="openAuthModal()"
                                   class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 px-4 font-bold tracking-wide transition rounded-sm mb-3">
                                    <i class="fas fa-lock mr-2"></i>Login untuk Checkout
                                </button>
                            @endif

                            <a href="https://wa.me/+6287796715916?text=Halo%2C%20saya%20ingin%20melakukan%20pemesanan.%20Total%20pembelian%20saya%20adalah%20Rp%20{{ $subtotal }}" 
                               target="_blank"
                               class="block w-full bg-[#1A1A1A] text-white py-3 px-4 font-bold tracking-wide hover:bg-black transition text-center rounded-sm">
                                <i class="fab fa-whatsapp mr-2"></i>Hubungi via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cart Management Script -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Decrease quantity
                        document.querySelectorAll('.decrease-qty').forEach(btn => {
                            btn.addEventListener('click', async (e) => {
                                const productId = e.target.dataset.productId;
                                const qtyInput = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
                                const newQty = Math.max(1, parseInt(qtyInput.value) - 1);
                                
                                await updateQuantity(productId, newQty, qtyInput);
                            });
                        });

                        // Increase quantity
                        document.querySelectorAll('.increase-qty').forEach(btn => {
                            btn.addEventListener('click', async (e) => {
                                const productId = e.target.dataset.productId;
                                const qtyInput = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
                                const newQty = parseInt(qtyInput.value) + 1;
                                
                                await updateQuantity(productId, newQty, qtyInput);
                            });
                        });

                        // Manual quantity input
                        document.querySelectorAll('.qty-input').forEach(input => {
                            input.addEventListener('change', async (e) => {
                                const productId = e.target.dataset.productId;
                                const newQty = Math.max(1, parseInt(e.target.value));
                                
                                await updateQuantity(productId, newQty, e.target);
                            });
                        });

                        // Remove item
                        document.querySelectorAll('.remove-item').forEach(btn => {
                            btn.addEventListener('click', async (e) => {
                                e.preventDefault();
                                const productId = e.target.dataset.productId;
                                
                                if (confirm('Hapus produk ini dari keranjang?')) {
                                    try {
                                        const response = await fetch(`/cart/${productId}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            }
                                        });

                                        if (response.ok) {
                                            location.reload();
                                        }
                                    } catch (error) {
                                        console.error('Error removing item:', error);
                                    }
                                }
                            });
                        });

                        // Update quantity function
                        async function updateQuantity(productId, quantity, inputElement) {
                            try {
                                const response = await fetch(`/cart/${productId}`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({ quantity })
                                });

                                if (response.ok) {
                                    location.reload();
                                }
                            } catch (error) {
                                console.error('Error updating quantity:', error);
                                inputElement.value = inputElement.previousValue;
                            }
                        }
                    });
                </script>
            @endif
        </div>
    </div>
</x-layouts.app>
