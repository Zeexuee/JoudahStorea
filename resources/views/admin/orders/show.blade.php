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
                <a href="{{ route('admin.orders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg font-medium transition">
                    ← Kembali
                </a>
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
                </div>

                <!-- Payment Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Status Pembayaran</h2>
                    <form action="{{ route('admin.orders.updatePaymentStatus', $order) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select name="payment_status" id="payment_status" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                    @foreach($paymentStatuses as $key => $label)
                                        <option value="{{ $key }}" {{ $order->payment?->status === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                    Perbarui Pembayaran
                                </button>
                            </div>
                        </div>
                    </form>
                    @if($order->payment)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Metode Pembayaran</p>
                                    <p class="font-medium text-gray-900">{{ $order->payment->payment_method ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Tanggal Pembayaran</p>
                                    <p class="font-medium text-gray-900">{{ $order->payment->paid_at?->format('d M Y H:i') ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">No. Transaksi</p>
                                    <p class="font-medium text-gray-900">{{ $order->payment->transaction_id ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Total Pembayaran</p>
                                    <p class="font-medium text-gray-900">Rp{{ number_format($order->payment->amount, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Shipping Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Status Pengiriman</h2>
                    <form action="{{ route('admin.orders.updateShippingStatus', $order) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="shipping_status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status Pengiriman
                                </label>
                                <select name="shipping_status" id="shipping_status" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                    @foreach($shippingStatuses as $key => $label)
                                        <option value="{{ $key }}" {{ $order->shipping?->status === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="courier" class="block text-sm font-medium text-gray-700 mb-2">
                                        Kurir
                                    </label>
                                    <input type="text" name="courier" id="courier" 
                                        value="{{ $order->shipping?->courier ?? '' }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="JNE, POS, TIKI, dll...">
                                </div>
                                <div>
                                    <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nomor Resi
                                    </label>
                                    <input type="text" name="tracking_number" id="tracking_number" 
                                        value="{{ $order->shipping?->tracking_number ?? '' }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                        placeholder="Nomor resi / tracking number...">
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                Perbarui Pengiriman
                            </button>
                        </div>
                    </form>
                    @if($order->shipping)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Kurir</p>
                                    <p class="font-medium text-gray-900">{{ $order->shipping->courier ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Biaya Pengiriman</p>
                                    <p class="font-medium text-gray-900">Rp{{ number_format($order->shipping->shipping_cost, 0, ',', '.') }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-600">Nomor Resi</p>
                                    <p class="font-medium text-gray-900">{{ $order->shipping->tracking_number ?? '-' }}</p>
                                </div>
                            </div>
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
                                Rp{{ number_format($order->total_price - ($order->shipping->shipping_cost ?? 0), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Ongkir</span>
                            <span class="font-medium text-gray-900">
                                Rp{{ number_format($order->shipping->shipping_cost ?? 0, 0, ',', '.') }}
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
@endsection
