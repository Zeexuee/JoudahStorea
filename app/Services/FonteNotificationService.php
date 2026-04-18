<?php

namespace App\Services;

use App\Models\Order;
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
        return $this->isTokenReady() && !empty($this->normalizePhone($this->adminPhone));
    }

    public function isTokenReady(): bool
    {
        return $this->enabled && !empty($this->token);
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

        return $this->sendMessageToPhone($target, $message);
    }

    public function notifyUserDeliveryReminder(Order $order, ?string $confirmationUrl, bool $isFollowUp = false): array
    {
        if (!$this->isTokenReady()) {
            return [
                'success' => false,
                'message' => 'Fonte notification is not configured',
            ];
        }

        $target = $this->normalizePhone($order->shipping_phone ?: $order->user?->phone);

        if (!$target) {
            return [
                'success' => false,
                'message' => 'User phone is missing',
            ];
        }

        $shipping = $order->shipping;
        $messageLines = [
            $isFollowUp ? 'Pengingat konfirmasi pesanan.' : 'Pesanan Anda sedang dalam pengiriman.',
            '',
            'Order: ' . ($order->order_number ?? '-'),
            'Kurir: ' . ($shipping?->courier_name ?? '-'),
            'Resi: ' . ($shipping?->tracking_number ?? '-'),
            'Estimasi tiba: ' . (($shipping?->estimated_delivery)?->format('d-m-Y') ?? '-'),
        ];

        if ($isFollowUp && !empty($confirmationUrl)) {
            $messageLines = array_merge($messageLines, [
                '',
                'Klik tautan berikut untuk langsung mengonfirmasi barang sudah sampai:',
                $confirmationUrl,
            ]);
        }

        $message = implode("\n", $messageLines);

        return $this->sendMessageToPhone($target, $message);
    }

    public function sendOtpToPhone(string $phone, string $otpCode): array
    {
        if (!$this->isTokenReady()) {
            return [
                'success' => false,
                'message' => 'Fonte notification is not configured',
            ];
        }

        $target = $this->normalizePhone($phone);

        if (!$target) {
            return [
                'success' => false,
                'message' => 'User phone is missing',
            ];
        }

        $message = implode("\n", [
            'Kode OTP verifikasi akun Anda:',
            $otpCode,
            '',
            'Kode berlaku selama 10 menit.',
            'Jangan bagikan kode ini ke siapa pun.',
        ]);

        return $this->sendMessageToPhone($target, $message);
    }

    private function sendMessageToPhone(string $target, string $message): array
    {

        $response = Http::withHeaders([
            'Authorization' => (string) $this->token,
        ])->post($this->baseUrl . '/send', [
            'target' => $target,
            'message' => $message,
            'countryCode' => '62',
        ]);

        $responseData = $response->json() ?? [];
        $fonteStatus = $responseData['status'] ?? null;

        if ($response->successful() && $fonteStatus === true) {
            return [
                'success' => true,
                'response' => $responseData,
            ];
        }

        return [
            'success' => false,
            'message' => $responseData['reason'] ?? $response->body(),
            'status' => $response->status(),
            'response' => $responseData,
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
