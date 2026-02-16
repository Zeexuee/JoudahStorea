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
        'notes',
    ];

    protected $casts = [
        'cost' => 'integer',
        'weight' => 'integer',
        'estimated_delivery' => 'datetime',
        'actual_delivery' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
