<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A conversation: a team channel (type=channel) or a direct/group message (dm).
 */
class ChatChannel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['type', 'name', 'description', 'is_private', 'created_by', 'last_message_at'];

    protected $casts = [
        'is_private' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /** Top-level (non-reply) messages, oldest→newest. */
    public function rootMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->whereNull('parent_id')->orderBy('id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_channel_user')
            ->withPivot(['role', 'last_read_message_id', 'muted', 'joined_at'])
            ->withTimestamps();
    }

    public function isDm(): bool
    {
        return $this->type === 'dm';
    }

    /** Channels the user is a member of, most-recently-active first. */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->whereHas('members', fn ($q) => $q->whereKey($user->id))
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->whereKey($user->id)->exists();
    }

    /** Display name for the viewer: channel name, or the other DM participant(s). */
    public function displayName(?User $viewer = null): string
    {
        if (! $this->isDm()) {
            return $this->name ?: __('portal.chat.untitled_channel');
        }

        $others = $this->members
            ->when($viewer, fn ($c) => $c->reject(fn ($m) => $m->id === $viewer->id))
            ->pluck('name')
            ->filter()
            ->values();

        return $others->isNotEmpty() ? $others->implode(', ') : __('portal.chat.direct_message');
    }
}
