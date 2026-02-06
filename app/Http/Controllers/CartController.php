<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get cart identifier (user_id or session_id)
     */
    private function getCartIdentifier()
    {
        if (auth()->check()) {
            return [
                'type' => 'user_id',
                'value' => auth()->id()
            ];
        } else {
            return [
                'type' => 'session_id',
                'value' => session()->getId()
            ];
        }
    }

    /**
     * Query cart items by identifier
     */
    private function queryCartItems()
    {
        $identifier = $this->getCartIdentifier();
        
        $query = CartItem::query();
        if ($identifier['type'] === 'user_id') {
            $query->where('user_id', $identifier['value']);
        } else {
            $query->where('session_id', $identifier['value'])
                  ->whereNull('user_id');
        }
        
        return $query;
    }

    public function index()
    {
        // Require authentication - redirect back to previous page
        if (!auth()->check()) {
            return redirect()->back();
        }

        $cartItems = $this->queryCartItems()
            ->with('product')
            ->get();

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return view('cart.index', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'requireLogin' => false
        ]);
    }

    public function add(Request $request)
    {
        $identifier = $this->getCartIdentifier();
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        // Validasi product ada
        $product = Product::findOrFail($productId);

        // Check if item already in cart
        $cartItem = CartItem::query();
        if ($identifier['type'] === 'user_id') {
            $cartItem->where('user_id', $identifier['value']);
        } else {
            $cartItem->where('session_id', $identifier['value'])
                     ->whereNull('user_id');
        }
        $cartItem = $cartItem->where('product_id', $productId)->first();

        if ($cartItem) {
            // Jika sudah ada, tambah quantity
            $cartItem->increment('quantity', $quantity);
        } else {
            // Jika belum ada, buat item baru
            $data = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
            
            if ($identifier['type'] === 'user_id') {
                $data['user_id'] = $identifier['value'];
            } else {
                $data['session_id'] = $identifier['value'];
            }
            
            CartItem::create($data);
        }

        // Get total cart count
        $cartCount = $this->queryCartItems()->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang',
            'productName' => $product->name,
            'cartCount' => $cartCount,
        ]);
    }

    public function remove($productId)
    {
        $identifier = $this->getCartIdentifier();
        
        $query = CartItem::where('product_id', $productId);
        if ($identifier['type'] === 'user_id') {
            $query->where('user_id', $identifier['value']);
        } else {
            $query->where('session_id', $identifier['value'])
                  ->whereNull('user_id');
        }
        $query->delete();

        $cartCount = $this->queryCartItems()->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Produk dihapus dari keranjang',
            'cartCount' => $cartCount,
        ]);
    }

    public function updateQuantity(Request $request, $productId)
    {
        $identifier = $this->getCartIdentifier();
        $quantity = $request->input('quantity', 1);

        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        $query = CartItem::where('product_id', $productId);
        if ($identifier['type'] === 'user_id') {
            $query->where('user_id', $identifier['value']);
        } else {
            $query->where('session_id', $identifier['value'])
                  ->whereNull('user_id');
        }
        $query->update(['quantity' => $quantity]);

        $cartCount = $this->queryCartItems()->sum('quantity');

        return response()->json([
            'success' => true,
            'cartCount' => $cartCount,
        ]);
    }

    public function getCartCount()
    {
        $cartCount = $this->queryCartItems()->sum('quantity');

        return response()->json([
            'cartCount' => $cartCount,
        ]);
    }
}
