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
            'paid' => 'Terbayar',
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
            'payment_status' => 'required|in:pending,paid,failed,expired',
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

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status pembayaran berhasil diperbarui');
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

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status pengiriman berhasil diperbarui');
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
}
