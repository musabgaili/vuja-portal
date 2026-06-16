<?php

namespace App\Engagement\Adapters;

use App\Engagement\Contracts\IdeasBridge;
use App\Models\ImprovementIdea;

class ImprovementIdeasBridge implements IdeasBridge
{
    public function acceptedIdeaCount(int $clientId): int
    {
        return ImprovementIdea::where('user_id', $clientId)
            ->whereIn('status', ['approved', 'implemented'])
            ->count();
    }
}
