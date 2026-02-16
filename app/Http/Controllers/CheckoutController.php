<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipping;
use App\Services\RajaongkirService;
use App\Services\DokuPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    private $rajaongkir;
    private $dokuPayment;

    public function __construct()
    {
        $this->rajaongkir = new RajaongkirService();
        $this->dokuPayment = new DokuPaymentService();
    }

    /**
     * Show checkout page
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Get cart items
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja Anda kosong');
        }

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        // Get provinces for dropdown
        $provinces = $this->rajaongkir->getProvinces();

        // Pre-fill with user data if available
        $userProfile = [
            'name' => $user->name ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone ?? '',
            'address' => $user->address ?? '',
            'city' => $user->city ?? '',
            'province' => $user->province ?? '',
            'postal_code' => $user->postal_code ?? '',
        ];

        return view('checkout.show', compact(
            'cartItems',
            'subtotal',
            'provinces',
            'userProfile'
        ));
    }

    /**
     * Get cities by province (AJAX)
     */
    public function getCities(Request $request)
    {
        $provinceId = $request->input('province_id');

        if (!$provinceId) {
            return response()->json(['error' => 'Province ID required'], 400);
        }

        $cities = $this->rajaongkir->getCitiesByProvince($provinceId);

        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }

    /**
     * Get shipping costs (AJAX)
     */
    public function getShippingCosts(Request $request)
    {
        $validated = $request->validate([
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
        ]);

        try {
            // Validate destination
            if (!$this->rajaongkir->validateDestination($validated['province_id'], $validated['city_id'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tujuan pengiriman tidak valid'
                ], 422);
            }

            // Get user's cart items
            $user = $request->user();
            $cartItems = CartItem::where('user_id', $user->id)
                ->with('product')
                ->get();

            // Calculate total weight
            $weight = $cartItems->sum(function ($item) {
                // Default 500g per item if weight not specified
                return ($item->product->weight ?? 500) * $item->quantity;
            });

            // Get shipping costs
            $costs = $this->rajaongkir->getShippingCosts(
                $validated['city_id'],
                $weight,
                ['jne', 'pos', 'tiki']
            );

            return response()->json([
                'success' => true,
                'costs' => $costs,
                'weight' => $weight
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil biaya pengiriman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process checkout and create order
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_province' => 'required|string|max:100',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:10',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'courier' => 'required|string',
            'service' => 'required|string',
            'shipping_cost' => 'required|integer|min:0',
            'admin_fee' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $user = $request->user();

            // Get cart items
            $cartItems = CartItem::where('user_id', $user->id)
                ->with('product')
                ->get();

            if ($cartItems->isEmpty()) {
                return back()->with('error', 'Keranjang belanja Anda kosong');
            }

            // Calculate subtotal
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            // Calculate total (subtotal + shipping + admin fee)
            $adminFee = 3000; // Biaya administratif
            $total = $subtotal + $validated['shipping_cost'] + $adminFee;

            // Create order
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . Str::random(4);
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_price' => $total,
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_province' => $validated['shipping_province'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'price' => $cartItem->product->price,
                    'quantity' => $cartItem->quantity,
                ]);
            }

            // Create shipping record
            $weight = $cartItems->sum(function ($item) {
                return ($item->product->weight ?? 500) * $item->quantity;
            });

            Shipping::create([
                'order_id' => $order->id,
                'courier' => $validated['courier'],
                'courier_name' => $this->getCourierName($validated['courier']),
                'service' => $validated['service'],
                'service_description' => $this->getServiceDescription($validated['courier'], $validated['service']),
                'cost' => $validated['shipping_cost'],
                'weight' => $weight,
                'origin_city_id' => config('rajaongkir.origin_city_id'),
                'destination_city_id' => $validated['city_id'],
                'status' => 'pending',
            ]);

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $total,
                'currency' => 'IDR',
                'payment_gateway' => 'doku',
                'status' => 'pending',
                'reference_number' => 'PAY-' . $order->id . '-' . Str::random(8),
            ]);

            // Clear cart
            CartItem::where('user_id', $user->id)->delete();

            // Redirect to payment
            return redirect()->route('payment.show', $order->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Get courier name
     */
    private function getCourierName($courierCode)
    {
        $couriers = config('rajaongkir.couriers');
        return $couriers[$courierCode]['name'] ?? $courierCode;
    }

    /**
     * Get service description
     */
    private function getServiceDescription($courier, $service)
    {
        $services = [
            'jne' => [
                'REG' => 'JNE Regular',
                'OKE' => 'JNE OKE',
            ],
            'pos' => [
                'REG' => 'Pos Regular',
            ],
            'tiki' => [
                'REG' => 'TIKI Regular',
                'ECO' => 'TIKI Ekonomis',
            ]
        ];

        return $services[$courier][$service] ?? "{$courier} {$service}";
    }
}
