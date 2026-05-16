<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'is_featured' => 'boolean',
        'discount_percent' => 'integer',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
    ];

    protected $appends = ['main_image'];

    protected $fillable = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the main/first image from the images array
     */
    public function getMainImageAttribute()
    {
        if (is_array($this->images) && count($this->images) > 0) {
            return $this->images[0];
        }
        return null;
    }

    public function getPriceAfterDiscountAttribute()
    {
        if (!$this->isDiscountActive()) {
            return $this->price;
        }

        $discount = intval($this->discount_percent);
        $reduced = $this->price - intval(round($this->price * ($discount / 100)));
        return max(0, $reduced);
    }

    public function getHasDiscountAttribute()
    {
        return $this->isDiscountActive();
    }

    public function getDiscountIsActiveAttribute()
    {
        return $this->isDiscountActive();
    }

    protected function isDiscountActive(): bool
    {
        if (!is_null($this->discount_percent) && $this->discount_percent > 0) {
            $now = now();

            if ($this->discount_starts_at && $now->lt($this->discount_starts_at)) {
                return false;
            }

            if ($this->discount_ends_at && $now->gt($this->discount_ends_at)) {
                return false;
            }

            return true;
        }

        return false;
    }
}
