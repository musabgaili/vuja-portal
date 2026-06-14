<?php

namespace App\Models;

use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasTags;

    protected $fillable = [
        'company_id', 'name', 'job_title', 'email', 'phone', 'is_primary', 'notes', 'owner_id', 'user_id',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
