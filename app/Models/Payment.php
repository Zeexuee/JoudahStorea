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
