<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Biteship Integration Service
 * 
 * Dokumentasi API: https://documentation.biteship.com
 * Mendukung: Indonesia, Malaysia, Singapore, Thailand, Vietnam, Philippines
 */
class BiteshipService
{
    private $apiKey;
    private $baseUrl;
    private $accountId;

    public function __construct()
    {
        $this->apiKey = config('biteship.api_key');
        $this->baseUrl = 'https://api.biteship.com/v1';
        $this->accountId = config('biteship.account_id');
    }

    /**
     * Create shipment dan get tracking number
     * 
     * @param array $data
     * @return array ['success' => bool, 'tracking_number' => 'xxx', 'courier' => 'xxx']
     */
    public function createShipment($data)
    {
        try {
            $payload = [
                'courier_id' => $data['courier_id'] ?? 'jne', // jne, pos, tiki, grab, gojek, etc
                'items' => $this->buildItems($data['items'] ?? []),
                'use_insurance' => $data['use_insurance'] ?? false,
                'origin_address' => [
                    'name' => 'Joudah Store',
                    'postal_code' => config('biteship.origin_postal_code', '12345'),
                    'country_code' => 'ID',
                    'city' => config('biteship.origin_city', 'Jakarta'),
                    'address_line' => config('biteship.origin_address', 'Gudang Pusat'),
                ],
                'destination_address' => [
                    'name' => $data['customer_name'] ?? 'Customer',
                    'phone_number' => $data['customer_phone'] ?? '0',
                    'postal_code' => $data['postal_code'] ?? '12345',
                    'country_code' => 'ID',
                    'city' => $data['city'] ?? 'Jakarta',
                    'area' => $data['area'] ?? 'District',
                    'address_line' => $data['address'] ?? 'Address',
                ],
            ];

            logger('Biteship: Creating shipment', $payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/shipments', $payload);

            logger('Biteship: Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['id']) && isset($result['courier_tracking_id'])) {
                    return [
                        'success' => true,
                        'tracking_number' => $result['courier_tracking_id'],
                        'shipment_id' => $result['id'],
                        'courier' => $result['courier_id'] ?? 'biteship',
                        'status' => $result['status'] ?? 'pending',
                        'message' => 'Shipment created successfully',
                    ];
                }
            }

            // Fallback
            logger('Biteship: Fallback to mock tracking', $response->json());
            return $this->generateMockTracking($data['courier_id'] ?? 'jne');

        } catch (\Exception $e) {
            logger('Biteship: Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->generateMockTracking($data['courier_id'] ?? 'jne');
        }
    }

    /**
     * Build items array for Biteship API
     * @param array $items
     * @return array
     */
    private function buildItems($items)
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => $item['name'] ?? 'Product',
                'description' => $item['description'] ?? '',
                'value' => (int)($item['price'] ?? 0),
                'weight' => (int)($item['weight'] ?? 100),
                'quantity' => (int)($item['quantity'] ?? 1),
                'sku' => $item['sku'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Get available rates/couriers
     * @param array $data
     * @return array
     */
    public function getRates($data)
    {
        try {
            $payload = [
                'origin_postal_code' => config('biteship.origin_postal_code', '12345'),
                'destination_postal_code' => $data['postal_code'] ?? '12345',
                'couriers' => $data['couriers'] ?? ['jne', 'pos', 'tiki'],
                'items' => $this->buildItems($data['items'] ?? []),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/rates', $payload);

            if ($response->successful()) {
                return $response->json('pricing') ?? [];
            }

            return [];

        } catch (\Exception $e) {
            logger('Biteship getRates Error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Track shipment
     * @param string $trackingId
     * @return array
     */
    public function trackShipment($trackingId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/shipments/track', [
                'tracking_id' => $trackingId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Tracking not found'];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate mock tracking number
     * @param string $courier
     * @return array
     */
    private function generateMockTracking($courier = 'jne')
    {
        $prefix = $this->getCourierPrefix(strtoupper($courier));
        $timestamp = date('YmdHis');
        $random = mt_rand(100, 999);
        $trackingNumber = $prefix . $timestamp . $random;

        logger('Biteship: Generated mock tracking', ['tracking_number' => $trackingNumber]);

        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'courier' => strtoupper($courier),
            'is_mock' => true,
            'message' => 'Using mock tracking number (API fallback)',
        ];
    }

    /**
     * Get courier prefix for mock tracking
     * @param string $courier
     * @return string
     */
    private function getCourierPrefix($courier)
    {
        return match (strtoupper($courier)) {
            'JNE' => '51',
            'POS' => 'EA',
            'TIKI' => '00',
            'GRAB' => 'GR',
            'GOJEK' => 'GO',
            default => 'BS', // Biteship
        };
    }
}
