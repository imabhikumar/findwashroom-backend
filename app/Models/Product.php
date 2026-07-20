<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'category_id',
        'name',
        'brand',
        'description',
        'size',
        'price',
        'discount_price',
        'stock_quantity',
        'availability',
        'image_url',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function bookingProducts(): HasMany
    {
        return $this->hasMany(BookingProduct::class);
    }

    public function isInStock(): bool
    {
        return $this->availability !== 'out_of_stock' && ($this->stock_quantity === -1 || $this->stock_quantity > 0);
    }

    public function getFinalPrice(): float
    {
        return $this->discount_price && $this->discount_price < $this->price
            ? $this->discount_price
            : $this->price;
    }
}