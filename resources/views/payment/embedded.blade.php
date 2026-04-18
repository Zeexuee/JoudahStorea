@extends('layouts.app')

@push('head')
<!-- Midtrans Snap.js for Embedded Payment -->
@php
    $snapUrl = 'https://app' . (config('payment.mindtrans.mode') === 'sandbox' ? '.sandbox' : '') . '.midtrans.com/snap/snap.js';
    $clientKey = config('payment.mindtrans.api_secret');
@endphp
<script type="text/javascript"
    src="{{ $snapUrl }}"
    data-client-key="{{ $clientKey }}"></script>
<style>
    .snap-container {
        min-height: 500px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 pt-24 pb-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ $returnToUrl ?? route('checkout.show') }}" class="text-amber-600 hover:text-amber-700 font-medium inline-flex items-center gap-2 mb-4">
                <i class="fas fa-arrow-left"></i>
                {{ $returnToLabel ?? 'Kembali' }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Pembayaran Pesanan</h1>
            <p class="text-gray-600 mt-2">Nomor Pesanan: <span class="font-semibold">{{ $order->order_number }}</span></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Formulir Pembayaran</h2>

                    <!-- Loading State -->
                    <div id="paymentLoading" class="text-center py-12">
                        <div class="inline-block">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-600"></div>
                        </div>
                        <p class="text-gray-600 mt-4">Sedang memuat formulir pembayaran...</p>
                    </div>

                    <!-- Snap Payment Container (Hidden until ready) -->
                    <div id="snapPaymentContainer" class="hidden snap-container"></div>

                    <!-- Error State -->
                    <div id="paymentError" class="hidden">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-exclamation-circle text-red-600 mt-1"></i>
                                <div>
                                    <h3 class="font-semibold text-red-900">Gagal Memuat Pembayaran</h3>
                                    <p class="text-red-700 text-sm mt-1" id="paymentErrorMessage"></p>
                                    <button onclick="location.reload()" class="mt-3 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                                        Coba Lagi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success State -->
                    <div id="paymentSuccess" class="hidden">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                            <i class="fas fa-check-circle text-green-600 text-5xl mb-4"></i>
                            <h3 class="text-xl font-bold text-green-900 mb-2">Pembayaran Berhasil!</h3>
                            <p class="text-green-700 mb-4">Terima kasih atas pembayaran Anda. Status pesanan Anda sekarang: Sudah Dibayar.</p>
                            <a href="{{ route('orders.show', $order) }}" class="inline-block px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                                Lihat Detail Pesanan
                            </a>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div id="paymentInfo" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
                        <h4 class="font-semibold text-blue-900 mb-2">Informasi:</h4>
                        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                            <li>Layanan pembayaran disediakan oleh Midtrans yang terpercaya</li>
                            <li>Data pembayaran Anda dienkripsi dan aman</li>
                            <li>Anda akan kembali ke sini setelah pembayaran selesai</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Pesanan</h3>
                    
                    <div class="space-y-3 mb-4 pb-4 border-b">
                        @foreach($order->items as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">
                                {{ $item->product ? $item->product->name : 'Product' }} (x{{ $item->quantity }})
                            </span>
                            <span class="font-medium">Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>Rp{{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 0, ',', '.') }}</span>
                        </div>
                        @if($order->shipping)
                        <div class="flex justify-between text-gray-600">
                            <span>Ongkir</span>
                            <span>Rp{{ number_format($order->shipping->cost, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold text-amber-600 pt-2 border-t">
                            <span>Total</span>
                            <span>Rp{{ number_format($amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t">
                        <div class="text-xs text-gray-500 space-y-1">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-clock"></i>
                                <span>Batas waktu: 24 jam</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-shield-alt"></i>
                                <span>Transaksi aman dengan Midtrans</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const snapToken = '{{ $snapToken }}';
    const snapContainer = document.getElementById('snapPaymentContainer');
    const paymentLoading = document.getElementById('paymentLoading');
    const paymentError = document.getElementById('paymentError');
    const paymentErrorMessage = document.getElementById('paymentErrorMessage');
    const paymentSuccess = document.getElementById('paymentSuccess');
    const paymentInfo = document.getElementById('paymentInfo');
    const orderId = '{{ $order->id }}';

    // Wait for Snap.js to load
    waitForSnap(function() {
        initializePayment();
    });

    function waitForSnap(callback) {
        let attempts = 0;
        const maxAttempts = 50; // 5 seconds max wait

        console.log('Waiting for Snap.js to load...');

        const checkSnap = setInterval(function() {
            attempts++;

            if (typeof window.snap !== 'undefined') {
                clearInterval(checkSnap);
                console.log('Snap.js loaded successfully');
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkSnap);
                console.error('Snap.js failed to load after 5 seconds');
                showError('Gagal memuat library pembayaran. Silakan refresh halaman atau hubungi administrator.');
            }
        }, 100);
    }

    function initializePayment() {
        try {
            // Hide loading, show info
            paymentLoading.classList.add('hidden');
            snapContainer.classList.remove('hidden');
            paymentInfo.classList.remove('hidden');

            // Setup Snap payment handler
            window.snap.embed(snapToken, {
                embedId: 'snapPaymentContainer',
                onSuccess: handlePaymentSuccess,
                onPending: handlePaymentPending,
                onError: handlePaymentError,
                onClose: handlePaymentClose
            });

            console.log('Snap payment embedded successfully');
        } catch (error) {
            console.error('Error embedding Snap payment:', error);
            showError('Gagal membuat form pembayaran: ' + error.message);
        }
    }

    function handlePaymentSuccess(result) {
        console.log('Payment successful:', result);
        
        // Hide payment form
        snapContainer.classList.add('hidden');
        paymentInfo.classList.add('hidden');
        
        // Show success message
        paymentSuccess.classList.remove('hidden');
        paymentLoading.classList.add('hidden');

        // Optionally verify payment status after a delay
        setTimeout(function() {
            verifyPaymentStatus();
        }, 2000);
    }

    function handlePaymentPending(result) {
        console.log('Payment pending:', result);
        // Payment is pending - user needs to complete it
        // Snap.js will handle this and show appropriate UI
    }

    function handlePaymentError(result) {
        console.error('Payment error:', result);
        showError('Terjadi kesalahan pembayaran: ' + (result.status_message || 'Unknown error'));
    }

    function handlePaymentClose() {
        console.log('Payment popup/form closed');
        // User closed the payment form - they can click the button again to retry
    }

    function showError(message) {
        paymentLoading.classList.add('hidden');
        snapContainer.classList.add('hidden');
        paymentInfo.classList.add('hidden');
        paymentError.classList.remove('hidden');
        paymentErrorMessage.textContent = message;
    }

    function verifyPaymentStatus() {
        // Call verify endpoint to sync payment status
        fetch(`/payment/${orderId}/verify`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // If response is a redirect, the payment was successful
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            console.log('Payment verification result:', data);
        })
        .catch(error => {
            console.log('Payment verification error (non-critical):', error);
            // Don't show error - payment might still be successful
        });
    }
});
</script>
@endsection
