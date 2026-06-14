<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PrototypeRequestFile extends Model
{
    protected $fillable = ['prototype_request_id', 'path', 'original_name', 'mime', 'size'];

    public function prototypeRequest(): BelongsTo
    {
        return $this->belongsTo(PrototypeRequest::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }

    /** Human-readable file size. */
    public function getSizeLabelAttribute(): string
    {
        $b = (int) $this->size;
        if ($b < 1024) {
            return $b.' B';
        }
        if ($b < 1048576) {
            return round($b / 1024, 1).' KB';
        }

        return round($b / 1048576, 1).' MB';
    }
}
