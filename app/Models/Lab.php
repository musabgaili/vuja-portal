<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lab extends Model
{
    protected $fillable = ['name', 'slug'];

    public function stockItems(): BelongsToMany
    {
        return $this->belongsToMany(StockItem::class, 'lab_stock_item')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
