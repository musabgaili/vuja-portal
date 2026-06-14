<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id', 'inventory_item_id', 'stock_item_id', 'name', 'category',
        'internal_cost', 'markup_percentage', 'qty', 'line_internal', 'line_client',
    ];

    protected $casts = [
        'internal_cost' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'qty' => 'integer',
        'line_internal' => 'decimal:2',
        'line_client' => 'decimal:2',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
