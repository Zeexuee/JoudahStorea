<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class FonteNotificationService
{
    private string $baseUrl;
    private ?string $token;
    private ?string $adminPhone;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.fonte.base_url', 'https://api.fonnte.com'), '/');
        $this->token = config('services.fonte.token');
        $this->adminPhone = config('services.fonte.admin_phone');
        $this->enabled = (bool) config('services.fonte.enabled', false);
    }

    public function isReady(): bool
    {
        return $this->enabled && !empty($this->token) && !empty($this->normalizePhone($this->adminPhone));
    }

    public function notifyAdminPaymentCompleted(Payment $payment): array
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Fonte notification is not configured',
            ];
        }

        $order = $payment->order;
        $target = $this->normalizePhone($this->adminPhone);

        if (!$order || !$target) {
            return [
                'success' => false,
                'message' => 'Order or admin phone is missing',
            ];
        }

        $message = $this->buildAdminPaymentMessage($payment);

        $response = Http::withHeaders([
            'Authorization' => (string) $this->token,
        ])->post($this->baseUrl . '/send', [
            'target' => $target,
            'message' => $message,
            'countryCode' => '62',
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'response' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => $response->body(),
            'status' => $response->status(),
        ];
    }

    private function buildAdminPaymentMessage(Payment $payment): string
    {
        $order = $payment->order;

        $lines = [
            'Pembayaran berhasil diterima.',
            '',
            'Order: ' . ($order->order_number ?? '-'),
            'Pelanggan: ' . ($order->shipping_name ?? '-'),
            'Total: Rp' . number_format((int) $payment->amount, 0, ',', '.'),
            'Metode: ' . ($payment->payment_method ?? '-'),
            'Status: ' . $payment->status_label,
            'Waktu: ' . now()->format('d-m-Y H:i:s'),
        ];

        return implode("\n", $lines);
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return '62' . $digits;
    }
}
