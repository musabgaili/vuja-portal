<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payment-schedule milestone (M1/M2/M3). `amount` is computed by PricingService
 * from grand_total × percentage; the last milestone absorbs the rounding remainder.
 */
class QuoteMilestone extends Model
{
    protected $fillable = [
        'quote_id', 'code', 'trigger', 'percentage', 'amount', 'sort_order',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
