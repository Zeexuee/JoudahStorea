<?php

namespace App\Models;

use App\Services\FonteNotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    private const CANCELLED_ORDER_PAYMENT_STATUSES = ['failed', 'expired', 'cancelled', 'refunded'];

    protected $fillable = [
        'order_id',
        'amount',
        'currency',
        'payment_method',
        'payment_gateway',
        'external_id',
        'status',
        'reference_number',
        'transaction_date',
        'paid_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'transaction_date' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method - Auto-update order status based on payment status
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            $payment->syncOrderStatusFromPayment('payment_model_observer_create');
        });

        static::updated(function ($payment) {
            if ($payment->wasChanged('status')) {
                $payment->syncOrderStatusFromPayment('payment_model_observer_update');
            }
        });
    }

    /**
     * Keep order status synchronized with payment status.
     */
    private function syncOrderStatusFromPayment(string $source): void
    {
        $order = $this->order;

        if (!$order) {
            return;
        }

        if ($this->status === 'completed') {
            if ($order->status !== 'processing') {
                $order->update(['status' => 'processing']);
            }

            $this->notifyAdminViaFonte();

            logger('Auto-update: Order status changed to processing (payment completed)', [
                'order_id' => $this->order_id,
                'payment_id' => $this->id,
                'payment_status' => $this->status,
                'by' => $source,
            ]);

            return;
        }

        if (in_array($this->status, self::CANCELLED_ORDER_PAYMENT_STATUSES, true)) {
            if ($order->status !== 'cancelled') {
                $order->update(['status' => 'cancelled']);
            }

            logger('Auto-update: Order status changed to cancelled (payment terminal state)', [
                'order_id' => $this->order_id,
                'payment_id' => $this->id,
                'payment_status' => $this->status,
                'by' => $source,
            ]);
        }
    }

    /**
     * Get the order that owns the payment
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get payment status label in Indonesian
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Sedang Diproses',
            'completed' => 'Lunas',
            'failed' => 'Gagal',
            'expired' => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan',
            default => 'Unknown'
        };
    }

    /**
     * Get payment status color for UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            'expired' => 'red',
            'cancelled' => 'red',
            'refunded' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Check if payment is paid
     */
    public function isPaid()
    {
        return $this->status === 'completed' && $this->paid_at !== null;
    }

    /**
     * Send payment success notification to admin via Fonte.
     */
    private function notifyAdminViaFonte(): void
    {
        if ($this->wasAdminFonteNotified()) {
            return;
        }

        try {
            $service = app(FonteNotificationService::class);
            $result = $service->notifyAdminPaymentCompleted($this);

            if (($result['success'] ?? false) === true) {
                $metadata = $this->metadata ?? [];
                $metadata['admin_fonte_notified_at'] = now()->toIso8601String();
                $this->update(['metadata' => $metadata]);
                return;
            }

            logger('Fonte notification failed', [
                'payment_id' => $this->id,
                'order_id' => $this->order_id,
                'result' => $result,
            ]);
        } catch (\Throwable $exception) {
            logger('Fonte notification error', [
                'payment_id' => $this->id,
                'order_id' => $this->order_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function wasAdminFonteNotified(): bool
    {
        return !empty($this->metadata['admin_fonte_notified_at'] ?? null);
    }
}
