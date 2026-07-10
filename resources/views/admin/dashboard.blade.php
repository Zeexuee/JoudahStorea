@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Admin</h1>
            <p class="text-gray-600">Ringkasan semua pesanan dan pembayaran</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Orders -->
            <div class="bg-white rounded-lg shadow p-6">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Pesanan</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_orders'] }}</p>
                </div>
            </div>

            <!-- Delivered -->
            <div class="bg-white rounded-lg shadow p-6">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Pesanan Terkirim</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['delivered_orders'] }}</p>
                </div>
            </div>

            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total User</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['total_users'] }}</p>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow p-6">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-blue-600 mt-2">Rp{{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Detailed Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-gray-600 text-sm">Sudah Dibayar</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['processing_orders'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-gray-600 text-sm">Dikirim</p>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['shipped_orders'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-gray-600 text-sm">Pembayaran Gagal</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['failed_payments'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-gray-600 text-sm">Dibatalkan</p>
                <p class="text-2xl font-bold text-gray-600">{{ $stats['cancelled_orders'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-gray-600 text-sm">Menunggu Pembayaran</p>
                <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_payments'] }}</p>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Pesanan Terbaru</h2>
                    <a href="{{ route('admin.orders.index') }}" class="text-amber-600 hover:text-amber-700 font-medium">
                        Lihat Semua →
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">No. Pesanan</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pelanggan</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Total</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pembayaran</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-amber-600">{{ $order->order_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $order->shipping_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $order->user->email }}</p>
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
                                        $labels = [
                                            'pending' => 'Menunggu Pembayaran',
                                            'processing' => 'Sudah Dibayar',
                                            'shipped' => 'Dikirim',
                                            'delivered' => 'Terkirim',
                                            'cancelled' => 'Dibatalkan',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $colors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $labels[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->payment)
                                        @php
                                            $paymentColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'processing' => 'bg-blue-100 text-blue-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                'expired' => 'bg-orange-100 text-orange-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                'refunded' => 'bg-gray-100 text-gray-800',
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $paymentColors[$order->payment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $order->payment->status_label }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                        class="text-amber-600 hover:text-amber-700 font-medium">
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                                    Belum ada pesanan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
