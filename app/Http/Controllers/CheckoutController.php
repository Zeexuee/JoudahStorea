<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipping;
use App\Services\RajaongkirService;
use App\Services\DokuPaymentService;
use App\Services\MockPaymentService;
use App\Services\MindtransPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    private $rajaongkir;
    private $paymentService;
    private $activeGateway;

    public function __construct()
    {
        $this->rajaongkir = new RajaongkirService();
        $this->activeGateway = config('payment.gateway', 'mock');
        $this->paymentService = $this->getPaymentService();
    }

    /**
     * Get the appropriate payment service instance
     */
    private function getPaymentService()
    {
        return match($this->activeGateway) {
            'mock' => new MockPaymentService(),
            'mindtrans' => new MindtransPaymentService(),
            'doku' => new DokuPaymentService(),
            default => new MockPaymentService(), // Fallback to mock
        };
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

            // Testing mode: force all shipping options to Rp0.
            $costs = [
                [
                    'courier_code' => 'JNE',
                    'courier_name' => 'JNE - REG',
                    'service' => 'REG',
                    'description' => 'Reguler',
                    'cost' => 0,
                    'estimated_days' => 2,
                ],
                [
                    'courier_code' => 'POS',
                    'courier_name' => 'POS Indonesia - REG',
                    'service' => 'REG',
                    'description' => 'Reguler',
                    'cost' => 0,
                    'estimated_days' => 2,
                ],
                [
                    'courier_code' => 'TIKI',
                    'courier_name' => 'TIKI - REG',
                    'service' => 'REG',
                    'description' => 'Reguler',
                    'cost' => 0,
                    'estimated_days' => 2,
                ],
            ];

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
        logger('Checkout process started', [
            'user_id' => auth()->id(),
            'request_data' => $request->except(['_token'])
        ]);

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

        logger('Validation passed', ['validated' => $validated]);

        try {
            $user = $request->user();

            // Get cart items
            $cartItems = CartItem::where('user_id', $user->id)
                ->with('product')
                ->get();

            logger('Cart items retrieved', [
                'count' => $cartItems->count(),
                'user_id' => $user->id
            ]);

            if ($cartItems->isEmpty()) {
                logger('Cart is empty, redirecting to cart page');
                return redirect()->route('cart.index')
                    ->with('error', 'Keranjang belanja Anda kosong');
            }

            // Calculate subtotal
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            // Calculate fees
            $shippingCost = 0;
            $adminFee = 0;
            $paymentGatewayFee = 0;
            
            // Calculate total
            $total = $subtotal + $shippingCost + $adminFee + $paymentGatewayFee;

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
                'cost' => $shippingCost,
                'weight' => $weight,
                'origin_city_id' => config('rajaongkir.origin_city_id'),
                'destination_city_id' => $validated['city_id'],
                'status' => 'pending',
            ]);

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'reference_number' => 'PAY-' . date('YmdHis') . '-' . Str::random(4),
                'amount' => $total,
                'currency' => 'IDR',
                'payment_gateway' => $this->activeGateway, // 'mindtrans' or 'mock'
                'payment_method' => 'pending', // Will be determined in Snap payment form
                'status' => 'pending',
                'metadata' => [],
            ]);

            logger('Payment created successfully', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $total,
                'payment_gateway' => $this->activeGateway
            ]);

            // Keep cart until payment is confirmed successful.
            // This allows users to navigate back to checkout without being forced to cart page.
            logger('Cart retained until payment success', ['user_id' => $user->id]);

            // Redirect to payment processing view (which will auto-submit POST to payment.process)
            logger('Redirecting to payment processing', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
            ]);
            
            return view('payment.processing', [
                'order' => $order,
                'returnTo' => route('checkout.show'),
            ])
                ->with('success', 'Pesanan berhasil dibuat. Silakan tunggu saat payment form dimuat...');
        } catch (\Exception $e) {
            logger('Exception during checkout', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Prepare item details for payment gateway
     */
    private function prepareItemDetails(Order $order)
    {
        $items = [];

        // Calculate subtotal for product items only
        $subtotal = 0;
        foreach ($order->items as $orderItem) {
            $itemTotal = (int)$orderItem->price * (int)$orderItem->quantity;
            $subtotal += $itemTotal;
            
            $items[] = [
                'id' => (string)$orderItem->product_id,
                'name' => $orderItem->product->name,
                'price' => (int)$orderItem->price,
                'quantity' => (int)$orderItem->quantity,
            ];
        }

        return $items;
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
