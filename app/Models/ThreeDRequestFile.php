<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreeDRequestFile extends Model
{
    protected $fillable = ['three_d_request_id', 'path', 'original_name', 'mime', 'size'];

    public function threeDRequest(): BelongsTo
    {
        return $this->belongsTo(ThreeDRequest::class);
    }

    /**
     * Files live on the PRIVATE disk and are served only through the authorize()-
     * gated download route, so there is no public URL to expose. Use
     * route('threed.files.download', $file) in views.
     */
    public function getUrlAttribute(): ?string
    {
        return null;
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
