<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;

use App\Services\MockPaymentService;
use App\Services\MindtransPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $paymentService;
    private $activeGateway;

    public function __construct()
    {
        $this->activeGateway = config('payment.gateway', 'mock');
        $this->paymentService = $this->getPaymentService();
    }

    /**
     * Get the appropriate payment service instance
     */
    private function getPaymentService()
    {
        $gateway = strtolower((string) $this->activeGateway);

        return match($gateway) {
            'mock' => new MockPaymentService(),
            'midtrans',
            'mindtrans' => new MindtransPaymentService(),
            default => new MockPaymentService(), // Fallback to mock
        };
    }

    /**
     * Show payment page
     */
    public function show(Request $request, Order $order)
    {
        // Authorize user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Load payment info
        $payment = $order->payment;

        if (!$payment) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Data pembayaran tidak ditemukan');
        }

        return view('payment.show', compact('order', 'payment'));
    }

    /**
     * Process payment (render payment page with Snap embedded)
     */
    public function process(Request $request, Order $order)
    {
        // Authorize user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $payment = $order->payment;

        if (!$payment) {
            return back()->with('error', 'Data pembayaran tidak ditemukan');
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran tidak dapat diproses');
        }

        try {
            $returnTo = $this->resolveReturnDestination($request, $order);

            // Create payment using configured payment service
            $result = $this->paymentService->createPayment($payment, $this->prepareItemDetails($order));

            if (!empty($result['success'])) {
                $snapToken = $result['snap_token'] ?? null;
                $checkoutUrl = $result['checkout_url'] ?? null;

                if (!empty($snapToken)) {
                    // Midtrans Snap embedded flow.
                    return view('payment.embedded', [
                        'order' => $order,
                        'payment' => $payment,
                        'snapToken' => $snapToken,
                        'amount' => $payment->amount,
                        'returnToUrl' => $returnTo['url'],
                        'returnToLabel' => $returnTo['label'],
                    ]);
                }

                if (!empty($checkoutUrl)) {
                    // Fallback for non-Snap-token gateways (e.g. mock/direct URL).
                    return redirect()->to($checkoutUrl);
                }

                return back()->with('error', 'Gagal membuat pembayaran: respon gateway tidak lengkap (snap token / checkout URL tidak tersedia).');
            }

            return back()->with('error', 'Gagal membuat pembayaran: ' . ($result['error'] ?? 'unknown error'));
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Resolve where the payment page back button should go.
     */
    private function resolveReturnDestination(Request $request, Order $order): array
    {
        $checkoutUrl = route('checkout.show');
        $orderUrl = route('orders.show', $order);

        $returnTo = (string) $request->input('return_to', '');

        if ($returnTo === $checkoutUrl || str_contains($returnTo, '/checkout')) {
            return ['url' => $checkoutUrl, 'label' => 'Kembali ke Checkout'];
        }

        if ($returnTo === $orderUrl || str_contains($returnTo, '/orders/' . $order->id)) {
            return ['url' => $orderUrl, 'label' => 'Kembali ke Pesanan'];
        }

        // Default fallback: order detail page.
        return ['url' => $orderUrl, 'label' => 'Kembali ke Pesanan'];
    }

    /**
     * Payment callback from Doku
     */
    public function callback(Request $request)
    {
        $payload = $request->all();

        try {
            $this->paymentService->processCallback($payload);
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify payment status (after redirect from Doku)
     */
    public function verify(Request $request, Order $order)
    {
        // Authorize user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $payment = $order->payment;

        if (!$payment) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Data pembayaran tidak ditemukan');
        }

        try {
            $result = $this->paymentService->verifyPayment($payment);

            if ($result['success']) {
                // Payment info updated by service
                if ($payment->fresh()->isPaid()) {
                    CartItem::where('user_id', $order->user_id)->delete();

                    return redirect()->route('orders.show', $order)
                        ->with('success', 'Pembayaran berhasil diverifikasi');
                } else {
                    return redirect()->route('payment.show', $order)
                        ->with('info', 'Pembayaran masih diproses, silahkan cek nanti');
                }
            } else {
                return redirect()->route('payment.show', $order)
                    ->with('error', 'Gagal memverifikasi pembayaran: ' . $result['error']);
            }
        } catch (\Exception $e) {
            return redirect()->route('payment.show', $order)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Check payment status (AJAX)
     */
    public function checkStatus(Request $request, Order $order)
    {
        // Authorize user
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payment = $order->payment;

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json([
            'status' => $payment->status,
            'amount' => $payment->amount,
            'paid_at' => $payment->paid_at,
            'is_paid' => $payment->isPaid()
        ]);
    }

    /**
     * Show custom payment page (our design)
     */
    public function showCustom(Request $request, Payment $payment)
    {
        // Authorize user
        if ($payment->order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Load order with relationships
        $order = $payment->order()->with(['items.product', 'shipping'])->first();

        return view('payment.custom', compact('payment', 'order'));
    }

    /**
     * Process custom payment (trigger Midtrans API)
     */
    public function processCustom(Request $request, Payment $payment)
    {
        // Authorize user
        if ($payment->order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $directMethods = ['qris', 'gopay', 'shopeepay', 'bank_transfer', 'cstore'];
            $selectedMethod = $payment->payment_method;
            $requestedBank = strtolower((string) $request->input('bank', ''));
            $requestedStore = strtolower((string) $request->input('store', ''));

            $cachedBank = strtolower((string) ($payment->metadata['direct_bank'] ?? ''));
            $cachedStore = strtolower((string) ($payment->metadata['direct_store'] ?? ''));

            $isBankRequestChanged = $selectedMethod === 'bank_transfer' && $requestedBank !== '' && $requestedBank !== $cachedBank;
            $isStoreRequestChanged = $selectedMethod === 'cstore' && $requestedStore !== '' && $requestedStore !== $cachedStore;

            if (
                in_array($selectedMethod, $directMethods, true)
                && !empty($payment->metadata['direct_payment'])
                && (($payment->metadata['direct_payment_method'] ?? null) === $selectedMethod)
                && !$isBankRequestChanged
                && !$isStoreRequestChanged
                && (
                    !empty($payment->metadata['direct_qr_url'])
                    || !empty($payment->metadata['direct_deeplink_url'])
                    || !empty($payment->metadata['direct_va_number'])
                    || !empty($payment->metadata['direct_payment_code'])
                )
            ) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment_type' => $selectedMethod,
                        'external_id' => $payment->external_id,
                        'qr_url' => $payment->metadata['direct_qr_url'] ?? null,
                        'deeplink_url' => $payment->metadata['direct_deeplink_url'] ?? null,
                        'payment_code' => $payment->metadata['direct_payment_code'] ?? null,
                        'store' => $payment->metadata['direct_store'] ?? null,
                        'bank' => $payment->metadata['direct_bank'] ?? null,
                        'va_number' => $payment->metadata['direct_va_number'] ?? null,
                        'expiry_time' => $payment->metadata['expiry_time'] ?? null,
                        'payment_gateway' => 'midtrans',
                    ],
                ]);
            }

            if (in_array($selectedMethod, $directMethods, true) && method_exists($this->paymentService, 'createDirectPayment')) {
                $options = [];
                if ($selectedMethod === 'bank_transfer') {
                    $defaultBank = strtolower((string) ($payment->metadata['selected_bank'] ?? 'bca'));
                    $options['bank'] = strtolower((string) $request->input('bank', $defaultBank));
                }
                if ($selectedMethod === 'cstore') {
                    $defaultStore = strtolower((string) ($payment->metadata['selected_store'] ?? 'indomaret'));
                    $options['store'] = strtolower((string) $request->input('store', $defaultStore));
                }

                $result = $this->paymentService->createDirectPayment(
                    $payment,
                    $this->prepareItemDetails($payment->order),
                    $selectedMethod,
                    $options
                );

                return response()->json([
                    'success' => $result['success'],
                    'data' => $result,
                    'error' => $result['error'] ?? null,
                ], $result['success'] ? 200 : 422);
            }

            // Always create a fresh token for pending payments to avoid stale channel config.
            $result = $this->paymentService->createPayment(
                $payment, 
                $this->prepareItemDetails($payment->order),
                $payment->payment_method
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status (AJAX)
     */
    public function checkPaymentStatus(Request $request, Payment $payment)
    {
        // Authorize user
        if ($payment->order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Pull latest status from gateway so direct-flow methods (ShopeePay/GoPay/QRIS/VA/CStore)
        // don't depend only on webhook timing.
        try {
            if (method_exists($this->paymentService, 'verifyPayment') && $payment->external_id) {
                $this->paymentService->verifyPayment($payment);
                $payment->refresh();
                $payment->order->refresh();
            }
        } catch (\Throwable $e) {
            logger('Payment status sync failed', [
                'payment_id' => $payment->id,
                'external_id' => $payment->external_id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
            'order_status' => $payment->order->status,
        ]);
    }

    /**
     * Cancel payment and return to checkout
     */
    public function cancel(Request $request, Order $order)
    {
        // Authorize user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $payment = $order->payment;

        if (!$payment) {
            return redirect()->route('orders.show', $order);
        }

        if ($payment->status === 'pending') {
            $payment->update(['status' => 'cancelled']);
            $order->update(['status' => 'cancelled']);
        }

        return redirect()->route('orders.show', $order)
            ->with('info', 'Pembayaran dibatalkan');
    }

    /**
     * Prepare item details for Midtrans
     */
    private function prepareItemDetails(Order $order)
    {
        $items = [];

        // Calculate subtotal for product items only
        $subtotal = 0;
        foreach ($order->items as $orderItem) {
            $itemTotal = (int)$orderItem->price * (int)$orderItem->quantity;
            $subtotal += $itemTotal;
            
            $items[] = [
                'id' => (string)$orderItem->product_id,
                'name' => $orderItem->product->name,
                'price' => (int)$orderItem->price,
                'quantity' => (int)$orderItem->quantity,
            ];
        }

        $shippingCost = (int) optional($order->shipping)->cost;
        $adminFee = 3000;
        $paymentGatewayFee = max(0, (int) $order->total_price - $subtotal - $shippingCost - $adminFee);

        if ($shippingCost > 0) {
            $items[] = [
                'id' => 'shipping',
                'name' => 'Biaya Pengiriman',
                'price' => $shippingCost,
                'quantity' => 1,
            ];
        }

        if ($adminFee > 0) {
            $items[] = [
                'id' => 'admin_fee',
                'name' => 'Biaya Administratif',
                'price' => $adminFee,
                'quantity' => 1,
            ];
        }

        if ($paymentGatewayFee > 0) {
            $items[] = [
                'id' => 'payment_gateway_fee',
                'name' => 'Biaya Payment Gateway',
                'price' => $paymentGatewayFee,
                'quantity' => 1,
            ];
        }

        return $items;
    }

    /**
     * Mock payment verification endpoint
     * Used by mock checkout for testing
     */
    public function verifyMock(Request $request)
    {
        $externalId = $request->query('external_id');
        $status = $request->query('status', 'pending');

        logger('Mock Payment Verify Request', [
            'external_id' => $externalId,
            'status' => $status,
            'is_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'ip' => $request->ip()
        ]);

        // Validate input
        if (!$externalId) {
            logger('Mock Payment Verification: Missing external_id', ['status' => $status]);
            return redirect('/')->with('error', 'External ID tidak ditemukan');
        }

        // Check if user is authenticated FIRST before doing anything
        if (!auth()->check()) {
            logger('Mock Payment Verification: User not authenticated', ['external_id' => $externalId, 'status' => $status]);
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk verifikasi pembayaran');
        }

        // Find payment by external ID
        $payment = Payment::where('external_id', $externalId)->first();

        if (!$payment) {
            logger('Mock Payment Verification: Payment not found', [
                'external_id' => $externalId, 
                'user_id' => auth()->id(),
                'total_payments' => Payment::count()
            ]);
            return redirect('/orders')->with('error', 'Pembayaran dengan ID ' . $externalId . ' tidak ditemukan di sistem');
        }

        $order = $payment->order;

        if (!$order) {
            logger('Mock Payment Verification: Order not found', ['payment_id' => $payment->id, 'user_id' => auth()->id()]);
            return redirect('/orders')->with('error', 'Pesanan untuk pembayaran ini tidak ditemukan');
        }

        // Authorize user
        if ($order->user_id !== auth()->id()) {
            logger('Mock Payment Verification: Unauthorized user', [
                'order_id' => $order->id, 
                'order_user_id' => $order->user_id,
                'auth_user_id' => auth()->id()
            ]);
            abort(403, 'Unauthorized - Pesanan ini bukan milik Anda');
        }

        try {
            // Process based on test status from URL parameter  
            if ($status === 'approved' || $status === 'completed') {
                // Mark payment as completed - this will trigger model observer to update order
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'test_result' => 'approved',
                        'verified_at' => now()->toIso8601String(),
                    ])
                ]);

                logger('Mock Payment Approved', [
                    'order_id' => $order->id,
                    'external_id' => $externalId,
                    'user_id' => auth()->id()
                ]);

                // Refresh order to get updated status
                $order->refresh();

                return redirect()->route('orders.show', $order)
                    ->with('success', '✓ Pembayaran berhasil disetujui (Test Mode) - Status pesanan otomatis berubah ke "Sudah Dibayar"');
            } elseif ($status === 'cancelled') {
                // Mark payment as cancelled
                $payment->update([
                    'status' => 'cancelled',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'test_result' => 'cancelled',
                        'verified_at' => now()->toIso8601String(),
                    ])
                ]);

                logger('Mock Payment Cancelled', [
                    'order_id' => $order->id,
                    'external_id' => $externalId,
                    'user_id' => auth()->id()
                ]);

                return redirect()->route('orders.show', $order)
                    ->with('info', '✗ Pembayaran dibatalkan (Test Mode)');
            } elseif ($status === 'expired') {
                // Mark payment as expired
                $payment->update([
                    'status' => 'expired',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'test_result' => 'expired',
                        'verified_at' => now()->toIso8601String(),
                    ])
                ]);

                logger('Mock Payment Expired', [
                    'order_id' => $order->id,
                    'external_id' => $externalId,
                    'user_id' => auth()->id()
                ]);

                return redirect()->route('orders.show', $order)
                    ->with('warning', '⏱ Pembayaran kadaluarsa (Test Mode)');
            } else {
                logger('Mock Payment Verification: Unknown status', ['status' => $status, 'external_id' => $externalId]);
                return redirect()->route('orders.show', $order)
                    ->with('warning', 'Status pembayaran tidak dikenali: ' . $status);
            }
        } catch (\Exception $e) {
            logger('Mock Payment Verification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => isset($order) ? $order->id : 'unknown',
                'external_id' => $externalId,
                'user_id' => auth()->id()
            ]);
            
            // Safe fallback - try to redirect to orders page
            try {
                if (isset($order) && $order) {
                    return redirect()->route('orders.show', $order)
                        ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
                }
            } catch (\Exception $routeError) {
                //  Fall through to home redirect
            }
            
            return redirect('/orders')
                ->with('error', 'Terjadi kesalahan saat memverifikasi pembayaran: ' . $e->getMessage());
        }
    }
}
