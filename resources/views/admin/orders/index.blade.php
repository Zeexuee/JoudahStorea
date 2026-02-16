@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Kelola Pesanan</h1>
            <p class="text-gray-600">Daftar semua pesanan pelanggan</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            Cari No. Pesanan / Nama / Telepon
                        </label>
                        <input type="text" name="search" id="search" 
                            value="{{ request('search') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            placeholder="No. pesanan, nama, atau telepon...">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Pesanan
                        </label>
                        <select name="status" id="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 px-4 py-2 rounded-lg font-medium transition text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($orders->count())
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">No. Pesanan</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Total</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status Pesanan</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pembayaran</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pengiriman</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-amber-600">{{ $order->order_number }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $order->shipping_name }}</p>
                                            <p class="text-sm text-gray-600">{{ $order->shipping_phone }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $order->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        Rp{{ number_format($order->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $colors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'processing' => 'bg-blue-100 text-blue-800',
                                                'shipped' => 'bg-purple-100 text-purple-800',
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $colors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statuses[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($order->payment)
                                            @php
                                                $paymentColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'failed' => 'bg-red-100 text-red-800',
                                                    'expired' => 'bg-orange-100 text-orange-800',
                                                ];
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $paymentColors[$order->payment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($order->payment->status) }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($order->shipping)
                                            <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $order->shipping->status)) }}</span>
                                        @else
                                            <span class="text-gray-500 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                            class="text-amber-600 hover:text-amber-700 font-medium">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-600 text-lg">Tidak ada pesanan ditemukan</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
