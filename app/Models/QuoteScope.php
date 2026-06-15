<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One scope/work-package of a Company multi-scope quotation. The narrative
 * fields are AI-authored (arrays of bullets / paragraphs); `price` is the
 * deterministic sum of this scope's line items.
 */
class QuoteScope extends Model
{
    protected $fillable = [
        'quote_id', 'sort_order', 'title', 'type',
        'objective', 'inputs_required', 'deliverables', 'acceptance_criteria', 'exclusions', 'price',
    ];

    protected $casts = [
        'objective' => 'array',
        'inputs_required' => 'array',
        'deliverables' => 'array',
        'acceptance_criteria' => 'array',
        'exclusions' => 'array',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_scope_id');
    }
}
