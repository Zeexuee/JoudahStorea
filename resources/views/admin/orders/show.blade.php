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
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'expired' => 'bg-gray-100 text-gray-800',
                                            'refunded' => 'bg-orange-100 text-orange-800',
                                        ];
                                        $icons = [
                                            'pending' => '⏳',
                                            'processing' => '🔄',
                                            'completed' => '✓',
                                            'failed' => '✗',
                                            'cancelled' => '✗',
                                            'expired' => '⏱',
                                            'refunded' => '↩️',
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
                            @elseif($order->payment->status === 'expired' || $order->payment->status === 'cancelled')
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-red-900 mb-2">⏱ Pembayaran Tidak Selesai</h4>
                                    <p class="text-red-800">Pembayaran berakhir/terbatal. Order status otomatis set ke "Dibatalkan".</p>
                                </div>
                            @elseif($order->payment->status === 'refunded')
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-sm">
                                    <h4 class="font-bold text-orange-900 mb-2">↩️ Dana Dikembalikan</h4>
                                    <p class="text-orange-800">Pembayaran sudah direfund. Status order disesuaikan sebagai dibatalkan.</p>
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
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Pengiriman</h2>

                    @if($order->shipping)
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Kurir</p>
                                    <p class="font-medium text-gray-900">{{ $order->shipping->courier_name ?? $order->shipping->courier ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Status</p>
                                    <p class="font-medium text-gray-900">{{ $shippingStatuses[$order->shipping->status] ?? $order->shipping->status }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-gray-600">Nomor Resi</p>
                                    <p class="font-medium text-gray-900">{{ $order->shipping->tracking_number ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 mb-4">
                            <button type="button" id="shippingEditToggleButton" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition" onclick="toggleShippingEditForm()">
                                Edit Data Pengiriman
                            </button>
                            @if($order->shipping->tracking_number)
                                <a href="{{ route('admin.orders.print-receipt', $order->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                    Print Resi
                                </a>
                            @endif
                        </div>

                        <form id="shippingEditForm" action="{{ route('admin.orders.updateShippingStatus', $order->id) }}" method="POST" class="hidden border border-gray-200 rounded-lg p-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                                    <input type="text" name="courier" value="{{ old('courier', $order->shipping->courier_name ?? $order->shipping->courier) }}"
                                           placeholder="Contoh: JNE, POS, TIKI"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pengiriman (Opsional)</label>
                                    <select name="shipping_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                        <option value="">Otomatis: Dalam Perjalanan saat resi diisi</option>
                                        <option value="out_for_delivery" @selected(old('shipping_status', $order->shipping->status) === 'out_for_delivery')>Sedang Diantar</option>
                                        <option value="delivered" @selected(old('shipping_status', $order->shipping->status) === 'delivered')>Terkirim</option>
                                        <option value="failed" @selected(old('shipping_status', $order->shipping->status) === 'failed')>Gagal Dikirim</option>
                                        <option value="returned" @selected(old('shipping_status', $order->shipping->status) === 'returned')>Dikembalikan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi</label>
                                <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->shipping->tracking_number) }}"
                                       placeholder="Contoh: JNE1234567890"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono" required>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                    Simpan
                                </button>
                                <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium transition" onclick="hideShippingEditForm()">
                                    Batal
                                </button>
                            </div>
                        </form>
                    @else
                        <form action="{{ route('admin.orders.updateShippingStatus', $order->id) }}" method="POST" class="border border-gray-200 rounded-lg p-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                                    <input type="text" name="courier" value="{{ old('courier') }}" placeholder="Contoh: JNE, POS, TIKI"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pengiriman (Opsional)</label>
                                    <select name="shipping_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                        <option value="">Otomatis: Dalam Perjalanan saat resi diisi</option>
                                        <option value="out_for_delivery" @selected(old('shipping_status') === 'out_for_delivery')>Sedang Diantar</option>
                                        <option value="delivered" @selected(old('shipping_status') === 'delivered')>Terkirim</option>
                                        <option value="failed" @selected(old('shipping_status') === 'failed')>Gagal Dikirim</option>
                                        <option value="returned" @selected(old('shipping_status') === 'returned')>Dikembalikan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi</label>
                                <input type="text" name="tracking_number" value="{{ old('tracking_number') }}" placeholder="Contoh: JNE1234567890"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono" required>
                            </div>

                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                Simpan Data Pengiriman
                            </button>

                            <p class="text-xs text-gray-500 mt-3">Saat resi disimpan, status pengiriman otomatis menjadi Dalam Perjalanan.</p>
                        </form>
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

<script>
function toggleShippingEditForm() {
    const form = document.getElementById('shippingEditForm');
    const button = document.getElementById('shippingEditToggleButton');

    if (!form || !button) {
        return;
    }

    const isHidden = form.classList.contains('hidden');
    form.classList.toggle('hidden', !isHidden);
    button.textContent = isHidden ? 'Tutup Edit' : 'Edit Data Pengiriman';
}

function hideShippingEditForm() {
    const form = document.getElementById('shippingEditForm');
    const button = document.getElementById('shippingEditToggleButton');

    if (form) {
        form.classList.add('hidden');
    }

    if (button) {
        button.textContent = 'Edit Data Pengiriman';
    }
}

@if($errors->has('courier') || $errors->has('tracking_number') || $errors->has('shipping_status'))
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('shippingEditForm');
    const button = document.getElementById('shippingEditToggleButton');

    if (form) {
        form.classList.remove('hidden');
    }

    if (button) {
        button.textContent = 'Tutup Edit';
    }
});
@endif
</script>

@endsection
