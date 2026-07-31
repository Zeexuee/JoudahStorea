<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

class DeliveryReminderService
{
    public function __construct(private readonly FonteNotificationService $fonte)
    {
    }

    /**
     * Send delivery confirmation reminder to user via WA and email.
     */
    public function sendReminder(Order $order, bool $isFollowUp = false): array
    {
        $order->loadMissing('user', 'shipping');

        if (!$order->shipping) {
            return ['success' => false, 'message' => 'Shipping data not found'];
        }

        $confirmationUrl = $isFollowUp
            ? URL::temporarySignedRoute(
                'delivery.confirm',
                now()->addDays(7),
                ['order' => $order->id]
            )
            : null;

        $waResult = $this->fonte->notifyUserDeliveryReminder($order, $confirmationUrl, $isFollowUp);
        $emailResult = $this->sendEmailReminder($order, $confirmationUrl, $isFollowUp);

        $shipping = $order->shipping;
        $shipping->update([
            'reminder_last_sent_at' => now(),
            'reminder_sent_count' => (int) $shipping->reminder_sent_count + 1,
        ]);

        return [
            'success' => ($waResult['success'] ?? false) || ($emailResult['success'] ?? false),
            'wa' => $waResult,
            'email' => $emailResult,
        ];
    }

    private function sendEmailReminder(Order $order, ?string $confirmationUrl, bool $isFollowUp): array
    {
        $email = $order->user?->email;

        if (!$email) {
            return ['success' => false, 'message' => 'User email not found'];
        }

        try {
            $shipping = $order->shipping;

            $subject = $isFollowUp
                ? 'Pengingat Konfirmasi Pesanan - ' . $order->order_number
                : 'Pesanan Anda Sedang Dalam Pengiriman - ' . $order->order_number;

            $bodyLines = [
                'Halo ' . ($order->shipping_name ?: $order->user?->name ?: 'Pelanggan') . ',',
                '',
                $isFollowUp
                    ? 'Ini pengingat untuk konfirmasi pesanan Anda sudah diterima.'
                    : 'Pesanan Anda sedang dalam pengiriman.',
                '',
                'Nomor Pesanan: ' . $order->order_number,
                'Kurir: ' . ($shipping?->courier_name ?? '-'),
                'Resi: ' . ($shipping?->tracking_number ?? '-'),
                'Estimasi Tiba: ' . (($shipping?->estimated_delivery)?->format('d-m-Y') ?? '-'),
                'Terima kasih.',
            ];

            if ($isFollowUp && !empty($confirmationUrl)) {
                $bodyLines = array_merge($bodyLines, [
                    '',
                    'Klik tautan berikut untuk langsung mengonfirmasi barang sudah sampai:',
                    $confirmationUrl,
                ]);
            }

            Mail::raw(implode("\n", $bodyLines), function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
