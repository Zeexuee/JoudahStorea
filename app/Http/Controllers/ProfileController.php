<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class ProfileController extends Controller
{
    /**
     * Show user profile page
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
        ]);

        if ($user->phone !== $validated['phone']) {
            $validated['phone_verified_at'] = null;
        }

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Show order history page
     */
    public function orders(Request $request)
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with('items.product', 'payment', 'shipping')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('profile.orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show order detail
     */
    public function orderDetail(Request $request, Order $order)
    {
        // Authorize user can view this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Force fresh data from database (no caching)
        $order = $order->fresh();
        $order->load('items.product', 'payment', 'shipping', 'comments.user');

        return view('profile.order-detail', [
            'order' => $order,
        ]);
    }

    /**
     * User confirms order has been received.
     */
    public function confirmDelivered(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $result = $this->markDelivered($order, 'user');

        if (!($result['success'] ?? false)) {
            return redirect()->route('orders.show', $order)
                ->with($result['level'] ?? 'error', $result['message'] ?? 'Gagal mengonfirmasi pesanan.');
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Terima kasih, pesanan sudah dikonfirmasi diterima.');
    }

    /**
     * Public confirmation link from delivery reminder.
     */
    public function confirmDeliveredFromLink(Request $request, Order $order)
    {
        $result = $this->markDelivered($order, 'user');

        return view('delivery.confirmed', [
            'order' => $order->fresh(['shipping', 'user']),
            'result' => $result,
        ]);
    }

    /**
     * Shared delivery confirmation logic for user and public link.
     */
    private function markDelivered(Order $order, string $confirmedBy): array
    {
        $order->loadMissing('shipping');

        $shipping = $order->shipping;

        if (!$shipping) {
            return [
                'success' => false,
                'level' => 'error',
                'message' => 'Data pengiriman tidak ditemukan.',
            ];
        }

        if ($order->status === 'delivered') {
            return [
                'success' => true,
                'level' => 'info',
                'message' => 'Pesanan sudah dikonfirmasi sebelumnya.',
            ];
        }

        if (!in_array($order->status, ['shipped', 'delivered'], true)) {
            return [
                'success' => false,
                'level' => 'error',
                'message' => 'Pesanan belum dalam status pengiriman.',
            ];
        }

        $shipping->update([
            'status' => 'delivered',
            'actual_delivery' => $shipping->actual_delivery ?? now(),
        ]);

        $order->update([
            'status' => 'delivered',
            'delivered_confirmed_at' => now(),
            'delivered_confirmed_by' => $confirmedBy,
        ]);

        return [
            'success' => true,
            'level' => 'success',
            'message' => 'Pesanan berhasil dikonfirmasi diterima.',
        ];
    }
}
