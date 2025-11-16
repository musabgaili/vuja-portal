<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaRequestComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'idea_request_id',
        'user_id',
        'comment',
        'suggested_price',
        'is_internal',
    ];

    protected $casts = [
        'suggested_price' => 'decimal:2',
        'is_internal' => 'boolean',
    ];

    public function ideaRequest(): BelongsTo
    {
        return $this->belongsTo(IdeaRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
