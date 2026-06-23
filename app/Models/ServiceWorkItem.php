<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ServiceWorkItem extends Model
{
    protected $fillable = [
        'noteable_type', 'noteable_id',
        'user_id', 'type',
        'content', 'file_path', 'file_name', 'file_size',
        'is_client_visible',
    ];

    protected $casts = [
        'is_client_visible' => 'boolean',
        'file_size' => 'integer',
    ];

    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSizeLabelAttribute(): string
    {
        if (! $this->file_size) {
            return '';
        }
        $kb = $this->file_size / 1024;

        return $kb > 1024 ? round($kb / 1024, 1).' MB' : round($kb, 0).' KB';
    }
}
