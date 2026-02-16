<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\DokuPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $dokuPayment;

    public function __construct()
    {
        $this->dokuPayment = new DokuPaymentService();
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
            // Create payment in Doku
            $result = $this->dokuPayment->createPayment($payment, $this->prepareItemDetails($order));

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
            $this->dokuPayment->processCallback($payload);
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
            $result = $this->dokuPayment->verifyPayment($payment);

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
}
