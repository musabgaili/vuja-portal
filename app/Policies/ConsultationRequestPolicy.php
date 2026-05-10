<?php

namespace App\Policies;

use App\Models\ConsultationRequest;
use App\Models\User;

class ConsultationRequestPolicy
{
    public function view(User $user, ConsultationRequest $consultationRequest): bool
    {
        return $user->isInternal() || $consultationRequest->user_id === $user->id;
    }

    public function update(User $user, ConsultationRequest $consultationRequest): bool
    {
        return $user->isInternal() || $consultationRequest->user_id === $user->id;
    }

    public function manage(User $user, ConsultationRequest $consultationRequest): bool
    {
        return $user->isInternal();
    }
}
