<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier',
        'courier_name',
        'service',
        'service_description',
        'cost',
        'weight',
        'origin_city_id',
        'destination_city_id',
        'tracking_number',
        'status',
        'estimated_delivery',
        'actual_delivery',
        'reminder_last_sent_at',
        'reminder_sent_count',
        'notes',
    ];

    protected $casts = [
        'cost' => 'integer',
        'weight' => 'integer',
        'estimated_delivery' => 'datetime',
        'actual_delivery' => 'datetime',
        'reminder_last_sent_at' => 'datetime',
        'reminder_sent_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method - Auto-update order status based on shipping status
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($shipping) {
            $shipping->syncOrderStatusFromShipping('shipping_model_observer_create');
        });

        static::updated(function ($shipping) {
            if ($shipping->wasChanged('status')) {
                $shipping->syncOrderStatusFromShipping('shipping_model_observer_update');
            }
        });
    }

    /**
     * Keep order status synchronized with shipping status.
     */
    private function syncOrderStatusFromShipping(string $source): void
    {
        $order = $this->order;

        if (!$order) {
            return;
        }

        $orderStatus = match($this->status) {
            'picked_up' => 'processing',
            'in_transit', 'out_for_delivery' => 'shipped',
            'delivered' => 'delivered',
            'failed', 'returned' => 'cancelled',
            default => $order->status,
        };

        if ($orderStatus === $order->status) {
            return;
        }

        $order->update(['status' => $orderStatus]);

        logger('Auto-update: Order status changed from shipping state', [
            'order_id' => $this->order_id,
            'shipping_id' => $this->id,
            'shipping_status' => $this->status,
            'order_status' => $orderStatus,
            'by' => $source,
        ]);
    }

    /**
     * Get the order that owns the shipping
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu Pickup',
            'picked_up' => 'Sudah Diambil',
            'in_transit' => 'Sedang Dalam Perjalanan',
            'out_for_delivery' => 'Sedang di Anter',
            'delivered' => 'Terima',
            'failed' => 'Gagal Dikirim',
            'returned' => 'Dikembalikan',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'picked_up' => 'blue',
            'in_transit' => 'blue',
            'out_for_delivery' => 'purple',
            'delivered' => 'green',
            'failed' => 'red',
            'returned' => 'red',
            default => 'gray'
        };
    }
}
