<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id', 'inventory_item_id', 'stock_item_id', 'pricing_rule_id', 'quote_scope_id',
        'name', 'category', 'type', 'source', 'unit',
        'internal_cost', 'markup_percentage', 'qty', 'unit_price', 'line_internal', 'line_client',
        'is_client_visible', 'sort_order',
    ];

    protected $casts = [
        'internal_cost' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'line_internal' => 'decimal:2',
        'line_client' => 'decimal:2',
        'is_client_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(QuoteScope::class, 'quote_scope_id');
    }

    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
