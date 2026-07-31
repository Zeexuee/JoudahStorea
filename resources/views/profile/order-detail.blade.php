@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pt-20 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('orders.index') }}" class="text-amber-600 hover:text-amber-700 font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat Pesanan
            </a>
        </div>

        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-6 border-b border-gray-200">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Order #{{ $order->order_number }}</h1>
                    <p class="text-gray-600">
                        <i class="fas fa-calendar mr-2"></i>
                        {{ $order->created_at->locale('id')->translatedFormat('d F Y H:i') }}
                    </p>
                </div>
                <span class="inline-block px-4 py-2 rounded-lg text-lg font-medium
                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                    @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                    @elseif($order->status === 'delivered') bg-green-100 text-green-800
                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ $order->status_label }}
                </span>
            </div>

            <!-- Order Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Total Pesanan</p>
                    <p class="text-2xl font-bold text-amber-600">Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm mb-1">Jumlah Item</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $order->items->sum('quantity') }} Produk</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm mb-1">Status Pembayaran</p>
                    <p class="text-2xl font-bold">
                        @if($order->payment)
                            @if($order->payment->status === 'pending')
                                <span class="text-yellow-600">{{ $order->payment->status_label }}</span>
                            @elseif($order->payment->status === 'processing')
                                <span class="text-blue-600">{{ $order->payment->status_label }}</span>
                            @elseif($order->payment->status === 'completed')
                                <span class="text-green-600">{{ $order->payment->status_label }}</span>
                            @elseif($order->payment->status === 'failed')
                                <span class="text-red-600">{{ $order->payment->status_label }}</span>
                            @elseif($order->payment->status === 'expired')
                                <span class="text-red-600">{{ $order->payment->status_label }}</span>
                            @elseif($order->payment->status === 'cancelled')
                                <span class="text-red-600">{{ $order->payment->status_label }}</span>
                            @elseif($order->payment->status === 'refunded')
                                <span class="text-orange-600">{{ $order->payment->status_label }}</span>
                            @else
                                <span class="text-gray-600">{{ $order->payment->status_label }}</span>
                            @endif
                        @else
                            <span class="text-gray-600">Belum ada pembayaran</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- Items Header -->
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="font-semibold text-gray-900">Produk Pesanan</h2>
                    </div>

                    <!-- Items List -->
                    <div class="divide-y divide-gray-200">
                        @foreach ($order->items as $item)
                            <div class="px-6 py-6 flex gap-4">
                                <!-- Product Image -->
                                @if($item->product->images && count($item->product->images) > 0)
                                    <div class="flex-shrink-0 w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="{{ asset('storage/' . $item->product->images[0]) }}" alt="{{ $item->product->name }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-2xl"></i>
                                    </div>
                                @endif

                                <!-- Product Info -->
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        <a href="{{ route('product.detail', $item->product->slug) }}" class="hover:text-amber-600 transition">
                                            {{ $item->product->name }}
                                        </a>
                                    </h3>
                                    <div class="text-gray-600 text-sm space-y-1">
                                        <p>Harga: <strong>Rp{{ number_format($item->price, 0, ',', '.') }}{{ $item->product->is_per_gram ? ' / gram' : '' }}</strong></p>
                                        <p>Jumlah: <strong>{{ $item->quantity }} {{ $item->product->is_per_gram ? 'gram' : '' }}</strong></p>
                                        <p>Subtotal: <strong class="text-amber-600">Rp{{ number_format($item->quantity * $item->price, 0, ',', '.') }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Total -->
                    <div class="bg-gray-50 px-6 py-6 border-t border-gray-200">
                        <div class="space-y-3">
                            <div class="flex justify-between text-gray-700">
                                <span>Subtotal</span>
                                <span>Rp{{ number_format($order->total_price - ($order->shipping?->cost ?? 0), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>Ongkos Kirim</span>
                                @if($order->shipping)
                                    <span class="font-medium">
                                        {{ $order->shipping->courier_name }} ({{ $order->shipping->service }})
                                        <br>
                                        <span class="text-sm">Rp{{ number_format($order->shipping->cost, 0, ',', '.') }}</span>
                                    </span>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                            <div class="flex justify-between text-lg font-bold text-amber-600 pt-3 border-t border-gray-300">
                                <span>Total</span>
                                <span>Rp{{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $userComment = $order->comments->firstWhere('user_id', auth()->id());
            @endphp

            <!-- Comment Section -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-gray-900">Komentar Pesanan</h2>
                        @if($order->status === 'delivered')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Bisa dikomentari
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                Tersedia setelah pesanan diterima
                            </span>
                        @endif
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        @if($order->status === 'delivered')
                            <form method="POST" action="{{ route('comments.store', $order) }}" class="space-y-4">
                                @csrf

                                <div>
                                    <span class="block text-sm font-medium text-gray-700 mb-2">Rating</span>
                                    <div class="flex gap-1">
                                        @for($rating = 1; $rating <= 5; $rating++)
                                            <label class="cursor-pointer text-3xl leading-none transition {{ (string) old('rating', $userComment?->rating) === (string) $rating ? 'text-amber-500' : 'text-gray-300 hover:text-amber-300' }}">
                                                <input
                                                    type="radio"
                                                    name="rating"
                                                    value="{{ $rating }}"
                                                    class="sr-only"
                                                    {{ (string) old('rating', $userComment?->rating) === (string) $rating ? 'checked' : '' }}
                                                    required>
                                                <svg aria-hidden="true" class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1 1 0 00.95.69h4.173c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.286 3.966c.3.921-.755 1.688-1.54 1.118l-3.377-2.455a1 1 0 00-1.175 0L5.58 17.03c-.785.57-1.84-.197-1.54-1.118l1.286-3.966a1 1 0 00-.364-1.118L1.585 8.373c-.783-.57-.38-1.81.588-1.81h4.173a1 1 0 00.95-.69L9.049 2.927z"/>
                                                </svg>
                                            </label>
                                        @endfor
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Klik bintang untuk memberi nilai dari 1 sampai 5.</p>
                                    @error('rating')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $userComment ? 'Edit komentar Anda' : 'Tulis komentar Anda' }}
                                    </label>
                                    <textarea
                                        id="content"
                                        name="content"
                                        rows="4"
                                        class="w-full rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500"
                                        placeholder="Bagikan pengalaman Anda setelah menerima pesanan ini"
                                        required>{{ old('content', $userComment?->content) }}</textarea>
                                    @error('content')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition">
                                        {{ $userComment ? 'Perbarui Komentar' : 'Kirim Komentar' }}
                                    </button>
                                </div>
                            </form>

                            @if($userComment)
                                <div class="pt-1">
                                    <form method="POST" action="{{ route('comments.destroy', [$order, $userComment]) }}" onsubmit="return confirm('Hapus komentar ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition">
                                            Hapus Komentar
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-gray-600">
                                Komentar baru bisa ditambahkan setelah status pesanan berubah menjadi <span class="font-semibold text-gray-900">delivered</span>.
                            </div>
                        @endif

                        @if($order->comments->count() > 0)
                            <div class="space-y-4 pt-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Riwayat Komentar</h3>

                                <div class="space-y-4">
                                    @foreach($order->comments as $comment)
                                        <div class="rounded-lg border border-gray-200 p-4 {{ $comment->user_id === auth()->id() ? 'bg-amber-50 border-amber-200' : 'bg-white' }}">
                                            <div class="flex items-center justify-between gap-3 mb-2">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $comment->user->name ?? 'User' }}</p>
                                                    <p class="text-xs text-gray-500">{{ $comment->created_at->locale('id')->translatedFormat('d F Y H:i') }}</p>
                                                </div>
                                                @if($comment->user_id === auth()->id())
                                                    <span class="text-xs font-medium text-amber-700">Komentar Anda</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1 mb-3 text-amber-500">
                                                @if(!is_null($comment->rating))
                                                    @for($rating = 1; $rating <= 5; $rating++)
                                                        <svg class="w-4 h-4 inline-block {{ $comment->rating >= $rating ? 'text-amber-500' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1 1 0 00.95.69h4.173c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.286 3.966c.3.921-.755 1.688-1.54 1.118l-3.377-2.455a1 1 0 00-1.175 0L5.58 17.03c-.785.57-1.84-.197-1.54-1.118l1.286-3.966a1 1 0 00-.364-1.118L1.585 8.373c-.783-.57-.38-1.81.588-1.81h4.173a1 1 0 00.95-.69L9.049 2.927z"/>
                                                        </svg>
                                                    @endfor
                                                    <span class="ml-2 text-xs font-medium text-gray-500">{{ $comment->rating }}/5</span>
                                                @else
                                                    <span class="text-xs font-medium text-gray-500">Belum ada rating</span>
                                                @endif
                                            </div>
                                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Shipping and Payment Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Payment Status -->
                @if($order->payment)
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-credit-card text-amber-600"></i>
                            Status Pembayaran
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Status</p>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                    @if($order->payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->payment->status === 'processing') bg-blue-100 text-blue-800
                                    @elseif($order->payment->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->payment->status === 'failed') bg-red-100 text-red-800
                                    @elseif($order->payment->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($order->payment->status === 'refunded') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $order->payment->status_label }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Metode Pembayaran</p>
                                <p class="font-medium text-gray-900">{{ $order->payment->payment_gateway }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Ref Pembayaran</p>
                                <p class="font-mono text-sm text-gray-900">{{ $order->payment->reference_number }}</p>
                            </div>
                            @if($order->payment->paid_at)
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Tanggal Pembayaran</p>
                                    <p class="text-sm text-gray-900">{{ $order->payment->paid_at->locale('id')->translatedFormat('d F Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                        @if($order->payment->status === 'pending')
                            <a href="{{ route('payment.show', $order) }}" class="block mt-4 text-center bg-amber-600 hover:bg-amber-700 text-white font-medium py-2 px-4 rounded-lg transition">
                                Lanjut ke Pembayaran
                            </a>
                        @endif
                    </div>
                @endif
                
                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-amber-600"></i>
                        Alamat Pengiriman
                    </h3>
                    <div class="space-y-3 text-gray-700 text-sm">
                        <div>
                            <p class="text-sm text-gray-600">Nama Penerima</p>
                            <p class="font-medium">{{ $order->shipping_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Nomor Telepon</p>
                            <p class="font-medium">{{ $order->shipping_phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Alamat</p>
                            <p class="font-medium">{{ $order->shipping_address }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Kota</p>
                                <p class="font-medium">{{ $order->shipping_city }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Provinsi</p>
                                <p class="font-medium">{{ $order->shipping_province }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kode Pos</p>
                            <p class="font-medium">{{ $order->shipping_postal_code }}</p>
                        </div>
                    </div>
                </div>

                <!-- Shipping Status -->
                @if($order->shipping)
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-truck text-amber-600"></i>
                            Info Pengiriman
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-gray-600 mb-1">Kurir</p>
                                <p class="font-medium text-gray-900">{{ $order->shipping->courier_name }} - {{ $order->shipping->service }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 mb-1">Status Pengiriman</p>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                    @if($order->shipping->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif(in_array($order->shipping->status, ['picked_up', 'in_transit', 'out_for_delivery'])) bg-blue-100 text-blue-800
                                    @elseif($order->shipping->status === 'delivered') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ $order->shipping->status_label }}
                                </span>
                            </div>
                            @if($order->shipping->tracking_number)
                                <div>
                                    <p class="text-gray-600 mb-1">Nomor Resi</p>
                                    <p class="font-mono text-gray-900">{{ $order->shipping->tracking_number }}</p>
                                </div>
                            @endif
                            @if($order->shipping->estimated_delivery)
                                <div>
                                    <p class="text-gray-600 mb-1">Perkiraan Tiba</p>
                                    <p class="text-gray-900">{{ $order->shipping->estimated_delivery->locale('id')->translatedFormat('d F Y') }}</p>
                                </div>
                            @endif

                            @if($order->status === 'shipped')
                                <div class="pt-2 border-t border-gray-200">
                                    <form method="POST" action="{{ route('orders.confirmDelivered', $order) }}" onsubmit="return confirm('Konfirmasi bahwa barang sudah sampai?')">
                                        @csrf
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                                            Konfirmasi Barang Sudah Sampai
                                        </button>
                                    </form>
                                </div>
                            @elseif($order->status === 'delivered')
                                <div class="pt-2 border-t border-gray-200 text-xs text-gray-600">
                                    Dikonfirmasi diterima oleh {{ $order->delivered_confirmed_by === 'admin' ? 'admin' : 'user' }}
                                    @if($order->delivered_confirmed_at)
                                        pada {{ $order->delivered_confirmed_at->locale('id')->translatedFormat('d F Y H:i') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Status Timeline -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        Timeline
                    </h3>
                    <div class="relative">
                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Pesanan Dibuat</p>
                                    <p class="text-sm text-gray-600">{{ $order->created_at->locale('id')->translatedFormat('d F Y H:i') }}</p>
                                </div>
                            </div>

                            @if($order->payment && $order->payment->paid_at)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Pembayaran Berhasil</p>
                                        <p class="text-sm text-gray-600">{{ $order->payment->paid_at->locale('id')->translatedFormat('d F Y H:i') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-yellow-300 rounded-full flex items-center justify-center text-white text-sm">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Menunggu Pembayaran</p>
                                        <p class="text-sm text-gray-600">Belum dibayar</p>
                                    </div>
                                </div>
                            @endif

                            @if(in_array($order->status, ['processing', 'shipped', 'delivered']))
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Sudah Dibayar</p>
                                        <p class="text-sm text-gray-600">Pembayaran tervalidasi</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex gap-4 opacity-50">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-600">Sudah Dibayar</p>
                                        <p class="text-sm text-gray-500">Menunggu</p>
                                    </div>
                                </div>
                            @endif

                            @if(in_array($order->status, ['shipped', 'delivered']))
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Pesanan Dikirim</p>
                                        <p class="text-sm text-gray-600">Dalam perjalanan</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex gap-4 opacity-50">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-600">Pesanan Dikirim</p>
                                        <p class="text-sm text-gray-500">Menunggu</p>
                                    </div>
                                </div>
                            @endif

                            @if($order->status === 'delivered')
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Pesanan Diterima</p>
                                        <p class="text-sm text-gray-600">Selesai</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex gap-4 opacity-50">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-600">Pesanan Diterima</p>
                                        <p class="text-sm text-gray-500">Menunggu</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ratingInputs = Array.from(document.querySelectorAll('input[name="rating"]'));
    if (!ratingInputs.length) return;

    const labels = ratingInputs.map(i => i.closest('label'));

    function applyVisual(selected) {
        labels.forEach(label => {
            const input = label.querySelector('input[name="rating"]');
            const val = Number(input.value);
            if (!isNaN(selected) && val <= selected) {
                label.classList.add('text-amber-500');
                label.classList.remove('text-gray-300');
            } else {
                label.classList.remove('text-amber-500');
                label.classList.add('text-gray-300');
            }
        });
    }

    // initialize from checked input
    const checked = document.querySelector('input[name="rating"]:checked');
    const current = checked ? Number(checked.value) : 0;
    applyVisual(current);

    labels.forEach(label => {
        const input = label.querySelector('input[name="rating"]');
        const val = Number(input.value);

        label.addEventListener('click', function () {
            input.checked = true;
            applyVisual(val);
        });

        label.addEventListener('mouseenter', function () {
            applyVisual(val);
        });

        label.addEventListener('mouseleave', function () {
            const sel = Number(document.querySelector('input[name="rating"]:checked')?.value || 0);
            applyVisual(sel);
        });
    });
});
</script>
@endpush
