<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEntry extends Model
{
    protected $fillable = ['team_member_id', 'date', 'hours', 'type'];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }
}
