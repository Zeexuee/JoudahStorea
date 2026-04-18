<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Midtrans Payment Service (Snap API)
 * Integration with Midtrans payment gateway
 * 
 * Documentation: https://docs.midtrans.com
 * Dashboard: https://dashboard.midtrans.com
 */
class MindtransPaymentService
{
    private $serverKey;
    private $clientKey;
    private $baseUrl;
    private $coreApiBaseUrl;
    private $isConfigured = false;

    public function __construct()
    {
        $this->serverKey = config('payment.mindtrans.api_key');
        $this->clientKey = config('payment.mindtrans.api_secret');
        $mode = config('payment.mindtrans.mode', 'sandbox');
        
        // Set base URL based on mode
        $this->baseUrl = $mode === 'production'
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';

        // Core API URL (for direct charge/status API)
        $this->coreApiBaseUrl = $mode === 'production'
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
        
        // Verify that Midtrans is configured
        $this->isConfigured = !empty($this->serverKey);
    }

    /**
     * Check if Midtrans is configured and ready
     */
    public function isReady()
    {
        return $this->isConfigured;
    }

    /**
     * Create payment request using Midtrans Snap
     * @param Payment $payment
     * @param array $itemDetails
     * @param string|null $paymentMethod User's chosen payment method
     * @return array
     */
    public function createPayment(Payment $payment, $itemDetails = [], $paymentMethod = null)
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'error' => 'Midtrans is not configured. Please provide Server Key in .env (MINDTRANS_API_KEY)',
                'status' => 'not_configured'
            ];
        }

        try {
            $externalId = $this->generateExternalId($payment->order_id);
            $amount = $this->resolveMidtransAmount($payment);

            // Prepare customer details with null checks
            $nameParts = explode(' ', $payment->order->shipping_name ?? 'Customer', 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $resolvedItemDetails = $this->resolveItemDetailsForMidtrans($payment, $itemDetails, $amount);

            $payload = [
                'transaction_details' => [
                    'order_id' => $externalId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $payment->order->user->email ?? 'customer@example.com',
                    'phone' => $payment->order->shipping_phone ?? '',
                ],
                'item_details' => $resolvedItemDetails,
            ];

            // Add enabled payment methods based on user's choice
            if ($paymentMethod) {
                $payload['enabled_payments'] = $this->mapPaymentMethod($paymentMethod);
            }

            // Add shipping address if available
            if ($payment->order->shipping_address) {
                $payload['customer_details']['shipping_address'] = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $payment->order->shipping_phone ?? '',
                    'address' => $payment->order->shipping_address,
                    'city' => $payment->order->shipping_city ?? '',
                    'postal_code' => $payment->order->shipping_postal_code ?? '',
                    'country_code' => 'IDN',
                ];
            }

            logger('Midtrans Payment Request', [
                'order_id' => $externalId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'enabled_payments' => $payload['enabled_payments'] ?? 'all',
                'item_details' => $payload['item_details'],
                'item_total' => array_sum(array_map(function($item) {
                    return $item['price'] * $item['quantity'];
                }, $payload['item_details'])),
                'url' => $this->baseUrl . '/transactions'
            ]);

            // Call Midtrans Snap API
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/transactions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Midtrans Snap returns: token, redirect_url
                $snapToken = $data['token'] ?? null;
                $redirectUrl = $data['redirect_url'] ?? null;

                if (!$snapToken || !$redirectUrl) {
                    logger('Midtrans Response Missing Data', ['response' => $data]);
                    return [
                        'success' => false,
                        'error' => 'Invalid response from Midtrans',
                    ];
                }
                
                // Save transaction info to payment record
                $payment->update([
                    'external_id' => $externalId,
                    'payment_gateway' => 'midtrans',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'snap_token' => $snapToken,
                        'checkout_url' => $redirectUrl,
                        'order_id' => $externalId,
                        'midtrans_gross_amount' => $amount,
                        'midtrans_force_test_amount' => $this->isForceTestAmountEnabled(),
                    ])
                ]);

                logger('Midtrans Payment Created', [
                    'order_id' => $payment->order_id,
                    'external_id' => $externalId,
                    'snap_token' => $snapToken,
                    'amount' => $amount
                ]);

                return [
                    'success' => true,
                    'checkout_url' => $redirectUrl,
                    'snap_token' => $snapToken,
                    'external_id' => $externalId,
                    'payment_gateway' => 'midtrans',
                ];
            }

            $errorMessage = $response->json('message') ?? $response->json('error_messages.0') ?? 'Failed to create payment';
            
            logger('Midtrans Payment Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'response' => $response->json(),
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            logger('Midtrans Payment Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create direct payment using Midtrans Core API (without Snap UI)
     * Currently used for QRIS-first custom checkout flow.
     *
     * @param Payment $payment
     * @param array $itemDetails
     * @param string $paymentMethod
     * @return array
     */
    public function createDirectPayment(Payment $payment, $itemDetails = [], $paymentMethod = 'qris', array $options = [])
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'error' => 'Midtrans is not configured. Please provide Server Key in .env (MINDTRANS_API_KEY)',
                'status' => 'not_configured'
            ];
        }

        try {
            $externalId = $this->generateExternalId($payment->order_id);
            $amount = $this->resolveMidtransAmount($payment);

            $nameParts = explode(' ', $payment->order->shipping_name ?? 'Customer', 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            // Production often enables "QRIS Dinamis GoPay" instead of native qris charge.
            // For qris selection in UI, route charge through gopay channel to get a scannable QR.
            $midtransPaymentType = match ($paymentMethod) {
                'cstore' => 'cstore',
                'qris' => 'gopay',
                default => $paymentMethod,
            };

            $resolvedItemDetails = $this->resolveItemDetailsForMidtrans($payment, $itemDetails, $amount);

            $payload = [
                'payment_type' => $midtransPaymentType,
                'transaction_details' => [
                    'order_id' => $externalId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $payment->order->user->email ?? 'customer@example.com',
                    'phone' => $payment->order->shipping_phone ?? '',
                ],
                'item_details' => $resolvedItemDetails,
            ];

            $callbackUrl = rtrim(config('app.url'), '/') . '/orders/' . $payment->order_id;

            if (in_array($paymentMethod, ['gopay', 'qris'], true)) {
                $payload['gopay'] = [
                    'enable_callback' => true,
                    'callback_url' => $callbackUrl,
                ];
            }

            if ($paymentMethod === 'shopeepay') {
                $payload['shopeepay'] = [
                    'callback_url' => $callbackUrl,
                ];
            }

            if ($paymentMethod === 'bank_transfer') {
                $bank = $options['bank'] ?? 'bca';
                $payload['bank_transfer'] = [
                    'bank' => $bank,
                ];
            }

            if ($paymentMethod === 'cstore') {
                $store = $options['store'] ?? 'indomaret';
                $payload['cstore'] = [
                    'store' => $store,
                    'message' => 'Pembayaran pesanan Joudah Store',
                ];
            }

            $chargeUrl = $this->coreApiBaseUrl . '/charge';

            logger('Midtrans Direct Payment Request', [
                'order_id' => $externalId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'item_total' => array_sum(array_map(function ($item) {
                    return $item['price'] * $item['quantity'];
                }, $payload['item_details'])),
                'url' => $chargeUrl,
            ]);

            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($chargeUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $directDetails = $this->extractDirectDetailsFromChargeResponse($data, $paymentMethod);

                if (
                    in_array($paymentMethod, ['qris', 'gopay'], true)
                    && empty($directDetails['qr_url'])
                ) {
                    $statusMessage = $data['status_message'] ?? null;
                    logger('Midtrans Direct Payment Missing QR', ['response' => $data]);
                    return [
                        'success' => false,
                        'error' => $statusMessage ?: 'QR code tidak tersedia dari Midtrans',
                        'response' => $data,
                    ];
                }

                $payment->update([
                    'external_id' => $externalId,
                    'payment_gateway' => 'midtrans',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'direct_payment' => true,
                        'direct_payment_method' => $paymentMethod,
                        'direct_qr_url' => $directDetails['qr_url'] ?? null,
                        'direct_deeplink_url' => $directDetails['deeplink_url'] ?? null,
                        'direct_payment_code' => $directDetails['payment_code'] ?? null,
                        'direct_store' => $directDetails['store'] ?? null,
                        'direct_bank' => $directDetails['bank'] ?? null,
                        'direct_va_number' => $directDetails['va_number'] ?? null,
                        'transaction_status' => $data['transaction_status'] ?? 'pending',
                        'transaction_id' => $data['transaction_id'] ?? null,
                        'expiry_time' => $data['expiry_time'] ?? null,
                        'midtrans_gross_amount' => $amount,
                        'midtrans_force_test_amount' => $this->isForceTestAmountEnabled(),
                    ]),
                ]);

                logger('Midtrans Direct Payment Created', [
                    'order_id' => $payment->order_id,
                    'external_id' => $externalId,
                    'payment_method' => $paymentMethod,
                ]);

                return [
                    'success' => true,
                    'payment_type' => $paymentMethod,
                    'external_id' => $externalId,
                    'qr_url' => $directDetails['qr_url'] ?? null,
                    'deeplink_url' => $directDetails['deeplink_url'] ?? null,
                    'payment_code' => $directDetails['payment_code'] ?? null,
                    'store' => $directDetails['store'] ?? null,
                    'bank' => $directDetails['bank'] ?? null,
                    'va_number' => $directDetails['va_number'] ?? null,
                    'expiry_time' => $data['expiry_time'] ?? null,
                    'payment_gateway' => 'midtrans',
                    'raw' => $data,
                ];
            }

            $responseJson = $response->json();
            $rawBody = $response->body();

            $errorMessage = $responseJson['status_message']
                ?? $responseJson['message']
                ?? ($responseJson['error_messages'][0] ?? null)
                ?? $rawBody
                ?? 'Failed to create direct payment';

            logger('Midtrans Direct Payment Failed', [
                'status' => $response->status(),
                'response' => $responseJson,
                'raw_body' => $rawBody,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'response' => $responseJson,
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            logger('Midtrans Direct Payment Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status via Midtrans Transaction Status API
     * @param Payment $payment
     * @return array
     */
    public function verifyPayment(Payment $payment)
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'error' => 'Midtrans is not configured'
            ];
        }

        if (!$payment->external_id) {
            return [
                'success' => false,
                'error' => 'No transaction ID found'
            ];
        }

        try {
            // Midtrans Transaction Status API
            $statusUrl = $this->coreApiBaseUrl . '/' . $payment->external_id . '/status';

            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders(['Accept' => 'application/json'])
                ->get($statusUrl);

            if ($response->successful()) {
                $data = $response->json();
                $transactionStatus = $data['transaction_status'] ?? 'pending';
                $fraudStatus = $data['fraud_status'] ?? 'accept';
                
                $status = $this->mapPaymentStatus($transactionStatus, $fraudStatus);

                $payment->update([
                    'status' => $status,
                    'payment_method' => $data['payment_type'] ?? null,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'transaction_status' => $transactionStatus,
                        'fraud_status' => $fraudStatus,
                        'last_verified' => now()->toIso8601String(),
                    ])
                ]);

                if ($status === 'completed' && !$payment->paid_at) {
                    $payment->update(['paid_at' => now()]);
                    $payment->order->update(['status' => 'processing']);
                }

                logger('Midtrans Payment Verified', [
                    'order_id' => $payment->order_id,
                    'external_id' => $payment->external_id,
                    'transaction_status' => $transactionStatus,
                    'mapped_status' => $status
                ]);

                return [
                    'success' => true,
                    'status' => $status,
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                    'data' => $data
                ];
            }

            logger('Midtrans Verify Failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to verify payment',
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            logger('Midtrans Verify Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook callback from Midtrans
     * @param array $payload
     * @return bool
     */
    public function processCallback($payload)
    {
        logger('Midtrans Callback Received', ['payload' => $payload]);

        // Verify signature first
        if (!$this->verifyCallbackSignature($payload)) {
            logger('Invalid Midtrans callback signature', ['payload' => $payload]);
            return false;
        }

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        if (!$orderId || !$transactionStatus) {
            logger('Midtrans Callback Missing Data', ['payload' => $payload]);
            return false;
        }

        // Find payment by external ID (order_id from Midtrans)
        $payment = Payment::where('external_id', $orderId)->first();

        if (!$payment) {
            logger('Midtrans Callback - Payment Not Found', ['order_id' => $orderId]);
            return false;
        }

        // Map and update payment status
        $status = $this->mapPaymentStatus($transactionStatus, $fraudStatus);
        
        $payment->update([
            'status' => $status,
            'payment_method' => $payload['payment_type'] ?? null,
            'metadata' => array_merge($payment->metadata ?? [], [
                'transaction_id' => $payload['transaction_id'] ?? null,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'settlement_time' => $payload['settlement_time'] ?? null,
                'transaction_time' => $payload['transaction_time'] ?? null,
            ])
        ]);

        // Update order status if payment completed
        if ($status === 'completed' && !$payment->paid_at) {
            $payment->update(['paid_at' => now()]);
            $payment->order->update(['status' => 'processing']);
            
            logger('Midtrans Payment Completed - Order Updated', [
                'order_id' => $payment->order_id,
                'payment_id' => $payment->id
            ]);
        }

        // Handle failed payment
        if ($status === 'failed' || $status === 'cancelled') {
            $payment->order->update(['status' => 'cancelled']);
            
            logger('Midtrans Payment Failed - Order Cancelled', [
                'order_id' => $payment->order_id,
                'status' => $status
            ]);
        }

        logger('Midtrans Callback Processed', [
            'order_id' => $payment->order_id,
            'external_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'mapped_status' => $status
        ]);


        return true;
    }

    /**
     * Verify callback signature from Midtrans
     * @param array $payload
     * @return bool
     */
    private function verifyCallbackSignature($payload)
    {
        $signature = $payload['signature_key'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;

        if (!$signature) {
            logger('Midtrans Callback - No signature provided');
            return false;
        }

        // Midtrans signature: SHA512(order_id+status_code+gross_amount+server_key)
        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        $isValid = hash_equals($signature, $signatureKey);
        
        if (!$isValid) {
            logger('Midtrans Callback - Invalid signature', [
                'expected' => $signatureKey,
                'received' => $signature
            ]);
        }

        return $isValid;
    }

    /**
     * Get default item details from order
     */
    private function getDefaultItemDetails(Payment $payment)
    {
        $items = [];
        $subtotal = 0;

        foreach ($payment->order->items as $orderItem) {
            $itemTotal = (int)$orderItem->price * (int)$orderItem->quantity;
            $subtotal += $itemTotal;
            
            $items[] = [
                'id' => (string)$orderItem->product_id,
                'price' => (int)$orderItem->price,
                'quantity' => (int)$orderItem->quantity,
                'name' => $orderItem->product->name ?? 'Product',
            ];
        }

        return $items;
    }

    /**
     * Midtrans only accepts integer IDR values. If testing override is enabled,
     * force charge to configured test amount (minimum 1 IDR).
     */
    private function resolveMidtransAmount(Payment $payment): int
    {
        if (!$this->isForceTestAmountEnabled()) {
            return max(1, (int) $payment->amount);
        }

        $configured = (int) config('payment.mindtrans.testing_amount_idr', 1);

        return max(1, $configured);
    }

    private function resolveItemDetailsForMidtrans(Payment $payment, array $itemDetails, int $grossAmount): array
    {
        if (!$this->isForceTestAmountEnabled()) {
            return $itemDetails ?: $this->getDefaultItemDetails($payment);
        }

        // Keep Midtrans payload totals valid in testing mode by sending a single synthetic item.
        return [[
            'id' => 'TEST-AMOUNT',
            'name' => 'Testing Amount Override',
            'price' => $grossAmount,
            'quantity' => 1,
        ]];
    }

    private function isForceTestAmountEnabled(): bool
    {
        return (bool) config('payment.mindtrans.force_test_amount', false);
    }

    /**
     * Map Midtrans transaction status to our system status
     * 
     * Midtrans statuses:
     * - capture: Credit card transaction captured (authorized & collected)
     * - settlement: Transaction settled (funds received)
     * - pending: Transaction created, waiting for customer to complete payment
     * - deny: Payment denied by bank/fraud detection
     * - cancel: Transaction cancelled
     * - expire: Transaction expired (customer didn't complete payment)
     * - refund: Transaction refunded
     * - partial_refund: Transaction partially refunded
     */
    private function mapPaymentStatus($transactionStatus, $fraudStatus = 'accept')
    {
        // Handle fraud detection
        if ($fraudStatus === 'deny') {
            return 'failed';
        }

        if ($fraudStatus === 'challenge') {
            return 'pending'; // Wait for manual review
        }

        // Map transaction status
        return match($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'completed' : 'pending',
            'settlement' => 'completed',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'failed',
            'refund', 'partial_refund' => 'refunded',
            default => 'pending'
        };
    }

    /**
     * Generate unique external ID for Midtrans
     */
    private function generateExternalId($orderId)
    {
        return 'ORDER-' . $orderId . '-' . time();
    }

    /**
     * Get configuration status for debugging
     */
    public function getConfigStatus()
    {
        return [
            'is_configured' => $this->isConfigured,
            'server_key_set' => !empty($this->serverKey),
            'client_key_set' => !empty($this->clientKey),
            'base_url' => $this->baseUrl,
            'environment' => config('payment.mindtrans.mode', 'sandbox'),
            'message' => $this->isConfigured 
                ? 'Midtrans is fully configured and ready to use'
                : 'Midtrans Server Key is missing. Please set MINDTRANS_API_KEY in .env file',
        ];
    }

    /**
     * Map user-selected payment method to Midtrans enabled_payments array
     * @param string $paymentMethod
     * @return array
     */
    private function mapPaymentMethod($paymentMethod)
    {
        $paymentMap = [
            'credit_card' => ['credit_card'],
            'gopay' => ['gopay'],
            'shopeepay' => ['shopeepay'],
            // QRIS on some sandbox accounts may not be fully enabled.
            // Add e-wallet fallback so at least one QR-capable channel appears.
            'qris' => ['qris', 'gopay', 'shopeepay'],
            'bank_transfer' => ['bca_va', 'bni_va', 'bri_va', 'permata_va', 'other_va'],
            'cstore' => ['indomaret', 'alfamart'],
        ];

        return $paymentMap[$paymentMethod] ?? [];
    }

    /**
     * Extract QR URL from Midtrans Core API charge response.
     *
     * @param array $data
     * @return string|null
     */
    private function extractDirectDetailsFromChargeResponse(array $data, string $paymentMethod)
    {
        $result = [
            'qr_url' => null,
            'deeplink_url' => null,
            'payment_code' => null,
            'store' => null,
            'bank' => null,
            'va_number' => null,
        ];

        $actions = $data['actions'] ?? [];

        foreach ($actions as $action) {
            $name = $action['name'] ?? '';
            $url = $action['url'] ?? null;

            if (!$url) {
                continue;
            }

            if (in_array($name, ['generate-qr-code', 'generate_qr_code'], true)) {
                $result['qr_url'] = $url;
            }

            if (in_array($name, ['deeplink-redirect', 'deeplink_redirect'], true)) {
                $result['deeplink_url'] = $url;
            }
        }

        if (!$result['qr_url'] && !empty($actions[0]['url']) && in_array($paymentMethod, ['qris', 'gopay'], true)) {
            $result['qr_url'] = $actions[0]['url'];
        }

        if (!empty($data['qr_string'])) {
            $result['qr_url'] = 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&data=' . urlencode($data['qr_string']);
        }

        if (!empty($data['va_numbers'][0]['bank'])) {
            $result['bank'] = strtoupper($data['va_numbers'][0]['bank']);
        }

        if (!empty($data['va_numbers'][0]['va_number'])) {
            $result['va_number'] = $data['va_numbers'][0]['va_number'];
        }

        if (!empty($data['permata_va_number'])) {
            $result['bank'] = 'PERMATA';
            $result['va_number'] = $data['permata_va_number'];
        }

        if (!empty($data['payment_code'])) {
            $result['payment_code'] = $data['payment_code'];
            $result['store'] = strtoupper($data['store'] ?? 'CSTORE');
        }

        return $result;
    }
}
