<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DokuPaymentService
{
    private $apiKey;
    private $secretKey;
    private $baseUrl;
    private $merchantId;

    public function __construct()
    {
        $this->apiKey = config('doku.api_key');
        $this->secretKey = config('doku.secret_key');
        $this->baseUrl = config('doku.base_url');
        $this->merchantId = config('doku.merchant_id');
    }

    /**
     * Create payment request
     * @param Payment $payment
     * @param array $itemDetails
     * @return array
     */
    public function createPayment(Payment $payment, $itemDetails = [])
    {
        $externalId = $this->generateExternalId($payment->order_id);
        $amount = (int)$payment->amount;

        $payload = [
            'amount' => $amount,
            'invoice_id' => $externalId,
            'customer' => [
                'id' => 'CUST-' . $payment->order->user_id,
                'email' => $payment->order->user->email,
                'name' => $payment->order->shipping_name,
                'phone' => $payment->order->shipping_phone,
            ],
            'items' => $itemDetails ?: $this->getDefaultItemDetails($payment),
            'channel_preferences' => [
                'priority' => ['VIRTUAL_ACCOUNT', 'QRIS', 'E_WALLET'],
                'disabled' => []
            ],
            'order_extended_info' => [
                'payment_reminder' => true,
            ]
        ];

        $signature = $this->generateSignature($payload);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateToken($externalId),
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/checkout/payment', $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            // Save external ID to payment record
            $payment->update([
                'external_id' => $externalId,
                'metadata' => [
                    'checkout_url' => $data['checkout_url'] ?? null,
                    'invoice_id' => $data['invoice_id'] ?? null,
                ]
            ]);

            return [
                'success' => true,
                'checkout_url' => $data['checkout_url'] ?? null,
                'external_id' => $externalId,
                'data' => $data
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('error.message') ?? 'Failed to create payment',
            'response' => $response->json()
        ];
    }

    /**
     * Verify payment status
     * @param Payment $payment
     * @return array
     */
    public function verifyPayment(Payment $payment)
    {
        if (!$payment->external_id) {
            return [
                'success' => false,
                'error' => 'No external ID found for this payment'
            ];
        }

        $signature = $this->generateSignature(['invoice_id' => $payment->external_id]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->generateToken($payment->external_id),
            'X-Signature' => $signature,
        ])->get($this->baseUrl . '/checkout/payment/' . $payment->external_id);

        if ($response->successful()) {
            $data = $response->json();
            
            // Update payment status
            if (isset($data['status'])) {
                $payment->update([
                    'status' => $this->mapPaymentStatus($data['status']),
                    'metadata' => array_merge($payment->metadata ?? [], $data)
                ]);

                if ($data['status'] === 'COMPLETED') {
                    $payment->update([
                        'paid_at' => now(),
                        'status' => 'completed'
                    ]);
                }
            }

            return [
                'success' => true,
                'data' => $data
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('error.message') ?? 'Failed to verify payment',
            'response' => $response->json()
        ];
    }

    /**
     * Get default item details from order
     * @param Payment $payment
     * @return array
     */
    private function getDefaultItemDetails(Payment $payment)
    {
        $items = [];

        foreach ($payment->order->items as $orderItem) {
            $items[] = [
                'id' => (string)$orderItem->product_id,
                'name' => $orderItem->product->name,
                'price' => (int)$orderItem->price,
                'quantity' => (int)$orderItem->quantity,
            ];
        }

        // Add shipping cost if exists
        if ($payment->order->shipping) {
            $items[] = [
                'id' => 'shipping',
                'name' => 'Ongkos Kirim - ' . $payment->order->shipping->courier_name,
                'price' => (int)$payment->order->shipping->cost,
                'quantity' => 1,
            ];
        }

        return $items;
    }

    /**
     * Generate signature for request
     * @param array $payload
     * @return string
     */
    private function generateSignature($payload)
    {
        $jsonString = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonString, $this->secretKey);
        return $signature;
    }

    /**
     * Generate Bearer token
     * @param string $invoiceId
     * @return string
     */
    private function generateToken($invoiceId)
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub' => $this->merchantId,
            'name' => 'Joudah Store',
            'iat' => time(),
            'exp' => time() + 3600,
            'invoice_id' => $invoiceId,
            'api_key' => $this->apiKey,
        ]));

        $signature = hash_hmac('sha256', $header . '.' . $payload, $this->secretKey, true);
        $signature = base64_encode($signature);

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Generate external ID
     * @param int $orderId
     * @return string
     */
    private function generateExternalId($orderId)
    {
        return 'ORDER-' . $orderId . '-' . Str::random(8);
    }

    /**
     * Map payment status from Doku to our system
     * @param string $dokuStatus
     * @return string
     */
    private function mapPaymentStatus($dokuStatus)
    {
        return match($dokuStatus) {
            'PENDING' => 'pending',
            'PROCESSING' => 'processing',
            'COMPLETED' => 'completed',
            'FAILED' => 'failed',
            'EXPIRED' => 'expired',
            'CANCELLED' => 'cancelled',
            'REFUNDED' => 'refunded',
            default => 'pending'
        };
    }

    /**
     * Process webhook callback from Doku
     * @param array $payload
     * @return bool
     */
    public function processCallback($payload)
    {
        // Verify signature
        $signature = $payload['signature'] ?? null;
        $invoiceId = $payload['invoice_id'] ?? null;

        if (!$this->verifyCallbackSignature($payload, $signature)) {
            return false;
        }

        // Find payment by external ID
        $payment = Payment::where('external_id', $invoiceId)->first();

        if (!$payment) {
            return false;
        }

        // Update payment status
        if (isset($payload['status'])) {
            $status = $this->mapPaymentStatus($payload['status']);
            $payment->update([
                'status' => $status,
                'metadata' => array_merge($payment->metadata ?? [], $payload)
            ]);

            if ($status === 'completed') {
                $payment->update(['paid_at' => now()]);
                // Update order status
                $payment->order->update(['status' => 'processing']);
            }
        }

        return true;
    }

    /**
     * Verify callback signature
     * @param array $payload
     * @param string $signature
     * @return bool
     */
    private function verifyCallbackSignature($payload, $signature)
    {
        $dataToSign = json_encode(array_filter($payload, function ($key) {
            return $key !== 'signature';
        }, ARRAY_FILTER_USE_KEY));

        $expectedSignature = hash_hmac('sha256', $dataToSign, $this->secretKey);

        return hash_equals($expectedSignature, $signature);
    }
}
