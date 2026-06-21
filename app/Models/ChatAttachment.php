<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatAttachment extends Model
{
    protected $fillable = ['chat_message_id', 'disk', 'path', 'original_name', 'mime', 'size'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    /** Membership-gated download route (files live on the private disk). */
    public function url(): string
    {
        return route('chat.attachments.show', $this);
    }

    public function isImage(): bool
    {
        return Str::startsWith((string) $this->mime, 'image/');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        $units = ['KB', 'MB', 'GB'];
        $i = -1;
        do {
            $bytes /= 1024;
            $i++;
        } while ($bytes >= 1024 && $i < count($units) - 1);

        return round($bytes, 1).' '.$units[$i];
    }
}
