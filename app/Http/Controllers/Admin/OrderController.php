<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipping;
use App\Services\DeliveryReminderService;

class OrderController extends Controller
{
    /**
     * Show admin orders list
     */
    public function index(Request $request)
    {
        $query = Order::with('user', 'items.product', 'payment', 'shipping')
            ->orderByDesc('created_at');

        // Filter by order status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->whereHas('payment', function ($paymentQuery) use ($request) {
                $paymentQuery->where('status', $request->payment_status);
            });
        }

        // Filter by shipping status
        if ($request->has('shipping_status') && $request->shipping_status) {
            $query->whereHas('shipping', function ($shippingQuery) use ($request) {
                $shippingQuery->where('status', $request->shipping_status);
            });
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhere('shipping_name', 'like', "%$search%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();
        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Sudah Dibayar',
            'shipped' => 'Dikirim',
            'delivered' => 'Terima',
            'cancelled' => 'Dibatalkan',
        ];

        $paymentStatuses = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Lunas',
            'failed' => 'Gagal',
            'expired' => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan',
        ];

        $shippingStatuses = [
            'pending' => 'Menunggu Pickup',
            'picked_up' => 'Sudah Diambil',
            'in_transit' => 'Dalam Perjalanan',
            'out_for_delivery' => 'Sedang Diantar',
            'delivered' => 'Terkirim',
            'failed' => 'Gagal Dikirim',
            'returned' => 'Dikembalikan',
        ];

        return view('admin.orders.index', compact('orders', 'statuses', 'paymentStatuses', 'shippingStatuses'));
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
            'processing' => 'Sudah Dibayar',
            'shipped' => 'Dikirim',
            'delivered' => 'Terima',
            'cancelled' => 'Dibatalkan',
        ];

        $paymentStatuses = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Lunas',
            'failed' => 'Gagal',
            'expired' => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan',
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
            'payment_status' => 'required|in:pending,processing,completed,failed,expired,cancelled,refunded',
        ]);

        $payment = $order->payment;

        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'status' => $validated['payment_status'],
                'paid_at' => $validated['payment_status'] === 'completed' ? now() : null,
            ]);
        } else {
            $payment->update([
                'status' => $validated['payment_status'],
                'paid_at' => $validated['payment_status'] === 'completed'
                    ? ($payment->paid_at ?? now())
                    : null,
            ]);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status pembayaran berhasil diperbarui. Status pesanan ikut tersinkron otomatis.');
    }

    /**
     * Update shipping status
     */
    public function updateShippingStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
            'courier' => 'required|string|max:100',
        ]);

        $trackingNumber = trim((string) $validated['tracking_number']);
        $courier = trim((string) $validated['courier']);

        // Once tracking number is entered by admin, shipment is marked as sent.
        // Shipping model will sync order status to "shipped" automatically.
        $resolvedShippingStatus = 'in_transit';

        $payload = [
            'status' => $resolvedShippingStatus,
            'tracking_number' => $trackingNumber,
            'courier' => $courier,
            'courier_name' => $courier,
        ];

        $shipping = $order->shipping;

        if (!$shipping) {
            $shipping = Shipping::create(array_merge($payload, [
                'order_id' => $order->id,
            ]));
        } else {
            $shipping->update($payload);
        }

        // Send immediate reminder once shipment data is available.
        app(DeliveryReminderService::class)->sendReminder($order->fresh(['user', 'shipping']));

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Data pengiriman berhasil disimpan. Status pesanan otomatis berubah menjadi Dikirim.');
    }

    /**
     * Fallback: allow admin to confirm order delivered when user forgets.
     */
    public function confirmDelivered(Request $request, Order $order)
    {
        $shipping = $order->shipping;

        if (!$shipping) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Data pengiriman tidak ditemukan.');
        }

        $shipping->update([
            'status' => 'delivered',
            'actual_delivery' => $shipping->actual_delivery ?? now(),
        ]);

        $order->update([
            'status' => 'delivered',
            'delivered_confirmed_at' => now(),
            'delivered_confirmed_by' => 'admin',
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Pesanan dikonfirmasi sudah diterima oleh admin.');
    }

    /**
     * Manually resend delivery reminder to user via WA/email.
     */
    public function sendDeliveryReminder(Request $request, Order $order)
    {
        $order = $order->fresh(['user', 'shipping']);

        if (!$order->shipping) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Data pengiriman tidak ditemukan.');
        }

        $result = app(DeliveryReminderService::class)->sendReminder($order, true);

        if (!($result['success'] ?? false)) {
            $message = $result['wa']['message'] ?? $result['email']['message'] ?? 'Gagal mengirim pengingat ke user.';

            return redirect()->route('admin.orders.show', $order)
                ->with('error', $message);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Pengingat WA/email berhasil dikirim ke user.');
    }

    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
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
