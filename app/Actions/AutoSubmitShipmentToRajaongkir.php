<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Shipping;
use App\Services\RajaongkirService;
use App\Services\BiteshipService;

/**
 * Action untuk auto-submit shipment ke shipping provider
 * Support: Biteship (primary), Raja Ongkir (fallback)
 * 
 * Dipanggil otomatis saat payment berhasil
 */
class AutoSubmitShipmentToRajaongkir
{
    protected $biteship;
    protected $rajaongkir;

    public function __construct()
    {
        $this->biteship = new BiteshipService();
        $this->rajaongkir = new RajaongkirService();
    }

    /**
     * Execute action
     * @param Order $order
     * @return array ['success' => bool, 'tracking_number' => 'xxx', 'message' => 'xxx']
     */
    public function handle(Order $order)
    {
        try {
            // Cek jika order sudah punya shipping record
            $shipping = $order->shipping;
            
            if ($shipping && $shipping->tracking_number) {
                // Sudah punya tracking number, skip
                logger('Shipment: Order already has tracking number', [
                    'order_id' => $order->id,
                    'tracking_number' => $shipping->tracking_number
                ]);
                
                return [
                    'success' => true,
                    'tracking_number' => $shipping->tracking_number,
                    'message' => 'Order already has tracking number',
                    'skipped' => true
                ];
            }

            // Try Biteship first (if enabled)
            if (config('biteship.enabled')) {
                logger('Shipment: Trying Biteship provider');
                $result = $this->submitToBiteship($order);
                
                if ($result['success']) {
                    return $this->saveTrackingNumber($order, $result, 'biteship');
                }
            }

            // Fallback to Raja Ongkir
            logger('Shipment: Falling back to Raja Ongkir');
            $result = $this->submitToRajaongkir($order);
            
            if ($result['success']) {
                return $this->saveTrackingNumber($order, $result, 'rajaongkir');
            }

            return [
                'success' => false,
                'message' => 'Failed to create shipment'
            ];

        } catch (\Exception $e) {
            logger('Shipment: Error in auto-submit action', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Submit to Biteship
     * @param Order $order
     * @return array
     */
    private function submitToBiteship(Order $order)
    {
        $payload = [
            'courier_id' => 'jne',
            'customer_name' => $order->shipping_name,
            'customer_phone' => $order->shipping_phone,
            'address' => $order->shipping_address,
            'city' => $order->shipping_city,
            'area' => $order->shipping_province,
            'postal_code' => $order->shipping_postal_code,
            'items' => $this->buildItemsList($order),
        ];

        logger('Shipment: Submitting to Biteship', $payload);
        return $this->biteship->createShipment($payload);
    }

    /**
     * Submit to Raja Ongkir
     * @param Order $order
     * @return array
     */
    private function submitToRajaongkir(Order $order)
    {
        $shipping = $order->shipping;
        
        $payload = [
            'courier' => $shipping?->courier ?? 'jne',
            'destination_city_id' => $shipping?->destination_city_id ?? 501,
            'weight' => $this->calculateOrderWeight($order),
            'customer_name' => $order->shipping_name,
            'customer_phone' => $order->shipping_phone,
            'address' => $order->shipping_address,
            'items' => $this->buildItemsList($order),
        ];

        logger('Shipment: Submitting to Raja Ongkir', $payload);
        return $this->rajaongkir->createWaybill($payload);
    }

    /**
     * Save tracking number ke database
     * @param Order $order
     * @param array $result
     * @param string $provider
     * @return array
     */
    private function saveTrackingNumber($order, $result, $provider)
    {
        $shipping = $order->shipping;

        if (!$shipping) {
            $shipping = Shipping::create([
                'order_id' => $order->id,
                'tracking_number' => $result['tracking_number'],
                'courier' => strtoupper($result['courier'] ?? 'UNKNOWN'),
                'status' => 'pending',
                'cost' => $order->total_price - $this->calculateSubtotal($order),
                'weight' => $this->calculateOrderWeight($order),
                'destination_city_id' => 501,
            ]);
            
            logger('Shipment: New shipping record created', [
                'order_id' => $order->id,
                'shipping_id' => $shipping->id,
                'tracking_number' => $result['tracking_number'],
                'provider' => $provider
            ]);
        } else {
            $shipping->update([
                'tracking_number' => $result['tracking_number'],
                'courier' => strtoupper($result['courier'] ?? 'UNKNOWN'),
                'status' => 'pending',
            ]);
            
            logger('Shipment: Shipping record updated', [
                'order_id' => $order->id,
                'shipping_id' => $shipping->id,
                'tracking_number' => $result['tracking_number'],
                'provider' => $provider
            ]);
        }

        return [
            'success' => true,
            'tracking_number' => $result['tracking_number'],
            'provider' => $provider,
            'message' => "Shipment submitted to {$provider}",
            'is_mock' => $result['is_mock'] ?? false
        ];
    }

    /**
     * Calculate total weight of order items
     * @param Order $order
     * @return int weight in grams
     */
    private function calculateOrderWeight($order)
    {
        $totalWeight = 0;

        foreach ($order->items as $item) {
            $weight = $item->product?->weight ?? 100;
            $totalWeight += $weight * $item->quantity;
        }

        return max(100, min(30000, $totalWeight));
    }

    /**
     * Calculate subtotal (total - shipping)
     * @param Order $order
     * @return int
     */
    private function calculateSubtotal($order)
    {
        return $order->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Build items list for API
     * @param Order $order
     * @return array
     */
    private function buildItemsList($order)
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->product?->name ?? 'Product',
                'weight' => ($item->product?->weight ?? 100) * $item->quantity,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'sku' => $item->product?->sku ?? null,
            ];
        }

        return $items;
    }
}
