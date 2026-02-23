@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Pesanan {{ $order->order_number }}
                    </h1>
                    <p class="text-gray-600">
                        Dipesan pada {{ $order->created_at->format('d M Y H:i') }}
                    </p>
                </div>
                <div class="flex gap-3">
                    @if(in_array($order->status, ['pending', 'processing']))
                        <button onclick="document.getElementById('cancelModal').showModal()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            🗑️ Batalkan Pesanan
                        </button>
                    @endif
                    <a href="{{ route('admin.orders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg font-medium transition">
                        ← Kembali
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Status Pesanan</h2>
                    @if($order->payment?->status === 'completed')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-sm">
                            <p class="text-green-800"><strong>✓ Status otomatis ter-perbarui</strong> karena pembayaran sudah berhasil. Admin tidak dapat mengubah status secara manual untuk order yang sudah dibayar.</p>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-4">
                            <p class="text-gray-600 text-sm mb-2">Status Pesanan Saat Ini</p>
                            <p class="font-bold text-gray-900 text-lg">{{ $statuses[$order->status] ?? $order->status }}</p>
                        </div>
                    @else
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Ubah Status
                                    </label>
                                    <select name="status" id="status" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                    Perbarui
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <!-- Payment Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Status Pembayaran</h2>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 text-sm">
                        <p class="text-blue-800"><strong>ℹ️ Auto-Update Enabled</strong> - Status pembayaran dikelola sistem secara otomatis. Admin tidak bisa mengubah status secara manual.</p>
                    </div>

                    @if($order->payment)
                        <div class="space-y-4">
                            <!-- Payment Status Badge -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-600 text-sm mb-2">Status Pembayaran Saat Ini</p>
                                <div class="flex items-center gap-2">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'expired' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $icons = [
                                            'pending' => '⏳',
                                            'completed' => '✓',
                                            'failed' => '✗',
                                            'cancelled' => '✗',
                                            'expired' => '⏱',
                                        ];
                                    @endphp
                                    <span class="px-4 py-2 rounded-full font-semibold text-lg {{ $statusColors[$order->payment->status] ?? 'bg-gray-100' }}">
                                        {{ $icons[$order->payment->status] ?? '' }} {{ $paymentStatuses[$order->payment->status] ?? $order->payment->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Payment Details -->
                            <div class="grid grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <p class="text-gray-600">Gateway</p>
                                    <p class="font-medium text-gray-900">{{ $order->payment->payment_gateway ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Jumlah</p>
                                    <p class="font-medium text-gray-900">Rp{{ number_format($order->payment->amount, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Tanggal Pembayaran</p>
                                    <p class="font-medium text-gray-900">{{ $order->payment->paid_at?->format('d M Y H:i') ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">No. Referensi</p>
                                    <p class="font-medium text-gray-900 text-xs">{{ $order->payment->reference_number ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Auto-Update Info -->
                            @if($order->payment->status === 'pending')
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-amber-900 mb-2">⏳ Menunggu Pembayaran</h4>
                                    <p class="text-amber-800 mb-2">Customer perlu menyelesaikan pembayaran di payment gateway. Status akan otomatis terupdate ketika pembayaran berhasil.</p>
                                    <p class="text-amber-700 text-xs">Sistem akan automatically set order status ke "Diproses" saat pembayaran confirmed.</p>
                                </div>
                            @elseif($order->payment->status === 'completed')
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-green-900 mb-2">✓ Pembayaran Berhasil</h4>
                                    <p class="text-green-800">Pembayaran telah confirmed pada {{ $order->payment->paid_at?->format('d M Y H:i') }}. Order status otomatis berubah ke "Diproses".</p>
                                </div>
                            @elseif($order->payment->status === 'failed')
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-red-900 mb-2">✗ Pembayaran Gagal</h4>
                                    <p class="text-red-800">Pembayaran gagal diproses. Order status otomatis set ke "Dibatalkan".</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 text-gray-600">
                            <p>📋 Belum ada data pembayaran untuk order ini.</p>
                        </div>
                    @endif
                </div>

                <!-- Shipping Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Status Pengiriman</h2>
                    
                    @if($order->shipping)
                        <!-- Edit Shipping Form -->
                        <form action="{{ route('admin.orders.updateShippingStatus', $order->id) }}" method="POST" class="mb-6">
                            @csrf
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                                <h3 class="font-semibold text-amber-900 mb-4">📝 Update Data Pengiriman</h3>
                                
                                <!-- Info Box: Cara Dapatin Nomor Resi -->
                                <div class="bg-white border border-amber-300 rounded-lg p-3 mb-4 text-sm">
                                    <p class="font-semibold text-amber-900 mb-2">💡 Cara Dapatin Nomor Resi:</p>
                                    <ul class="list-disc list-inside text-amber-800 space-y-1">
                                        <li><strong>Raja Ongkir:</strong> Jika integrasi Raja Ongkir aktif, nomor resi otomatis dari API saat order dikirim</li>
                                        <li><strong>Manual:</strong> Cek website kurir (JNE, POS, TIKI, Shopee) → cari nomor resi ada di bukti pengiriman</li>
                                        <li><strong>Kurir:</strong> Minta langsung kepada kurir saat pickup barang</li>
                                        <li><strong>Email:</strong> Biasanya kurir kirim email dengan nomor resi + tracking link</li>
                                    </ul>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <!-- Courier Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                                        <input type="text" name="courier" value="{{ $order->shipping->courier_name ?? '' }}" 
                                               placeholder="Misal: JNE, POS, TIKI, Shopee Express, etc"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                        <small class="text-gray-500">Nama kurir pengiriman</small>
                                    </div>
                                    
                                    <!-- Shipping Status -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Pengiriman *</label>
                                        <select name="shipping_status" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                            <option value="">-- Pilih Status --</option>
                                            <option value="pending" @selected($order->shipping->status === 'pending')>📦 Menunggu Pickup</option>
                                            <option value="picked_up" @selected($order->shipping->status === 'picked_up')>🚚 Sudah Diambil</option>
                                            <option value="in_transit" @selected($order->shipping->status === 'in_transit')>🚛 Dalam Perjalanan</option>
                                            <option value="out_for_delivery" @selected($order->shipping->status === 'out_for_delivery')>📍 Sedang di Anter</option>
                                            <option value="delivered" @selected($order->shipping->status === 'delivered')>✓ Terima</option>
                                            <option value="failed" @selected($order->shipping->status === 'failed')>✗ Gagal Dikirim</option>
                                            <option value="returned" @selected($order->shipping->status === 'returned')>↩️ Dikembalikan</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Tracking Number -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi / Tracking Number *</label>
                                    <input type="text" name="tracking_number" value="{{ $order->shipping->tracking_number ?? '' }}" 
                                           placeholder="Misal: JNE1234567890 atau nomor resi dari kurir"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono"
                                           required>
                                    <small class="text-gray-500">Masukkan nomor resi yang diberikan kurir</small>
                                </div>
                                
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium py-2 px-4 rounded-lg transition">
                                        💾 Simpan Data Pengiriman
                                    </button>
                                    <small class="text-gray-600 flex items-center">* Wajib diisi untuk print resi</small>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                            <p class="text-amber-800"><strong>⚠️ Belum ada data pengiriman</strong> - Silakan isi form di bawah untuk membuat data pengiriman</p>
                            <form action="{{ route('admin.orders.updateShippingStatus', $order->id) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                                        <input type="text" name="courier" placeholder="Misal: JNE, POS, TIKI, Shopee Express, etc"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Pengiriman *</label>
                                        <select name="shipping_status" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                            <option value="">-- Pilih Status --</option>
                                            <option value="pending">📦 Menunggu Pickup</option>
                                            <option value="picked_up">🚚 Sudah Diambil</option>
                                            <option value="in_transit">🚛 Dalam Perjalanan</option>
                                            <option value="out_for_delivery">📍 Sedang di Anter</option>
                                            <option value="delivered">✓ Terima</option>
                                            <option value="failed">✗ Gagal Dikirim</option>
                                            <option value="returned">↩️ Dikembalikan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi / Tracking Number *</label>
                                    <input type="text" name="tracking_number" placeholder="Misal: JNE1234567890"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono"
                                           required>
                                </div>
                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium py-2 px-4 rounded-lg transition">
                                    ➕ Buat Data Pengiriman
                                </button>
                            </form>
                        </div>
                    @endif
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 text-sm">
                        <p class="text-blue-800"><strong>ℹ️ Catatan:</strong> Saat status diubah, order status akan otomatis terupdate. Gunakan tombol Print Resi setelah nomor resi diisi.</p>
                    </div>

                    @if($order->shipping)
                        <!-- Current Shipping Status Display -->
                        <h3 class="text-md font-bold text-gray-900 mb-3 mt-6">📊 Status Pengiriman Saat Ini</h3>
                        <div class="space-y-4">
                            <!-- Shipping Status Badge -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-600 text-sm mb-2">Status Pengiriman Saat Ini</p>
                                <div class="flex items-center gap-2">
                                    @php
                                        $shippingStatusColors = [
                                            'pending' => 'bg-gray-100 text-gray-800',
                                            'picked_up' => 'bg-blue-100 text-blue-800',
                                            'in_transit' => 'bg-indigo-100 text-indigo-800',
                                            'out_for_delivery' => 'bg-purple-100 text-purple-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'returned' => 'bg-orange-100 text-orange-800',
                                        ];
                                        $shippingIcons = [
                                            'pending' => '📦',
                                            'picked_up' => '🚚',
                                            'in_transit' => '🚛',
                                            'out_for_delivery' => '📍',
                                            'delivered' => '✓',
                                            'failed' => '✗',
                                            'returned' => '↩️',
                                        ];
                                    @endphp
                                    <span class="px-4 py-2 rounded-full font-semibold text-lg {{ $shippingStatusColors[$order->shipping->status] ?? 'bg-gray-100' }}">
                                        {{ $shippingIcons[$order->shipping->status] ?? '' }} {{ $shippingStatuses[$order->shipping->status] ?? $order->shipping->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Shipping Details -->
                            <div class="grid grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <p class="text-gray-600">Kurir</p>
                                    <p class="font-medium text-gray-900">{{ $order->shipping->courier ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Biaya Pengiriman</p>
                                    <p class="font-medium text-gray-900">Rp{{ number_format($order->shipping->cost, 0, ',', '.') }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-600">Nomor Resi</p>
                                    <p class="font-medium text-gray-900">{{ $order->shipping->tracking_number ?? '-' }}</p>
                                </div>
                                @if($order->shipping->estimated_delivery)
                                    <div class="col-span-2">
                                        <p class="text-gray-600">Estimasi Tiba</p>
                                        <p class="font-medium text-gray-900">{{ $order->shipping->estimated_delivery->format('d M Y') }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Print Receipt Button -->
                            @if($order->shipping->tracking_number)
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.orders.print-receipt', $order->id) }}" 
                                       target="_blank"
                                       class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                        🖨️ Print Resi
                                    </a>
                                </div>
                            @endif

                            @if($order->shipping->status === 'pending')
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-gray-900 mb-2">📦 Menunggu Pickup</h4>
                                    <p class="text-gray-700">Barang belum diambil kurir. Status akan terupdate saat kurir pickup.</p>
                                </div>
                            @elseif($order->shipping->status === 'delivered')
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-green-900 mb-2">✓ Barang Terkirim</h4>
                                    <p class="text-green-800">Barang telah diterima recipient. Order status otomatis berubah ke "Terkirim".</p>
                                </div>
                            @elseif($order->shipping->status === 'in_transit')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-blue-900 mb-2">🚛 Dalam Pengiriman</h4>
                                    <p class="text-blue-800">Barang sedang dalam perjalanan. Order status otomatis berubah ke "Dikirim".</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 text-gray-600">
                            <p>📦 Belum ada data pengiriman untuk order ini.</p>
                        </div>
                    @endif
                </div>

                <!-- Items -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Item Pesanan</h2>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            <div class="flex gap-4 pb-4 border-b border-gray-200 last:border-0">
                                <div class="w-20 h-20 bg-gray-200 rounded-lg flex-shrink-0 overflow-hidden">
                                    @if($item->product && $item->product->main_image)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                            <span class="text-gray-500 text-xs text-center">No Image</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $item->product?->name ?? 'Produk Dihapus' }}</p>
                                    <p class="text-sm text-gray-600">SKU: {{ $item->product?->sku ?? '-' }}</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Qty: <span class="font-medium">{{ $item->quantity }}</span> × 
                                        Rp<span class="font-medium">{{ number_format($item->price, 0, ',', '.') }}</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">
                                        Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Informasi Pelanggan</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Nama</p>
                            <p class="font-medium text-gray-900">{{ $order->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium text-gray-900">{{ $order->user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Telepon</p>
                            <p class="font-medium text-gray-900">{{ $order->user->phone ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Alamat Pengiriman</h2>
                    <div class="space-y-2 text-sm">
                        <p class="font-medium text-gray-900">{{ $order->shipping_name }}</p>
                        <p class="text-gray-600">{{ $order->shipping_address }}</p>
                        <p class="text-gray-600">{{ $order->shipping_city }}, {{ $order->shipping_province }}</p>
                        <p class="text-gray-600">{{ $order->shipping_postal_code }}</p>
                        <p class="text-gray-600">Telepon: {{ $order->shipping_phone }}</p>
                    </div>
                </div>

                <!-- Price Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Harga</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-gray-900">
                                Rp{{ number_format($order->total_price - ($order->shipping->cost ?? 0), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Ongkir</span>
                            <span class="font-medium text-gray-900">
                                Rp{{ number_format($order->shipping->cost ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between">
                            <span class="font-semibold text-gray-900">Total</span>
                            <span class="text-lg font-bold text-amber-600">
                                Rp{{ number_format($order->total_price, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Timeline</h2>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-3 h-3 bg-amber-600 rounded-full mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="font-medium text-gray-900">Pesanan Dibuat</p>
                                <p class="text-sm text-gray-600">{{ $order->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        @if($order->payment && $order->payment->paid_at)
                            <div class="flex gap-3">
                                <div class="w-3 h-3 bg-green-600 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Pembayaran Diterima</p>
                                    <p class="text-sm text-gray-600">{{ $order->payment->paid_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        @if($order->updated_at && $order->updated_at !== $order->created_at)
                            <div class="flex gap-3">
                                <div class="w-3 h-3 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Terakhir Diperbarui</p>
                                    <p class="text-sm text-gray-600">{{ $order->updated_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cancel Order -->
<dialog id="cancelModal" class="rounded-lg shadow-xl max-w-md w-full">
    <div class="p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Batalkan Pesanan</h2>
        
        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Pembatalan <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="cancel_reason" 
                    id="cancel_reason"
                    rows="4"
                    placeholder="Jelaskan alasan mengapa pesanan ini dibatalkan..."
                    required
                    minlength="5"
                    maxlength="500"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
                ></textarea>
                <p class="text-xs text-gray-500 mt-2">Minimal 5 karakter, maksimal 500 karakter</p>
                @error('cancel_reason')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-6">
                <p class="text-sm text-red-800">
                    <strong>⚠️ PERHATIAN:</strong><br>
                    • Pesanan akan diatakan dengan status "Dibatalkan"<br>
                    • Pembayaran akan dikembalikan ke customer<br>
                    • Aksi ini tidak dapat dibatalkan
                </p>
            </div>

            <div class="flex gap-3">
                <button 
                    type="button"
                    onclick="document.getElementById('cancelModal').close()"
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 px-4 py-2 rounded-lg font-medium transition"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition"
                >
                    🗑️ Batalkan Pesanan
                </button>
            </div>
        </form>
    </div>
</dialog>

@endsection
