<?php

namespace App\Models\Concerns;

use App\Models\CrmActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCrmActivities
{
    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'subject')->latest('created_at');
    }

    public function plannedActivities()
    {
        return $this->activities()->where('status', 'planned')->orderBy('due_at');
    }
}
