<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of a {@see SpendRequest} — a single product/expense with its own
 * quantity, unit cost and (for purchases) buy link.
 */
class SpendRequestItem extends Model
{
    protected $fillable = [
        'spend_request_id',
        'description',
        'quantity',
        'unit_amount',
        'product_url',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function spendRequest(): BelongsTo
    {
        return $this->belongsTo(SpendRequest::class);
    }

    /** Line total = quantity × unit amount. */
    public function lineTotal(): float
    {
        return round((int) $this->quantity * (float) $this->unit_amount, 2);
    }
}
