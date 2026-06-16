<?php

namespace App\Engagement\Contracts;

use App\Models\User;

/** Binds to the existing User model (clients). Spec §5. */
interface ClientDirectory
{
    public function find(int $clientId): ?User;

    /** Referral anti-abuse: is this email already a registered user? */
    public function emailExists(string $email): bool;
}
