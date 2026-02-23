@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pt-20 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Riwayat Pesanan</h1>
            <p class="text-gray-600">Lihat semua pesanan dan status pengiriman Anda</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden sticky top-24">
                    <nav class="flex flex-col">
                        <a href="{{ route('profile.show') }}" class="px-6 py-4 border-l-4 border-transparent text-gray-700 hover:bg-gray-50 hover:border-amber-300 flex items-center gap-3 transition">
                            <i class="fas fa-user text-gray-400"></i>
                            Profil Saya
                        </a>
                        <a href="{{ route('orders.index') }}" class="px-6 py-4 border-l-4 border-amber-600 bg-amber-50 text-amber-700 font-medium flex items-center gap-3">
                            <i class="fas fa-shopping-bag text-amber-600"></i>
                            Riwayat Pesanan
                        </a>
                        <form method="POST" action="{{ route('auth.logout') }}" class="border-t">
                            @csrf
                            <button type="submit" class="w-full text-left px-6 py-4 text-red-600 hover:bg-red-50 flex items-center gap-3 transition">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3" id="orders-container">
                @if ($orders->count() > 0)
                    <div class="space-y-4">
                        @foreach ($orders as $order)
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                                <!-- Order Header -->
                                <div class="border-b border-gray-200 px-6 py-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                Order #{{ $order->order_number }}
                                            </h3>
                                            <p class="text-gray-600 text-sm">
                                                {{ $order->created_at->locale('id')->translatedFormat('d F Y H:i') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                                @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                                                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif" data-status="{{ $order->status }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="px-6 py-4">
                                    <div class="space-y-3">
                                        @foreach ($order->items as $item)
                                            <div class="flex items-start gap-4">
                                                @if($item->product->images && count($item->product->images) > 0)
                                                    <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                                                        <img src="{{ asset('storage/' . $item->product->images[0]) }}" alt="{{ $item->product->name }}"
                                                            class="w-full h-full object-cover">
                                                    </div>
                                                @else
                                                    <div class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-image text-gray-400"></i>
                                                    </div>
                                                @endif
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900">{{ $item->product->name }}</h4>
                                                    <p class="text-gray-600 text-sm">
                                                        {{ $item->quantity }}x × Rp{{ number_format($item->price, 0, ',', '.') }}
                                                    </p>
                                                </div>
                                                <div class="flex-shrink-0 text-right">
                                                    <p class="font-semibold text-gray-900">
                                                        Rp{{ number_format($item->quantity * $item->price, 0, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Order Total -->
                                    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                                        <span class="font-medium text-gray-900">Total Pesanan:</span>
                                        <span class="text-2xl font-bold text-amber-600">
                                            Rp{{ number_format($order->total_price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Order Footer -->
                                <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                                    <div class="text-sm text-gray-600">
                                        <p><strong>Penerima:</strong> {{ $order->shipping_name }}</p>
                                        <p><strong>Alamat:</strong> {{ Str::limit($order->shipping_address, 50) }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition font-medium">
                                            <i class="fas fa-eye"></i>
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if ($orders->hasPages())
                        <div class="mt-8">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-lg shadow-sm py-16 text-center">
                        <div class="mb-4">
                            <i class="fas fa-inbox text-gray-400 text-6xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Pesanan</h3>
                        <p class="text-gray-600 mb-6">Anda belum membuat pesanan. Mulai belanja sekarang!</p>
                        <a href="/" class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-medium py-2 px-6 rounded-lg transition">
                            <i class="fas fa-shopping-bag mr-2"></i>Mulai Berbelanja
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@endsection
