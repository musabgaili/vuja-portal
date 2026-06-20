<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A manager-editable AI prompt template for the Scope Planner. One row per
 * template key (generate_system / generate_user / suggest_system). When no row
 * exists or its content is blank, the code falls back to config('scope.prompts.*').
 */
class ScopePromptSetting extends Model
{
    protected $fillable = ['key', 'content', 'updated_by'];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
