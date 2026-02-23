<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipping;

class OrderController extends Controller
{
    /**
     * Show admin orders list
     */
    public function index(Request $request)
    {
        $query = Order::with('user', 'items.product', 'payment', 'shipping')
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhere('shipping_name', 'like', "%$search%")
                  ->orWhere('shipping_phone', 'like', "%$search%");
            });
        }

        $orders = $query->paginate(20);
        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'delivered' => 'Terima',
            'cancelled' => 'Dibatalkan',
        ];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    /**
     * Show order detail
     */
    public function show(Order $order)
    {
        // Force fresh data from database (no caching)
        $order = $order->fresh();
        $order->load('user', 'items.product', 'payment', 'shipping');

        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'delivered' => 'Terima',
            'cancelled' => 'Dibatalkan',
        ];

        $paymentStatuses = [
            'pending' => 'Menunggu',
            'completed' => 'Lunas',
            'failed' => 'Gagal',
            'expired' => 'Kadaluarsa',
        ];

        $shippingStatuses = [
            'pending' => 'Menunggu',
            'picked_up' => 'Diambil',
            'in_transit' => 'Dalam Pengiriman',
            'out_for_delivery' => 'Dalam Pengiriman (Hari Ini)',
            'delivered' => 'Terkirim',
            'failed' => 'Gagal',
            'returned' => 'Dikembalikan',
        ];

        return view('admin.orders.show', compact('order', 'statuses', 'paymentStatuses', 'shippingStatuses'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status pesanan berhasil diperbarui');
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,completed,failed,expired',
        ]);

        $payment = $order->payment;

        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'status' => $validated['payment_status'],
            ]);
        } else {
            $payment->update([
                'status' => $validated['payment_status'],
            ]);
        }

        // AUTO UPDATE: Jika pembayaran berhasil, ubah status order menjadi "processing"
        if ($validated['payment_status'] === 'completed') {
            $order->update(['status' => 'processing']);
            logger('Auto-update: Order status changed to processing (payment completed)', [
                'order_id' => $order->id,
                'by' => 'admin_payment_update'
            ]);
        }

        // AUTO UPDATE: Jika pembayaran gagal, ubah status order menjadi "cancelled"
        if ($validated['payment_status'] === 'failed') {
            $order->update(['status' => 'cancelled']);
            logger('Auto-update: Order status changed to cancelled (payment failed)', [
                'order_id' => $order->id,
                'by' => 'admin_payment_update'
            ]);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status pembayaran berhasil diperbarui (order status otomatis diupdate)');
    }

    /**
     * Update shipping status
     */
    public function updateShippingStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping_status' => 'required|in:pending,picked_up,in_transit,out_for_delivery,delivered,failed,returned',
            'tracking_number' => 'nullable|string|max:100',
            'courier' => 'nullable|string|max:100',
        ]);

        $shipping = $order->shipping;

        if (!$shipping) {
            $shipping = Shipping::create([
                'order_id' => $order->id,
                'status' => $validated['shipping_status'],
                'tracking_number' => $validated['tracking_number'] ?? null,
                'courier' => $validated['courier'] ?? null,
            ]);
        } else {
            $shipping->update([
                'status' => $validated['shipping_status'],
                'tracking_number' => $validated['tracking_number'] ?? $shipping->tracking_number,
                'courier' => $validated['courier'] ?? $shipping->courier,
            ]);
        }

        // AUTO UPDATE: Update order status berdasarkan shipping status
        if ($validated['shipping_status'] === 'picked_up') {
            $order->update(['status' => 'processing']);
            logger('Auto-update: Order status changed to processing (shipping picked up)', [
                'order_id' => $order->id,
                'by' => 'admin_shipping_update'
            ]);
        } elseif ($validated['shipping_status'] === 'in_transit' || $validated['shipping_status'] === 'out_for_delivery') {
            $order->update(['status' => 'shipped']);
            logger('Auto-update: Order status changed to shipped (in transit)', [
                'order_id' => $order->id,
                'by' => 'admin_shipping_update'
            ]);
        } elseif ($validated['shipping_status'] === 'delivered') {
            $order->update(['status' => 'delivered']);
            logger('Auto-update: Order status changed to delivered', [
                'order_id' => $order->id,
                'by' => 'admin_shipping_update'
            ]);
        } elseif ($validated['shipping_status'] === 'failed' || $validated['shipping_status'] === 'returned') {
            $order->update(['status' => 'cancelled']);
            logger('Auto-update: Order status changed to cancelled (shipping failed/returned)', [
                'order_id' => $order->id,
                'by' => 'admin_shipping_update'
            ]);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status pengiriman berhasil diperbarui (order status otomatis diupdate)');
    }

    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total_price'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
        ];

        // Recent orders
        $recentOrders = Order::with('user', 'payment')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }

    /**
     * Print/Export receipt (resi) for an order
     */
    public function printReceipt(Order $order)
    {
        // Check if order has shipping with tracking number
        if (!$order->shipping || !$order->shipping->tracking_number) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', 'Belum ada nomor resi untuk order ini.');
        }

        return view('admin.orders.print-receipt', compact('order'));
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Request $request, Order $order)
    {
        // Validate request
        $request->validate([
            'cancel_reason' => 'required|string|min:5|max:500',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi',
            'cancel_reason.min' => 'Alasan minimal 5 karakter',
            'cancel_reason.max' => 'Alasan maksimal 500 karakter',
        ]);

        // Check if order can be cancelled (only pending or processing)
        if (!in_array($order->status, ['pending', 'processing'])) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', 'Pesanan dengan status ' . $order->status . ' tidak dapat dibatalkan. Hanya pesanan dengan status pending atau processing yang dapat dibatalkan.');
        }

        try {
            // Update order status
            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => $request->cancel_reason,
            ]);

            // Refund payment if exists
            if ($order->payment) {
                $order->payment->update([
                    'status' => 'refunded',
                ]);

                logger('Order: Payment refunded', [
                    'order_id' => $order->id,
                    'payment_id' => $order->payment->id,
                ]);
            }

            logger('Order: Cancelled by admin', [
                'order_id' => $order->id,
                'cancel_reason' => $request->cancel_reason,
            ]);

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibatalkan. Uang pelanggan akan dikembalikan.');

        } catch (\Exception $e) {
            logger('Order: Cancel error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }
}
