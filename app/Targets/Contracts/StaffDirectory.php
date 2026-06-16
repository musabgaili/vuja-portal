<?php

namespace App\Targets\Contracts;

use App\Models\User;

/** Binds to the existing User model. Spec §5. */
interface StaffDirectory
{
    public function find(int $userId): ?User;
}
