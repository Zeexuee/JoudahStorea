<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Payment Checkout - Test Only</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="bg-white rounded-t-2xl shadow-lg p-8 text-center">
            <div class="mb-4">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-flask text-2xl text-blue-600"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Mock Payment Test</h1>
            <p class="text-gray-600">Joudah Store - Testing Mode</p>
        </div>

        <!-- Main Content -->
        <div class="bg-white shadow-lg rounded-b-2xl p-8 space-y-6">
            <!-- Info Alert -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-bold text-blue-900 mb-1">Testing Mode</h3>
                        <p class="text-blue-800 text-sm">
                            This is a mock payment gateway for development and testing. 
                            <strong>No real money will be charged.</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Transaction ID:</span>
                    <code class="bg-white px-3 py-1 rounded text-sm text-gray-900 font-mono">{{ $external_id }}</code>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Gateway:</span>
                    <span class="text-gray-900 font-semibold">Mock (Testing)</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded text-sm font-semibold">Pending</span>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded">
                <h4 class="font-bold text-amber-900 mb-2">Choose Test Outcome:</h4>
                <ul class="text-amber-800 text-sm space-y-1">
                    <li><strong>✓ APPROVE</strong> - Payment success</li>
                    <li><strong>✗ CANCEL</strong> - Payment failed</li>
                    <li><strong>⏱ TIMEOUT</strong> - Payment expired</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                @auth
                    <!-- Approve Button -->
                    <button onclick="simulateApprove()" 
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow-md">
                        <i class="fas fa-check-circle"></i>
                        APPROVE Payment
                    </button>

                    <!-- Cancel Button -->
                    <button onclick="simulateCancel()" 
                        class="w-full bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow-md">
                        <i class="fas fa-times-circle"></i>
                        CANCEL Payment
                    </button>

                    <!-- Timeout Button -->
                    <button onclick="simulateTimeout()" 
                        class="w-full bg-gradient-to-r from-gray-500 to-slate-600 hover:from-gray-600 hover:to-slate-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow-md">
                        <i class="fas fa-hourglass-end"></i>
                        TIMEOUT Payment
                    </button>
                @else
                    <!-- Login Required Message -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <i class="fas fa-exclamation-circle text-3xl text-red-600 mb-2"></i>
                        <h3 class="font-bold text-red-900 mb-2">Authentikasi Diperlukan</h3>
                        <p class="text-red-800 mb-4">Anda harus login terlebih dahulu untuk melanjutkan pembayaran.</p>
                        <a href="{{ route('login') }}" class="inline-block w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            Kembali ke Login
                        </a>
                    </div>
                @endauth

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 border-t pt-4">
                <p>This is a testing interface. All actions are simulated.</p>
                <p class="mt-1">No actual payment processing occurs.</p>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="text-center mt-4">
            <p class="text-gray-600 text-xs">
                Mock Payment Service v1.0 | 
                <a href="/" class="text-blue-600 hover:underline">Back to Store</a>
            </p>
        </div>
    </div>

    <script>
        const externalId = '{{ $external_id }}';
        
        // Debug logging
        console.log('[Mock Checkout] External ID:', externalId);
        console.log('[Mock Checkout] Page loaded at:', new Date().toISOString());

        function simulateApprove() {
            console.log('[Mock Checkout] Approve button clicked');
            // Set session status
            sessionStorage.setItem('test_payment_status', 'completed');
            
            // Show processing message
            showProcessing('Payment Approved ✓', true);
            
            // Redirect to verification after delay
            setTimeout(() => {
                const redirectUrl = '/payment/verify-mock?external_id=' + externalId + '&status=approved';
                console.log('[Mock Checkout] Redirecting to:', redirectUrl);
                window.location.href = redirectUrl;
            }, 1500);
        }

        function simulateCancel() {
            console.log('[Mock Checkout] Cancel button clicked');
            // Set session status
            sessionStorage.setItem('test_payment_status', 'cancelled');
            
            // Show processing message
            showProcessing('Payment Cancelled ✗', false);
            
            // Redirect to verification after delay
            setTimeout(() => {
                const redirectUrl = '/payment/verify-mock?external_id=' + externalId + '&status=cancelled';
                console.log('[Mock Checkout] Redirecting to:', redirectUrl);
                window.location.href = redirectUrl;
            }, 1500);
        }

        function simulateTimeout() {
            console.log('[Mock Checkout] Timeout button clicked');
            // Set session status
            sessionStorage.setItem('test_payment_status', 'expired');
            
            // Show processing message
            showProcessing('Payment Expired ⏱', false);
            
            // Redirect to verification after delay
            setTimeout(() => {
                const redirectUrl = '/payment/verify-mock?external_id=' + externalId + '&status=expired';
                console.log('[Mock Checkout] Redirecting to:', redirectUrl);
                window.location.href = redirectUrl;
            }, 1500);
        }

        function showProcessing(message, isSuccess) {
            console.log('[Mock Checkout] Showing processing:', message);
            const button = event.target.closest('button');
            const container = button.parentElement.parentElement;
            
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white rounded-2xl p-8 text-center shadow-2xl max-w-sm">
                    <div class="mb-4">
                        <div class="w-20 h-20 mx-auto flex items-center justify-center rounded-full ${isSuccess ? 'bg-green-100' : 'bg-red-100'}">
                            <i class="fas fa-${isSuccess ? 'check' : 'times'} text-4xl ${isSuccess ? 'text-green-600' : 'text-red-600'}"></i>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">${message}</h2>
                    <p class="text-gray-600 mb-4">Processing your test payment...</p>
                    <div class="flex justify-center gap-1">
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
        }
    </script>
</body>
</html>
