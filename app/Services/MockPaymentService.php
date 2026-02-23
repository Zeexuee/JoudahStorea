<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Mock Payment Service for Testing
 * Simulates payment gateway without actual API calls
 */
class MockPaymentService
{
    private $debugMode = true;

    public function __construct()
    {
        $this->debugMode = config('payment.mock_debug_mode', true);
    }

    /**
     * Create payment request (Mock)
     * @param Payment $payment
     * @param array $itemDetails
     * @return array
     */
    public function createPayment(Payment $payment, $itemDetails = [])
    {
        $externalId = $this->generateExternalId($payment->order_id);
        $amount = (int)$payment->amount;

        // Mock checkout URL - user can test different scenarios by amount
        $checkoutUrl = $this->generateMockCheckoutUrl($payment, $externalId);

        // Save external ID to payment record
        $payment->update([
            'external_id' => $externalId,
            'payment_gateway' => 'mock',
            'metadata' => [
                'checkout_url' => $checkoutUrl,
                'invoice_id' => $externalId,
                'gateway' => 'mock',
                'test_mode' => true,
                'created_at_mock' => now()->toIso8601String(),
            ]
        ]);

        if ($this->debugMode) {
            logger('Mock Payment Created', [
                'order_id' => $payment->order_id,
                'external_id' => $externalId,
                'amount' => $amount,
                'checkout_url' => $checkoutUrl
            ]);
        }

        return [
            'success' => true,
            'checkout_url' => $checkoutUrl,
            'external_id' => $externalId,
            'payment_gateway' => 'mock',
            'mode' => 'test'
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

        // Simulate payment verification
        // In mock mode, we check the test_payment_status session/cookie
        $testStatus = session('test_payment_status', 'pending');
        
        if ($testStatus === 'completed' || $testStatus === 'approved') {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'verified_at_mock' => now()->toIso8601String(),
                    'verification_mode' => 'mock_test',
                ])
            ]);

            // Update order status
            $payment->order->update(['status' => 'processing']);

            if ($this->debugMode) {
                logger('Mock Payment Verified', [
                    'order_id' => $payment->order_id,
                    'external_id' => $payment->external_id,
                    'status' => 'completed'
                ]);
            }

            return [
                'success' => true,
                'status' => 'completed',
                'data' => [
                    'status' => 'COMPLETED',
                    'amount' => $payment->amount,
                    'invoice_id' => $payment->external_id,
                ]
            ];
        }

        return [
            'success' => false,
            'error' => 'Payment is still pending. In mock mode, visit the checkout URL to test payment simulation.',
            'current_status' => $testStatus
        ];
    }

    /**
     * Process webhook callback (Mock)
     * @param array $payload
     * @return bool
     */
    public function processCallback($payload)
    {
        $invoiceId = $payload['invoice_id'] ?? $payload['external_id'] ?? null;

        if (!$invoiceId) {
            return false;
        }

        // Find payment by external ID
        $payment = Payment::where('external_id', $invoiceId)->first();

        if (!$payment) {
            return false;
        }

        // Update payment status
        $status = $payload['status'] ?? 'completed';
        $payment->update([
            'status' => $status === 'COMPLETED' ? 'completed' : 'pending',
            'metadata' => array_merge($payment->metadata ?? [], [
                'callback_received' => now()->toIso8601String(),
                'callback_payload' => $payload,
            ])
        ]);

        if ($status === 'COMPLETED' || $status === 'completed') {
            $payment->update(['paid_at' => now()]);
            $payment->order->update(['status' => 'processing']);
        }

        if ($this->debugMode) {
            logger('Mock Payment Callback', [
                'order_id' => $payment->order_id,
                'status' => $status
            ]);
        }

        return true;
    }

    /**
     * Cancel payment (Mock)
     * @param Payment $payment
     * @return array
     */
    public function cancelPayment(Payment $payment)
    {
        $payment->update([
            'status' => 'cancelled',
            'metadata' => array_merge($payment->metadata ?? [], [
                'cancelled_at' => now()->toIso8601String(),
            ])
        ]);

        return [
            'success' => true,
            'message' => 'Payment cancelled successfully'
        ];
    }

    /**
     * Get payment testing instructions
     * @return array
     */
    public function getTestingInstructions()
    {
        return [
            'mode' => 'mock',
            'description' => 'Mock Payment Gateway for Testing',
            'features' => [
                'No real charges',
                'Instant payment simulation',
                'Testing without credentials',
                'Easy status switching',
            ],
            'how_to_test' => [
                '1. Start checkout process',
                '2. Proceed to payment page',
                '3. Click "Proses Pembayaran" - goes to mock checkout',
                '4. In mock checkout, select test status:',
                '   - "APPROVE" to simulate successful payment',
                '   - "CANCEL" to simulate failed payment',
                '5. Return to app and verify payment status',
            ],
            'test_amounts' => [
                'Any amount will work - payment is instant',
                'Recommended: Test with different amounts',
            ],
            'notes' => [
                'Status persists in session during browser session',
                'Clear session to reset payment status',
                'All transactions logged in app logs',
            ]
        ];
    }

    /**
     * Generate mock checkout URL
     */
    private function generateMockCheckoutUrl(Payment $payment, $externalId)
    {
        return route('payment.mock-checkout', ['external_id' => $externalId, 'amount' => $payment->amount]);
    }

    /**
     * Generate external ID
     */
    private function generateExternalId($orderId)
    {
        return 'MOCK-' . $orderId . '-' . Str::random(8);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(Payment $payment)
    {
        $status = session('test_payment_status_' . $payment->external_id, $payment->status);
        
        return [
            'success' => true,
            'status' => $status,
            'payment_gateway' => 'mock',
            'test_mode' => true
        ];
    }
}
