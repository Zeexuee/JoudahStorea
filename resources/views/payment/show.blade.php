@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pt-20 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <a href="{{ route('orders.show', $order) }}" class="text-amber-600 hover:text-amber-700 font-medium flex items-center gap-2 mb-4">
                <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
            </a>
            <h1 class="text-4xl font-bold text-gray-900">Pembayaran</h1>
            <p class="text-gray-600 mt-2">Order #{{ $order->order_number }}</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-600 mr-3 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h3 class="font-semibold text-red-800">Ada kesalahan</h3>
                        <ul class="text-red-700 text-sm mt-2 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex gap-3">
                <i class="fas fa-exclamation-circle text-red-600 flex-shrink-0 mt-0.5"></i>
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex gap-3">
                <i class="fas fa-info-circle text-blue-600 flex-shrink-0 mt-0.5"></i>
                <p class="text-blue-700">{{ session('info') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Payment Info -->
            <div class="lg:col-span-2">
                <!-- Payment Status Card -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Status Pembayaran</h2>
                        <span class="inline-block px-4 py-2 rounded-lg text-sm font-medium
                            @if($payment->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($payment->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($payment->status === 'completed') bg-green-100 text-green-800
                            @elseif($payment->status === 'failed') bg-red-100 text-red-800
                            @elseif($payment->status === 'expired') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $payment->status_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Pembayaran</p>
                            <p class="text-2xl font-bold text-amber-600">
                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Payment Method</p>
                            <p class="text-lg font-medium text-gray-900">
                                {{ $payment->payment_method ?? 'Belum dipilih' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Ref. Number</p>
                            <p class="text-sm font-mono text-gray-900">{{ $payment->reference_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions -->
                @if($payment->status === 'pending')
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Petunjuk Pembayaran
                        </h3>
                        <ol class="space-y-3 text-blue-900 text-sm">
                            <li class="flex gap-3">
                                <span class="font-bold flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center">1</span>
                                <span>Klik tombol "Lanjut ke Pembayaran" di bawah</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-bold flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center">2</span>
                                <span>Pilih metode pembayaran yang Anda inginkan (Transfer Bank, E-Wallet, QRIS, dll)</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-bold flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center">3</span>
                                <span>Selesaikan pembayaran sesuai petunjuk</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="font-bold flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center">4</span>
                                <span>Anda akan diarahkan kembali ke sini setelah pembayaran</span>
                            </li>
                        </ol>
                    </div>

                    <form action="{{ route('payment.process', $order) }}" method="POST" id="paymentForm">
                        @csrf
                        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                            <i class="fas fa-lock"></i>
                            Lanjut ke Pembayaran
                        </button>
                    </form>
                @elseif($payment->status === 'completed')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <div class="flex gap-3">
                            <i class="fas fa-check-circle text-green-600 text-2xl flex-shrink-0"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-green-900 mb-1">
                                    Pembayaran Berhasil!
                                </h3>
                                <p class="text-green-800">
                                    Pesanan Anda telah dikonfirmasi. Kami akan segera memproses pengiriman.
                                    Anda dapat melacak status pengiriman di menu "Pesanan Saya".
                                </p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('orders.show', $order) }}" class="block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Lihat Pesanan
                    </a>
                @elseif($payment->status === 'failed' || $payment->status === 'expired')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-red-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-times-circle"></i>
                            Pembayaran Gagal
                        </h3>
                        <p class="text-red-800 mb-4">
                            @if($payment->status === 'failed')
                                Pembayaran Anda gagal diproses. Silahkan coba lagi.
                            @else
                                Waktu pembayaran telah berakhir. Silahkan lakukan pembayaran ulang.
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('payment.process', $order) }}" method="POST" id="retryPaymentForm">
                        @csrf
                        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2 mb-3">
                            <i class="fas fa-redo"></i>
                            Coba Lagi
                        </button>
                    </form>

                    <form action="{{ route('payment.cancel', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-4 rounded-lg transition duration-200">
                            Batalkan Pesanan
                        </button>
                    </form>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2 flex items-center gap-2">
                            <i class="fas fa-hourglass-half"></i>
                            Pembayaran Dalam Proses
                        </h3>
                        <p class="text-yellow-800">
                            Pembayaran Anda sedang diverifikasi. Silahkan tunggu beberapa saat dan periksa kembali status pembayaran.
                        </p>
                    </div>

                    <button type="button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200" 
                        onclick="checkPaymentStatus()">
                        <i class="fas fa-sync-alt mr-2"></i>Periksa Status Pembayaran
                    </button>
                @endif
            </div>

            <!-- Right Column - Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200">
                        Detail Pesanan
                    </h2>

                    <!-- Order Items -->
                    <div class="space-y-3 mb-4 pb-4 border-b border-gray-200">
                        @foreach($order->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700">
                                    {{ \Str::limit($item->product->name, 20) }}
                                    <span class="text-gray-500">(x{{ $item->quantity }})</span>
                                </span>
                                <span class="font-medium text-gray-900">
                                    Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Shipping Info -->
                    @if($order->shipping)
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600 mb-1">Pengiriman</p>
                            <p class="font-medium text-gray-900">{{ $order->shipping->courier_name }}</p>
                            <p class="text-sm text-gray-600">Rp{{ number_format($order->shipping->cost, 0, ',', '.') }}</p>
                        </div>
                    @endif

                    <!-- Total -->
                    <div class="flex justify-between text-lg font-bold text-amber-600">
                        <span>Total Pembayaran</span>
                        <span>Rp{{ number_format($payment->amount, 0, ',', '.') }}</span>
                    </div>

                    <!-- Shipping Address -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="font-semibold text-gray-900 mb-2 text-sm">Alamat Pengiriman</h3>
                        <p class="text-sm text-gray-700">
                            {{ $order->shipping_name }}<br>
                            {{ $order->shipping_address }}<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}<br>
                            <span class="text-gray-600">{{ $order->shipping_phone }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkPaymentStatus() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memeriksa...';

    fetch('{{ route("payment.checkStatus", $order) }}')
        .then(response => response.json())
        .then(data => {
            if (data.is_paid) {
                // Refresh page to show updated status
                window.location.reload();
            } else {
                alert('Pembayaran masih dalam proses. Silahkan coba lagi dalam beberapa saat.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Periksa Status Pembayaran';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memeriksa status pembayaran');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Periksa Status Pembayaran';
        });
}
</script>
@endsection
