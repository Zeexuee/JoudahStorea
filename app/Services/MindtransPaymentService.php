<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * MindTrans Payment Service
 * Integration for MindTrans payment gateway
 * 
 * Status: PREPARED FOR FUTURE USE
 * Note: Waiting for MindTrans account approval
 */
class MindtransPaymentService
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl;
    private $merchantId;
    private $isConfigured = false;

    public function __construct()
    {
        $this->apiKey = config('payment.mindtrans_api_key');
        $this->apiSecret = config('payment.mindtrans_api_secret');
        $this->merchantId = config('payment.mindtrans_merchant_id');
        $this->baseUrl = config('payment.mindtrans_base_url');
        
        // Verify that MindTrans is configured
        $this->isConfigured = !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Check if MindTrans is configured and ready
     */
    public function isReady()
    {
        return $this->isConfigured;
    }

    /**
     * Create payment request
     * @param Payment $payment
     * @param array $itemDetails
     * @return array
     */
    public function createPayment(Payment $payment, $itemDetails = [])
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'error' => 'MindTrans is not configured. Please provide API credentials in .env',
                'status' => 'not_configured'
            ];
        }

        try {
            $externalId = $this->generateExternalId($payment->order_id);
            $amount = (int)$payment->amount;

            $payload = [
                'transaction_details' => [
                    'order_id' => $externalId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => explode(' ', $payment->order->shipping_name)[0],
                    'last_name' => implode(' ', array_slice(explode(' ', $payment->order->shipping_name), 1)),
                    'email' => $payment->order->user->email,
                    'phone' => $payment->order->shipping_phone,
                    'billing_address' => [
                        'first_name' => explode(' ', $payment->order->shipping_name)[0],
                        'last_name' => implode(' ', array_slice(explode(' ', $payment->order->shipping_name), 1)),
                        'email' => $payment->order->user->email,
                        'phone' => $payment->order->shipping_phone,
                        'address' => $payment->order->shipping_address,
                        'city' => $payment->order->shipping_city,
                        'postal_code' => $payment->order->shipping_postal_code,
                        'country_code' => 'IDN',
                    ],
                    'shipping_address' => [
                        'first_name' => explode(' ', $payment->order->shipping_name)[0],
                        'last_name' => implode(' ', array_slice(explode(' ', $payment->order->shipping_name), 1)),
                        'email' => $payment->order->user->email,
                        'phone' => $payment->order->shipping_phone,
                        'address' => $payment->order->shipping_address,
                        'city' => $payment->order->shipping_city,
                        'postal_code' => $payment->order->shipping_postal_code,
                        'country_code' => 'IDN',
                    ],
                ],
                'item_details' => $itemDetails ?: $this->getDefaultItemDetails($payment),
                'vt_web' => [
                    'enabled' => true,
                ],
                'enabled_payments' => ['bank_transfer', 'qris', 'gopay', 'ovo', 'dana'],
            ];

            $response = Http::withBasicAuth($this->apiKey, 'Bearer')
                ->post($this->baseUrl . '/charge', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Save external ID to payment record
                $payment->update([
                    'external_id' => $data['transaction_id'] ?? $externalId,
                    'payment_gateway' => 'mindtrans',
                    'payment_method' => $data['payment_type'] ?? null,
                    'metadata' => [
                        'checkout_url' => $data['redirect_url'] ?? null,
                        'transaction_id' => $data['transaction_id'] ?? null,
                        'status' => $data['transaction_status'] ?? 'pending',
                    ]
                ]);

                logger('MindTrans Payment Created', [
                    'order_id' => $payment->order_id,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'amount' => $amount
                ]);

                return [
                    'success' => true,
                    'checkout_url' => $data['redirect_url'] ?? null,
                    'external_id' => $data['transaction_id'] ?? $externalId,
                    'payment_gateway' => 'mindtrans',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error_message') ?? 'Failed to create payment',
                'response' => $response->json()
            ];
        } catch (\Exception $e) {
            logger('MindTrans Payment Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment status
     * @param Payment $payment
     * @return array
     */
    public function verifyPayment(Payment $payment)
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'error' => 'MindTrans is not configured'
            ];
        }

        if (!$payment->external_id) {
            return [
                'success' => false,
                'error' => 'No transaction ID found'
            ];
        }

        try {
            $response = Http::withBasicAuth($this->apiKey, 'Bearer')
                ->get($this->baseUrl . '/' . $payment->external_id . '/status');

            if ($response->successful()) {
                $data = $response->json();
                $status = $this->mapPaymentStatus($data['transaction_status'] ?? 'pending');

                $payment->update([
                    'status' => $status,
                    'payment_method' => $data['payment_type'] ?? null,
                    'metadata' => array_merge($payment->metadata ?? [], $data)
                ]);

                if ($status === 'completed') {
                    $payment->update(['paid_at' => now()]);
                    $payment->order->update(['status' => 'processing']);
                }

                logger('MindTrans Payment Verified', [
                    'order_id' => $payment->order_id,
                    'status' => $status
                ]);

                return [
                    'success' => true,
                    'status' => $status,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to verify payment'
            ];
        } catch (\Exception $e) {
            logger('MindTrans Verify Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook callback
     * @param array $payload
     * @return bool
     */
    public function processCallback($payload)
    {
        if (!$this->verifyCallbackSignature($payload)) {
            logger('Invalid MindTrans callback signature');
            return false;
        }

        $transactionId = $payload['transaction_id'] ?? null;

        if (!$transactionId) {
            return false;
        }

        // Find payment by external ID
        $payment = Payment::where('external_id', $transactionId)->first();

        if (!$payment) {
            return false;
        }

        // Map and update payment status
        $status = $this->mapPaymentStatus($payload['transaction_status'] ?? 'pending');
        $payment->update([
            'status' => $status,
            'payment_method' => $payload['payment_type'] ?? null,
            'metadata' => array_merge($payment->metadata ?? [], $payload)
        ]);

        if ($status === 'completed') {
            $payment->update(['paid_at' => now()]);
            $payment->order->update(['status' => 'processing']);
        }

        logger('MindTrans Callback Processed', [
            'transaction_id' => $transactionId,
            'status' => $status
        ]);

        return true;
    }

    /**
     * Verify callback signature
     * @param array $payload
     * @return bool
     */
    private function verifyCallbackSignature($payload)
    {
        $signature = $payload['signature'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;

        if (!$signature) {
            return false;
        }

        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $this->apiSecret);

        return hash_equals($signature, $signatureKey);
    }

    /**
     * Get default item details from order
     */
    private function getDefaultItemDetails(Payment $payment)
    {
        $items = [];

        foreach ($payment->order->items as $orderItem) {
            $items[] = [
                'id' => (string)$orderItem->product_id,
                'price' => (int)$orderItem->price,
                'quantity' => (int)$orderItem->quantity,
                'name' => $orderItem->product->name,
            ];
        }

        // Add shipping cost
        if ($payment->order->shipping) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int)$payment->order->shipping->cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim - ' . $payment->order->shipping->courier_name,
            ];
        }

        return $items;
    }

    /**
     * Map MindTrans status to our system
     */
    private function mapPaymentStatus($mindtransStatus)
    {
        return match($mindtransStatus) {
            'capture', 'settlement' => 'completed',
            'pending' => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            'refund' => 'refunded',
            default => 'pending'
        };
    }

    /**
     * Generate external ID
     */
    private function generateExternalId($orderId)
    {
        return 'ORDER-' . $orderId . '-' . time();
    }

    /**
     * Get configuration status
     */
    public function getConfigStatus()
    {
        return [
            'is_configured' => $this->isConfigured,
            'api_key_set' => !empty($this->apiKey),
            'api_secret_set' => !empty($this->apiSecret),
            'merchant_id_set' => !empty($this->merchantId),
            'base_url' => $this->baseUrl ?? 'not set',
            'message' => $this->isConfigured 
                ? 'MindTrans is fully configured and ready to use'
                : 'MindTrans credentials are missing. Please set them in .env file',
        ];
    }
}
