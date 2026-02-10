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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
        ]);

        auth()->user()->update($validated);

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
            ->with('items.product')
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

        $order->load('items.product');

        return view('profile.order-detail', [
            'order' => $order,
        ]);
    }
}
