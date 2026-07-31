<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\DeliveryReminderService;
use Illuminate\Console\Command;

class SendDeliveryConfirmationReminders extends Command
{
    protected $signature = 'orders:send-delivery-reminders {--limit=50 : Maximum orders to process}';
    protected $description = 'Send delivery confirmation reminders for shipped orders nearing/past ETA';

    public function handle(DeliveryReminderService $reminderService): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $orders = Order::query()
            ->with(['user', 'shipping'])
            ->where('status', 'shipped')
            ->whereHas('shipping', function ($query) {
                $query->whereNotNull('tracking_number')
                    ->where(function ($etaQuery) {
                        $etaQuery->whereNull('estimated_delivery')
                            ->orWhere('estimated_delivery', '<=', now());
                    })
                    ->where(function ($reminderQuery) {
                        $reminderQuery->whereNull('reminder_last_sent_at')
                            ->orWhere('reminder_last_sent_at', '<=', now()->subDay());
                    });
            })
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No eligible orders for reminder.');
            return self::SUCCESS;
        }

        $successCount = 0;

        foreach ($orders as $order) {
            $result = $reminderService->sendReminder($order, true);

            if (!empty($result['success'])) {
                $successCount++;
                $this->info("Reminder sent for order {$order->order_number}");
            } else {
                $message = $result['wa']['message'] ?? $result['email']['message'] ?? 'unknown';
                $this->warn("Reminder failed for order {$order->order_number}: {$message}");
            }
        }

        $this->info("Done. Success: {$successCount}/{$orders->count()}");

        return self::SUCCESS;
    }
}
