<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        $cartItems = CartItem::where('session_id', $sessionId)
            ->with('product')
            ->get();

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return view('cart.index', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal
        ]);
    }

    public function add(Request $request)
    {
        $sessionId = session()->getId();
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        // Validasi product ada
        $product = Product::findOrFail($productId);

        // Check if item already in cart
        $cartItem = CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            // Jika sudah ada, tambah quantity
            $cartItem->increment('quantity', $quantity);
        } else {
            // Jika belum ada, buat item baru
            CartItem::create([
                'session_id' => $sessionId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        // Get total cart count
        $cartCount = CartItem::where('session_id', $sessionId)->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang',
            'productName' => $product->name,
            'cartCount' => $cartCount,
        ]);
    }

    public function remove($productId)
    {
        $sessionId = session()->getId();

        CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->delete();

        $cartCount = CartItem::where('session_id', $sessionId)->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Produk dihapus dari keranjang',
            'cartCount' => $cartCount,
        ]);
    }

    public function updateQuantity(Request $request, $productId)
    {
        $sessionId = session()->getId();
        $quantity = $request->input('quantity', 1);

        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->update(['quantity' => $quantity]);

        $cartCount = CartItem::where('session_id', $sessionId)->sum('quantity');

        return response()->json([
            'success' => true,
            'cartCount' => $cartCount,
        ]);
    }

    public function getCartCount()
    {
        $sessionId = session()->getId();
        $cartCount = CartItem::where('session_id', $sessionId)->sum('quantity');

        return response()->json([
            'cartCount' => $cartCount,
        ]);
    }
}
