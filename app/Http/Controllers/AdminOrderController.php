<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Admin dashboard - menampilkan overview pesanan
     */
    public function dashboard()
    {
        // Get order statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        // Get recent orders
        $recentOrders = Order::latest()->limit(10)->get();
        
        // Calculate total revenue
        $totalRevenue = Order::where('status', 'delivered')->sum('total_price');

        return view('admin.dashboard', [
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'processingOrders' => $processingOrders,
            'shippedOrders' => $shippedOrders,
            'deliveredOrders' => $deliveredOrders,
            'cancelledOrders' => $cancelledOrders,
            'recentOrders' => $recentOrders,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    /**
     * List all orders
     */
    public function index()
    {
        $orders = Order::with('user', 'shipping')
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show order detail
     */
    public function show(Order $order)
    {
        $order->load('user', 'items', 'payment', 'shipping');

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated successfully');
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,completed,failed,cancelled',
        ]);

        if ($order->payment) {
            $order->payment->update(['status' => $validated['payment_status']]);
        }

        return back()->with('success', 'Payment status updated successfully');
    }

    /**
     * Update shipping status
     */
    public function updateShippingStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping_status' => 'required|in:pending,picked_up,in_transit,out_for_delivery,delivered,failed,returned',
        ]);

        if ($order->shipping) {
            $order->shipping->update(['status' => $validated['shipping_status']]);
        }

        return back()->with('success', 'Shipping status updated successfully');
    }
}
