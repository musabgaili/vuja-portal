<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A receipt/attachment on a {@see SpendRequest}. kind: receipt (proof at request
 * time), actual_receipt (proof at purchase fulfilment), or attachment (quote etc.).
 */
class SpendRequestFile extends Model
{
    protected $fillable = [
        'spend_request_id',
        'kind',
        'path',
        'original_name',
        'uploaded_by',
    ];

    public function spendRequest(): BelongsTo
    {
        return $this->belongsTo(SpendRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function displayName(): string
    {
        return $this->original_name ?: basename($this->path);
    }
}
