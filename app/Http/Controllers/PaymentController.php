<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\DokuPaymentService;
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
        return match($this->activeGateway) {
            'mock' => new MockPaymentService(),
            'mindtrans' => new MindtransPaymentService(),
            'doku' => new DokuPaymentService(),
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
     * Process payment (redirect to Doku)
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
            // Create payment using configured payment service
            $result = $this->paymentService->createPayment($payment, $this->prepareItemDetails($order));

            if ($result['success']) {
                return redirect()->to($result['checkout_url']);
            } else {
                return back()->with('error', 'Gagal membuat pembayaran: ' . $result['error']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
     * Prepare item details for Doku
     */
    private function prepareItemDetails(Order $order)
    {
        $items = [];

        foreach ($order->items as $orderItem) {
            $items[] = [
                'id' => (string)$orderItem->product_id,
                'name' => $orderItem->product->name,
                'price' => (int)$orderItem->price,
                'quantity' => (int)$orderItem->quantity,
            ];
        }

        // Add shipping cost
        if ($order->shipping) {
            $items[] = [
                'id' => 'shipping',
                'name' => 'Ongkos Kirim - ' . $order->shipping->courier_name,
                'price' => (int)$order->shipping->cost,
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
                    ->with('success', '✓ Pembayaran berhasil disetujui (Test Mode) - Status pesanan otomatis berubah ke "Diproses"');
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
