@extends('layouts.app')

@section('content')

{{-- Checkout Under Development Modal - DISABLED (Midtrans sudah aktif) --}}
{{--
<div id="checkout-maintenance-modal" class="fixed inset-0 z-[80] bg-black/40 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all duration-300 overflow-hidden">
        <div class="bg-white px-8 py-10 text-center border-b border-gray-100">
            <div class="mb-4">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Dalam Pengembangan</h2>
            <p class="text-gray-600 text-sm leading-relaxed">
                Fitur checkout masih dalam tahap pengembangan. Namun Anda bisa melanjutkan pembelian melalui platform e-commerce kami yang tersedia.
            </p>
        </div>
        <div class="px-8 py-8">
            <p class="text-xs font-semibold text-gray-400 uppercase letter-spacing mb-4">Platform Belanja Tersedia</p>
            <div class="space-y-3">
                <a href="https://www.tokopedia.com/joudah-official" target="_blank" rel="noopener noreferrer" 
                   class="block w-full px-4 py-3 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 group">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium text-sm">Tokopedia</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <a href="https://shopee.co.id/joudah_official?categoryId=100630&entryPoint=ShopByPDP&itemId=25524347652&upstream=search" target="_blank" rel="noopener noreferrer"
                   class="block w-full px-4 py-3 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 group">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium text-sm">Shopee</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <a href="https://api.whatsapp.com/send?phone=6289606989863&text=hallo%20saya%20sangat%20tertarik%20dengan%20produk%20joudah" target="_blank" rel="noopener noreferrer"
                   class="block w-full px-4 py-3 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 group">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium text-sm">WhatsApp</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
        <div class="bg-gray-50 px-8 py-6 border-t border-gray-100">
            <button onclick="window.history.back();" 
                    class="w-full px-4 py-2 rounded-lg bg-gray-900 hover:bg-gray-800 text-white font-medium text-sm transition-colors duration-200">
                Kembali
            </button>
        </div>
    </div>
</div>
--}}

<div class="min-h-screen bg-gray-50 pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-600 mt-2">Selesaikan pemesanan Anda</p>
        </div>

        <!-- Error Messages -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center gap-2 text-red-800">
                    <i class="fas fa-exclamation-circle"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-red-800">
                    <p class="font-medium mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Ada masalah dengan form Anda:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-6">
            <!-- Left Column - Form -->
            <div class="lg:col-span-2">
                <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm" class="space-y-6">
                    @csrf

                    <!-- Shipping Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">
                            <i class="fas fa-map-marker-alt text-amber-600 mr-3"></i>Alamat Pengiriman
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Recipient Name -->
                            <div class="sm:col-span-2">
                                <label for="shipping_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Penerima
                                </label>
                                <input type="text" id="shipping_name" name="shipping_name" 
                                    value="{{ old('shipping_name', $userProfile['name']) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    placeholder="Nama lengkap penerima" required>
                                @error('shipping_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="shipping_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon
                                </label>
                                <input type="tel" id="shipping_phone" name="shipping_phone"
                                    value="{{ old('shipping_phone', $userProfile['phone']) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    placeholder="08xxxxxxxxxx" required>
                                @error('shipping_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kode Pos
                                </label>
                                <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                                    value="{{ old('shipping_postal_code', $userProfile['postal_code']) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    placeholder="12345" required>
                                @error('shipping_postal_code')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Province -->
                            <div>
                                <label for="shipping_province" class="block text-sm font-medium text-gray-700 mb-2">
                                    Provinsi
                                </label>
                                <select id="shipping_province" name="shipping_province"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    required>
                                    <option value="">Pilih Provinsi</option>
                                    @forelse($provinces as $id => $name)
                                        <option value="{{ $name }}" data-id="{{ $id }}"
                                            {{ old('shipping_province', $userProfile['province']) === $name ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @empty
                                        <option value="">Provinsi tidak tersedia</option>
                                    @endforelse
                                </select>
                                @error('shipping_province')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- City -->
                            <div>
                                <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kota / Kabupaten
                                </label>
                                <select id="shipping_city" name="shipping_city"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    required disabled>
                                    <option value="">Pilih Kota</option>
                                </select>
                                @error('shipping_city')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="sm:col-span-2">
                                <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alamat Lengkap
                                </label>
                                <textarea id="shipping_address" name="shipping_address" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    placeholder="Jalan, nomor rumah, desa/kelurahan" required>{{ old('shipping_address', $userProfile['address']) }}</textarea>
                                @error('shipping_address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="sm:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan (Opsional)
                                </label>
                                <textarea id="notes" name="notes" rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    placeholder="Catatan untuk penjual atau kurir">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Method -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">
                            <i class="fas fa-truck text-amber-600 mr-3"></i>Metode Pengiriman
                        </h2>

                        <div id="shippingMethods" class="space-y-3">
                            <p class="text-gray-600">Pilih provinsi dan kota terlebih dahulu untuk melihat opsi pengiriman</p>
                        </div>

                        <!-- Hidden fields for shipping data -->
                        <input type="hidden" id="province_id" name="province_id">
                        <input type="hidden" id="city_id" name="city_id">
                        <input type="hidden" id="courier" name="courier">
                        <input type="hidden" id="service" name="service">
                        <input type="hidden" id="shipping_cost" name="shipping_cost" value="">
                        <input type="hidden" id="admin_fee" name="admin_fee" value="3000">

                        @error('courier')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Method Selection Moved to Snap -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                        <h2 class="text-lg font-bold text-blue-900 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>Metode Pembayaran
                        </h2>
                        <p class="text-blue-800 text-sm">
                            Pilih metode pembayaran Anda di halaman pembayaran berikutnya. Kami menyediakan berbagai pilihan pembayaran yang aman dan terpercaya.
                        </p>
                    </div>
                </form>
            </div>

            <!-- Right Column - Summary -->
            <div class="lg:col-span-1">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">
                       Ringkasan Pesanan
                    </h2>

                    <!-- Items -->
                    <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                        @foreach($cartItems as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">
                                    {{ $item->product->name }}
                                    <span class="text-gray-500">(x{{ $item->quantity }})</span>
                                </span>
                                <span class="font-medium text-gray-900">
                                    Rp{{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Totals -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal</span>
                            <span id="displaySubtotal">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Ongkos Kirim</span>
                            <span id="displayShippingCost" class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Biaya Administratif</span>
                            <span id="displayAdminFee" class="font-medium">Rp3.000</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Biaya Payment Gateway</span>
                            <span id="displayPaymentFee" class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-amber-600 pt-3 border-t border-gray-300">
                            <span>Total</span>
                            <span id="totalPrice">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" form="checkoutForm"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2"
                        id="submitBtn" disabled>
                        Bayar Sekarang
                    </button>

                    <!-- Info -->
                    <p class="text-xs text-gray-500 text-center mt-4">
                        Transaksi aman melalui Midtrans
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('shipping_province');
    const citySelect = document.getElementById('shipping_city');
    const shippingMethods = document.getElementById('shippingMethods');
    const submitBtn = document.getElementById('submitBtn');
    const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    const bankSelectionWrapper = document.getElementById('bankSelectionWrapper');
    const bankSelection = document.getElementById('bankSelection');

    console.log('[OK] Checkout script loaded successfully');
    console.log('Province select element:', provinceSelect);
    console.log('City select element:', citySelect);
    console.log('City select disabled status:', citySelect.disabled);

    // Handle province change
    provinceSelect.addEventListener('change', async function() {
        console.log('=== Province Changed ===');
        console.log('Selected option:', this.options[this.selectedIndex]);
        console.log('Selected value:', this.value);
        console.log('Dataset.id:', this.options[this.selectedIndex].dataset.id);
        
        const provinceId = this.options[this.selectedIndex].dataset.id;
        const provinceName = this.value;
        
        console.log('Province ID to send:', provinceId);
        console.log('Province Name:', provinceName);
        
        if (!provinceId || !provinceName) {
            console.log('No province selected, clearing city select');
            citySelect.innerHTML = '<option value="">Pilih Kota</option>';
            citySelect.disabled = true;
            shippingMethods.innerHTML = '<p class="text-gray-600">Pilih kota untuk melihat opsi pengiriman</p>';
            submitBtn.disabled = true;
            return;
        }

        // Show loading
        citySelect.innerHTML = '<option value="">Memuat kota...</option>';
        citySelect.disabled = true;

        try {
            const url = '{{ route("checkout.getCities") }}?province_id=' + provinceId;
            console.log('Fetching cities from:', url);
            
            const response = await fetch(url);
            console.log('Response status:', response.status);
            
            const data = await response.json();
            console.log('Cities response:', data);

            if (data.success && data.cities) {
                console.log('Success! Loading cities...');
                console.log('Total cities:', Object.keys(data.cities).length);
                
                citySelect.innerHTML = '<option value="">Pilih Kota</option>';
                
                Object.entries(data.cities).forEach(([id, name]) => {
                    const option = document.createElement('option');
                    option.value = name;
                    option.dataset.id = id;
                    option.textContent = name;
                    citySelect.appendChild(option);
                });
                
                citySelect.disabled = false;
                console.log('City dropdown enabled with', citySelect.options.length, 'options');
            } else {
                console.error('Error in response:', data);
                citySelect.innerHTML = '<option value="">Gagal memuat kota</option>';
                citySelect.disabled = true;
                alert('Gagal memuat data kota. Silakan coba lagi.');
            }
        } catch (error) {
            console.error('Error loading cities:', error);
            citySelect.innerHTML = '<option value="">Gagal memuat kota</option>';
            citySelect.disabled = true;
            alert('Gagal memuat data kota: ' + error.message);
        }
    });

    // Handle city change
    citySelect.addEventListener('change', async function() {
        console.log('City changed');
        const provinceId = provinceSelect.options[provinceSelect.selectedIndex].dataset.id;
        const cityId = this.options[this.selectedIndex].dataset.id;

        console.log('Province ID:', provinceId, 'City ID:', cityId);

        if (!provinceId || !cityId) {
            shippingMethods.innerHTML = '<p class="text-gray-600">Pilih kota untuk melihat opsi pengiriman</p>';
            document.getElementById('shipping_cost').value = '0';
            updateTotal();
            return;
        }

        // Store IDs for form submission
        document.getElementById('province_id').value = provinceId;
        document.getElementById('city_id').value = cityId;

        try {
            shippingMethods.innerHTML = '<p class="text-gray-600">Memuat opsi pengiriman...</p>';

            const url = '{{ route("checkout.getShippingCosts") }}?province_id=' + provinceId + '&city_id=' + cityId;
            console.log('Fetching shipping costs from:', url);
            
            const response = await fetch(url);
            const data = await response.json();

            console.log('Shipping costs response:', data);

            if (data.success && data.costs.length > 0) {
                shippingMethods.innerHTML = '';
                data.costs.forEach(cost => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-amber-500 hover:bg-amber-50 transition';
                    
                    const input = document.createElement('input');
                    input.type = 'radio';
                    input.name = 'shipping_method';
                    input.className = 'w-4 h-4 text-amber-600 cursor-pointer';
                    input.value = JSON.stringify(cost);
                    
                    input.addEventListener('change', function() {
                        document.getElementById('courier').value = cost.courier_code;
                        document.getElementById('service').value = cost.courier_name;
                        document.getElementById('shipping_cost').value = cost.cost;
                        updateTotal();
                        submitBtn.disabled = false;
                    });

                    const div = document.createElement('div');
                    div.className = 'ml-4 flex-1';
                    
                    const name = document.createElement('p');
                    name.className = 'font-medium text-gray-900';
                    name.textContent = cost.courier_name + ' - ' + cost.courier_code;
                    
                    const details = document.createElement('p');
                    details.className = 'text-sm text-gray-600';
                    details.textContent = 'Rp' + new Intl.NumberFormat('id-ID').format(cost.cost) + 
                        (cost.estimated_days ? ' • Est. ' + cost.estimated_days + ' hari' : '');
                    
                    div.appendChild(name);
                    div.appendChild(details);
                    
                    label.appendChild(input);
                    label.appendChild(div);
                    shippingMethods.appendChild(label);
                });
            } else {
                shippingMethods.innerHTML = '<p class="text-red-600">Tidak ada opsi pengiriman tersedia untuk lokasi ini</p>';
                submitBtn.disabled = true;
            }
        } catch (error) {
            console.error('Error loading shipping costs:', error);
            shippingMethods.innerHTML = '<p class="text-red-600">Gagal memuat opsi pengiriman</p>';
            submitBtn.disabled = true;
        }
    });

    function updateTotal() {
        const subtotal = {{ $subtotal }};
        const shippingCost = parseInt(document.getElementById('shipping_cost').value) || 0;
        const adminFee = 3000;
        const paymentGatewayFee = subtotal < 1000000
            ? 5000
            : Math.round(subtotal * 0.025);

        document.getElementById('admin_fee').value = adminFee;
        
        const total = subtotal + shippingCost + adminFee + paymentGatewayFee;

        document.getElementById('displayShippingCost').textContent = 
            'Rp' + new Intl.NumberFormat('id-ID').format(shippingCost);
        document.getElementById('displayAdminFee').textContent = 
            'Rp' + new Intl.NumberFormat('id-ID').format(adminFee);
        document.getElementById('displayPaymentFee').textContent = 
            'Rp' + new Intl.NumberFormat('id-ID').format(paymentGatewayFee);
        document.getElementById('totalPrice').textContent = 
            'Rp' + new Intl.NumberFormat('id-ID').format(total);
    }

    function togglePaymentOptionSelections() {
        const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        const isBankTransfer = selectedPaymentMethod === 'bank_transfer';
        const isCstore = selectedPaymentMethod === 'cstore';

        const bankSelectionWrapper = document.getElementById('bankSelectionWrapper');
        const bankSelection = document.getElementById('bankSelection');
        const storeSelectionWrapper = document.getElementById('storeSelectionWrapper');
        const storeSelection = document.getElementById('storeSelection');

        // Handle bank selection
        if (bankSelectionWrapper) {
            bankSelectionWrapper.classList.toggle('hidden', !isBankTransfer);
        }
        if (bankSelection) {
            bankSelection.required = isBankTransfer;
            if (!isBankTransfer) {
                bankSelection.value = '';
            }
        }

        // Handle store selection
        if (storeSelectionWrapper) {
            storeSelectionWrapper.classList.toggle('hidden', !isCstore);
        }
        if (storeSelection) {
            storeSelection.required = isCstore;
            if (!isCstore) {
                storeSelection.value = '';
            }
        }
    }

    paymentMethodInputs.forEach((input) => {
        input.addEventListener('change', togglePaymentOptionSelections);
    });

    // Handle form submission - Allow normal submission (Midtrans aktif!)
    document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
        // Validasi form sebelum submit
        const selectedShippingMethod = document.querySelector('input[name="shipping_method"]:checked');
        if (!selectedShippingMethod) {
            e.preventDefault();
            alert('Silakan pilih metode pengiriman terlebih dahulu');
            return false;
        }

        const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        if (selectedPaymentMethod === 'bank_transfer' && (!bankSelection || !bankSelection.value)) {
            e.preventDefault();
            alert('Silakan pilih bank untuk transfer terlebih dahulu');
            return false;
        }

        // Form akan submit secara normal ke checkout.process route
        console.log('Form submitted - processing checkout...');
    });

    // Initialize display with payment gateway fee
    togglePaymentOptionSelections();
    updateTotal();
});
</script>
@endsection
