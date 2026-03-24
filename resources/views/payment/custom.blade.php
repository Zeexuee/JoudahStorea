@extends('layouts.app')

@push('head')
<!-- Midtrans Snap.js -->
@php
    $snapUrl = 'https://app' . (config('payment.mindtrans.mode') === 'sandbox' ? '.sandbox' : '') . '.midtrans.com/snap/snap.js';
    $clientKey = config('payment.mindtrans.api_secret');
@endphp
<script type="text/javascript"
    src="{{ $snapUrl }}"
    data-client-key="{{ $clientKey }}"></script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('checkout.show') }}" class="text-amber-600 hover:text-amber-700 font-medium inline-flex items-center gap-2 mb-4">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Checkout
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Pembayaran Pesanan</h1>
            <p class="text-gray-600 mt-2">Nomor Pesanan: <span class="font-semibold">{{ $order->order_number }}</span></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Payment Methods -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        Pilih Metode Pembayaran: {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                    </h2>

                    @if($payment->payment_method === 'gopay')
                        <div id="gopayPaymentContent" class="space-y-4">
                            <div class="text-center">
                                <div class="inline-block p-4 bg-green-50 rounded-lg mb-4">
                                    <i class="fas fa-mobile-alt text-green-600 text-6xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">GoPay Payment</h3>
                                <p class="text-gray-600 mb-4">Scan QR code dengan aplikasi Gojek Anda</p>
                            </div>
                            
                            <!-- Snap.js will render payment UI here -->
                            <div id="gopayQRCode" class="min-h-[400px]"></div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                <h4 class="font-semibold text-blue-900 mb-2">Cara Pembayaran:</h4>
                                <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                                    <li>Buka aplikasi Gojek</li>
                                    <li>Pilih menu "Pay" dan tap "Scan QR"</li>
                                    <li>Scan QR code di atas</li>
                                    <li>Konfirmasi pembayaran</li>
                                </ol>
                            </div>
                        </div>
                    @elseif($payment->payment_method === 'shopeepay')
                        <div id="shopeepayPaymentContent" class="space-y-4">
                            <div class="text-center">
                                <div class="inline-block p-4 bg-orange-50 rounded-lg mb-4">
                                    <i class="fas fa-wallet text-orange-600 text-6xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">ShopeePay Payment</h3>
                                <p class="text-gray-600 mb-4">Anda akan diarahkan ke aplikasi ShopeePay</p>
                            </div>
                            
                            <div class="text-center">
                                <button id="shopeepayButton" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-8 rounded-lg transition">
                                    Bayar dengan ShopeePay
                                </button>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                <p class="text-sm text-blue-800">
                                    Klik tombol di atas untuk melanjutkan ke ShopeePay
                                </p>
                            </div>
                        </div>
                    @elseif($payment->payment_method === 'qris')
                        <div id="qrisPaymentContent" class="space-y-4">
                            <div class="text-center">
                                <div class="inline-block p-4 bg-purple-50 rounded-lg mb-4">
                                    <i class="fas fa-qrcode text-purple-600 text-6xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">QRIS Payment</h3>
                                <p class="text-gray-600 mb-4">Scan QR code dengan aplikasi e-wallet Anda</p>
                            </div>
                            
                            <!-- Snap.js will render payment UI here -->
                            <div id="qrisQRCode" class="min-h-[400px]"></div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                <h4 class="font-semibold text-blue-900 mb-2">Cara Pembayaran:</h4>
                                <p class="text-sm text-blue-800 mb-2">Gunakan aplikasi e-wallet yang mendukung QRIS:</p>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="px-2 py-1 bg-white rounded border border-blue-200">GoPay</span>
                                    <span class="px-2 py-1 bg-white rounded border border-blue-200">OVO</span>
                                    <span class="px-2 py-1 bg-white rounded border border-blue-200">Dana</span>
                                    <span class="px-2 py-1 bg-white rounded border border-blue-200">ShopeePay</span>
                                    <span class="px-2 py-1 bg-white rounded border border-blue-200">LinkAja</span>
                                </div>
                            </div>
                        </div>
                    @elseif($payment->payment_method === 'bank_transfer')
                        <div id="bankTransferContent" class="space-y-4">
                            <div class="text-center mb-4">
                                <div class="inline-block p-4 bg-blue-50 rounded-lg mb-4">
                                    <i class="fas fa-university text-blue-600 text-6xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Transfer Bank</h3>
                                <p class="text-gray-600">Pilih bank untuk melakukan transfer</p>
                            </div>

                            <div id="bankOptions" class="space-y-3">
                                <p class="text-gray-600 text-center">Loading bank options...</p>
                            </div>
                        </div>
                    @elseif($payment->payment_method === 'credit_card')
                        <div id="creditCardContent" class="space-y-4">
                            <div class="text-center mb-4">
                                <div class="inline-block p-4 bg-blue-50 rounded-lg mb-4">
                                    <i class="fas fa-credit-card text-blue-600 text-6xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Kartu Kredit/Debit</h3>
                            </div>

                            <form id="creditCardForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kartu</label>
                                    <input type="text" id="card_number" maxlength="19" placeholder="1234 5678 9012 3456"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Berlaku Hingga</label>
                                        <input type="text" id="card_expiry" maxlength="5" placeholder="MM/YY"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">CVV</label>
                                        <input type="text" id="card_cvv" maxlength="3" placeholder="123"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                    </div>
                                </div>
                                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-lg">
                                    Bayar Sekarang
                                </button>
                            </form>

                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-600">
                                <i class="fas fa-lock mr-1"></i> Data kartu Anda dienkripsi dan aman
                            </div>
                        </div>
                    @elseif($payment->payment_method === 'cstore')
                        <div id="cstoreContent" class="space-y-4">
                            <div class="text-center mb-4">
                                <div class="inline-block p-4 bg-orange-50 rounded-lg mb-4">
                                    <i class="fas fa-store text-orange-600 text-6xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Bayar di Toko</h3>
                                <p class="text-gray-600">Pilih toko untuk melakukan pembayaran</p>
                            </div>

                            <div id="cstoreSelectionButtons" class="space-y-3">
                                <button onclick="selectStore('indomaret')" class="w-full p-4 border-2 border-gray-200 rounded-lg hover:border-amber-500 hover:bg-amber-50 transition text-left">
                                    <div class="font-semibold text-gray-900">Indomaret</div>
                                    <div class="text-sm text-gray-600">Bayar di kasir Indomaret terdekat</div>
                                </button>
                                <button onclick="selectStore('alfamart')" class="w-full p-4 border-2 border-gray-200 rounded-lg hover:border-amber-500 hover:bg-amber-50 transition text-left">
                                    <div class="font-semibold text-gray-900">Alfamart</div>
                                    <div class="text-sm text-gray-600">Bayar di kasir Alfamart terdekat</div>
                                </button>
                            </div>

                            <div id="cstoreResultArea" class="hidden mt-4"></div>

                            <div id="paymentCodeDisplayTemplate" class="hidden">
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 mb-2">Kode Pembayaran:</h4>
                                    <div class="text-2xl font-bold text-amber-600 mb-2" id="paymentCode">-</div>
                                    <p class="text-sm text-gray-600">Tunjukkan kode ini ke kasir</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Status -->
                    <div id="paymentStatus" class="mt-6 hidden">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                            <i class="fas fa-check-circle text-green-600 text-4xl mb-2"></i>
                            <h3 class="text-lg font-bold text-green-900">Pembayaran Berhasil!</h3>
                            <p class="text-green-700 text-sm">Terima kasih atas pembayaran Anda</p>
                        </div>
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
                            <span>Rp{{ number_format($payment->amount, 0, ',', '.') }}</span>
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
    const paymentMethod = '{{ $payment->payment_method }}';
    const paymentId = '{{ $payment->id }}';
    const selectedStore = '{{ $payment->metadata['selected_store'] ?? '' }}';
    const directMethods = ['qris', 'gopay', 'shopeepay', 'bank_transfer', 'cstore'];
    const useDirectFlow = directMethods.includes(paymentMethod);
    
    // Show loading indicator
    showLoading();
    
    if (useDirectFlow) {
        if (paymentMethod === 'cstore') {
            if (selectedStore) {
                processPayment({ store: selectedStore });
            } else {
                renderCstoreSelection();
            }
        } else {
            processPayment();
        }
    } else {
        // Wait for Snap.js to load before processing payment
        waitForSnap(function() {
            processPayment();
        });
    }
    
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
    
    function showLoading() {
        const containerId = getPrimaryContainerId(paymentMethod);
        const container = containerId ? document.getElementById(containerId) : null;
        if (container) {
            if (paymentMethod === 'cstore') {
                container.classList.remove('hidden');
            }
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-amber-600 mb-4"></div>
                    <p class="text-gray-600">Memuat metode pembayaran...</p>
                </div>
            `;
        }
    }
    
    function processPayment(extraPayload = {}) {
        console.log('Processing payment for method:', paymentMethod);
        
        fetch(`/payment/custom/${paymentId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(extraPayload)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Payment API response:', data);
            
            if (data.success && data.data.snap_token) {
                console.log('Initializing Snap with token:', data.data.snap_token);

                const snapCallbacks = {
                    onSuccess: function(result) {
                        console.log('Payment success:', result);
                        alert('Pembayaran berhasil!');
                        window.location.href = '{{ route("orders.show", $order->id) }}';
                    },
                    onPending: function(result) {
                        console.log('Payment pending:', result);
                        alert('Menunggu pembayaran. Kami akan memberi tahu Anda setelah pembayaran dikonfirmasi.');
                        startStatusCheck();
                    },
                    onError: function(result) {
                        console.error('Payment error:', result);
                        alert('Pembayaran gagal: ' + (result.status_message || 'Terjadi kesalahan'));
                        showError('Pembayaran gagal. Silakan coba lagi.');
                    },
                    onClose: function() {
                        console.log('Payment popup closed');
                        startStatusCheck();
                    }
                };

                const embedId = getEmbedId(paymentMethod);
                const useEmbed = ['qris', 'gopay'].includes(paymentMethod) &&
                    embedId &&
                    document.getElementById(embedId) &&
                    typeof window.snap.embed === 'function';

                if (useEmbed) {
                    window.snap.embed(data.data.snap_token, {
                        embedId: embedId,
                        ...snapCallbacks
                    });
                } else {
                    window.snap.pay(data.data.snap_token, snapCallbacks);
                }
            } else if (data.success && useDirectFlow) {
                renderDirectPaymentDetails(data.data || {});
                startStatusCheck();
            } else {
                let errorMsg = data.error || 'Gagal memuat metode pembayaran';

                if (String(errorMsg).toLowerCase().includes('not activated')) {
                    if (paymentMethod === 'qris') {
                        errorMsg = 'QRIS belum aktif di akun Midtrans production Anda. Aktifkan QRIS di dashboard Midtrans atau gunakan metode lain yang sudah aktif.';
                    } else {
                        errorMsg = 'Metode pembayaran ini belum aktif di akun Midtrans production Anda. Silakan aktifkan channel di dashboard Midtrans.';
                    }
                }

                console.error('Payment API error:', errorMsg);
                showError(errorMsg);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showError('Terjadi kesalahan koneksi');
        });
    }
    
    function showError(message) {
        const containerId = getPrimaryContainerId(paymentMethod);
        const container = containerId ? document.getElementById(containerId) : null;
        if (container) {
            if (paymentMethod === 'cstore') {
                container.classList.remove('hidden');
            }
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="text-red-500 mb-4">
                        <i class="fas fa-exclamation-circle text-6xl"></i>
                    </div>
                    <p class="text-gray-800 font-semibold mb-2">${message}</p>
                    <button onclick="location.reload()" class="mt-4 px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                        Coba Lagi
                    </button>
                </div>
            `;
        }
    }

    function renderDirectQr(qrUrl, expiryTime) {
        const containerId = getPrimaryContainerId(paymentMethod);
        const container = containerId ? document.getElementById(containerId) : null;

        if (!container) {
            return;
        }

        const expiryText = expiryTime
            ? `<p class="text-xs text-gray-500 mt-3">Berlaku sampai: ${expiryTime}</p>`
            : '';

        container.innerHTML = `
            <div class="flex flex-col items-center justify-center">
                <img src="${qrUrl}" alt="QRIS Code" class="w-72 h-72 object-contain border border-gray-200 rounded-lg p-2 bg-white" />
                ${expiryText}
            </div>
        `;
    }

    function renderDirectPaymentDetails(details) {
        if (details.qr_url && (paymentMethod === 'qris' || paymentMethod === 'gopay')) {
            renderDirectQr(details.qr_url, details.expiry_time || null);
            return;
        }

        if (paymentMethod === 'shopeepay') {
            renderShopeePay(details);
            return;
        }

        if (paymentMethod === 'bank_transfer') {
            renderBankTransfer(details);
            return;
        }

        if (paymentMethod === 'cstore') {
            renderCstore(details);
            return;
        }

        if (details.qr_url) {
            renderDirectQr(details.qr_url, details.expiry_time || null);
        }
    }

    function renderShopeePay(details) {
        const container = document.getElementById('shopeepayPaymentContent');
        if (!container) return;

        const deeplink = details.deeplink_url;
        container.innerHTML = `
            <div class="text-center">
                <div class="inline-block p-4 bg-orange-50 rounded-lg mb-4">
                    <i class="fas fa-wallet text-orange-600 text-6xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">ShopeePay Payment</h3>
                <p class="text-gray-600 mb-4">Lanjutkan pembayaran melalui aplikasi ShopeePay</p>
                ${deeplink ? `<a id="shopeepayDeepLink" href="${deeplink}" target="_blank" rel="noopener noreferrer" class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-8 rounded-lg transition">Buka ShopeePay</a>` : '<p class="text-red-600">Deep link ShopeePay tidak tersedia</p>'}
                ${deeplink ? `<p class="text-xs text-gray-500 mt-3 break-all">Link: ${deeplink}</p>` : ''}
                <p class="text-xs text-gray-500 mt-2">Klik tombol di atas untuk membuka ShopeePay. Setelah bayar, halaman ini akan cek status otomatis.</p>
            </div>
        `;
    }

    function renderBankTransfer(details) {
        const container = document.getElementById('bankOptions');
        if (!container) return;

        if (details.va_number) {
            container.innerHTML = `
                <div class="p-4 border border-blue-200 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800 mb-2">Transfer ke Virtual Account berikut:</p>
                    <p class="font-semibold text-gray-900 mb-1">Bank: ${details.bank || '-'}</p>
                    <p class="text-2xl font-bold text-blue-700 tracking-wide">${details.va_number}</p>
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="bank-btn p-3 border rounded-lg" data-bank="bca">BCA</button>
                <button type="button" class="bank-btn p-3 border rounded-lg" data-bank="bni">BNI</button>
                <button type="button" class="bank-btn p-3 border rounded-lg" data-bank="bri">BRI</button>
                <button type="button" class="bank-btn p-3 border rounded-lg" data-bank="permata">Permata</button>
            </div>
        `;

        container.querySelectorAll('.bank-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const bank = this.getAttribute('data-bank');
                showLoading();
                processPayment({ bank: bank });
            });
        });
    }

    function renderCstore(details) {
        const resultArea = document.getElementById('cstoreResultArea');
        const template = document.getElementById('paymentCodeDisplayTemplate');
        if (!resultArea || !template) return;

        if (details.payment_code) {
            resultArea.classList.remove('hidden');
            resultArea.innerHTML = template.innerHTML;

            const codeEl = resultArea.querySelector('#paymentCode');
            const title = resultArea.querySelector('h4');
            const note = resultArea.querySelector('p');

            if (codeEl) {
                codeEl.textContent = details.payment_code;
            }
            if (title) {
                title.textContent = `Kode Pembayaran ${details.store || 'CStore'}:`;
            }
            if (note) {
                note.textContent = 'Tunjukkan kode ini ke kasir untuk menyelesaikan pembayaran.';
            }
            return;
        }

        resultArea.classList.add('hidden');
        resultArea.innerHTML = '';
    }

    function renderCstoreSelection() {
        const resultArea = document.getElementById('cstoreResultArea');
        if (resultArea) {
            resultArea.classList.add('hidden');
            resultArea.innerHTML = '';
        }
    }

    function getPrimaryContainerId(method) {
        switch (method) {
            case 'gopay':
                return 'gopayQRCode';
            case 'qris':
                return 'qrisQRCode';
            case 'shopeepay':
                return 'shopeepayPaymentContent';
            case 'bank_transfer':
                return 'bankOptions';
            case 'cstore':
                return 'cstoreResultArea';
            default:
                return null;
        }
    }
    
    function getEmbedId(method) {
        switch(method) {
            case 'gopay':
                return 'gopayQRCode';
            case 'qris':
                return 'qrisQRCode';
            default:
                return null;
        }
    }

    window.processCstorePayment = function(store) {
        showLoading();
        processPayment({ store: store });
    };
    
    let statusCheckInterval;
    function startStatusCheck() {
        if (statusCheckInterval) return; // Already running
        statusCheckInterval = setInterval(checkPaymentStatus, 5000);
    }
    
    function checkPaymentStatus() {
        fetch(`/payment/check-status/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed' || data.status === 'paid') {
                    clearInterval(statusCheckInterval);
                    alert('Pembayaran berhasil dikonfirmasi!');
                    window.location.href = '{{ route("orders.show", $order->id) }}';
                }
            })
            .catch(error => console.error('Status check error:', error));
    }
});

function selectStore(store) {
    if (window.processCstorePayment) {
        window.processCstorePayment(store);
    }
}
</script>
@endsection
