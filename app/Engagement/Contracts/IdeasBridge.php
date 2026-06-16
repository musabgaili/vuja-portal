<?php

namespace App\Engagement\Contracts;

/** Binds to the existing ImprovementIdea feature. Spec §5. */
interface IdeasBridge
{
    /** Count of the client's accepted/implemented ideas (drives the first-5-auto rule). */
    public function acceptedIdeaCount(int $clientId): int;
}
