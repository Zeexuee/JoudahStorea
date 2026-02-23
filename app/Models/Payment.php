<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

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
            // Check if payment is created with completed status
            if ($payment->status === 'completed') {
                $payment->order->update(['status' => 'processing']);
                logger('Auto-update: Order status changed to processing (payment created as completed)', [
                    'order_id' => $payment->order_id,
                    'payment_id' => $payment->id,
                    'by' => 'payment_model_observer_create'
                ]);

                // NOTE: Auto-submit shipment disabled - Admin akan input resi secara manual
            }
        });

        static::updated(function ($payment) {
            // Auto-update order status when payment is completed
            if ($payment->wasChanged('status') && $payment->status === 'completed') {
                // Refresh order relationship to get fresh data
                $payment->refresh();
                $payment->order()->update(['status' => 'processing']);
                logger('Auto-update: Order status changed to processing (payment completed)', [
                    'order_id' => $payment->order_id,
                    'payment_id' => $payment->id,
                    'by' => 'payment_model_observer_update'
                ]);

                // NOTE: Auto-submit shipment disabled - Admin akan input resi secara manual
            }

            // Auto-cancel order if payment failed
            if ($payment->wasChanged('status') && $payment->status === 'failed') {
                $payment->order->update(['status' => 'cancelled']);
                logger('Auto-update: Order status changed to cancelled (payment failed)', [
                    'order_id' => $payment->order_id,
                    'payment_id' => $payment->id,
                    'by' => 'payment_model_observer'
                ]);
            }

            // Auto-cancel order if payment expired
            if ($payment->wasChanged('status') && $payment->status === 'expired') {
                $payment->order->update(['status' => 'cancelled']);
                logger('Auto-update: Order status changed to cancelled (payment expired)', [
                    'order_id' => $payment->order_id,
                    'payment_id' => $payment->id,
                    'by' => 'payment_model_observer'
                ]);
            }
        });
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
}
