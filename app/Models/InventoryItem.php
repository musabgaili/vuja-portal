<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'name', 'category', 'internal_cost', 'markup_percentage', 'unit', 'sku', 'active',
    ];

    protected $casts = [
        'internal_cost' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'active' => 'boolean',
    ];

    /** Price charged to the client (cost + markup). Item-level cost stays internal. */
    public function clientPrice(): float
    {
        return round((float) $this->internal_cost * (1 + (float) $this->markup_percentage / 100), 2);
    }
}
