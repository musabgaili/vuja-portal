<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A request by an internal user to join a public team channel. */
class ChatChannelJoinRequest extends Model
{
    protected $fillable = ['chat_channel_id', 'user_id', 'status', 'decided_by', 'decided_at'];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'chat_channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
