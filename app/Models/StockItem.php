<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * An inventory product / SKU. Stock is tracked per lab on the lab_stock_item
 * pivot; three selling prices map to the customer categories student /
 * entrepreneur / company.
 */
class StockItem extends Model
{
    protected $fillable = [
        'product_id', 'name', 'description', 'image_path',
        'purchase_price', 'price_student', 'price_entrepreneur', 'price_company',
        'unit', 'category', 'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'price_student' => 'decimal:2',
        'price_entrepreneur' => 'decimal:2',
        'price_company' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function labs(): BelongsToMany
    {
        return $this->belongsToMany(Lab::class, 'lab_stock_item')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /** Total quantity across every lab. Eager-load labs to avoid N+1. */
    public function getTotalStockAttribute(): int
    {
        return (int) $this->labs->sum(fn ($lab) => $lab->pivot->quantity);
    }

    /** Public URL of the product image, or null. */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    /**
     * Selling price for a customer category: 'student', 'entrepreneur' or
     * 'company'. The integration seam for quotes/orders elsewhere in the CRM.
     */
    public function priceFor(string $customerType): float
    {
        return (float) match ($customerType) {
            'student' => $this->price_student,
            'entrepreneur' => $this->price_entrepreneur,
            'company' => $this->price_company,
            default => $this->price_company,
        };
    }
}
