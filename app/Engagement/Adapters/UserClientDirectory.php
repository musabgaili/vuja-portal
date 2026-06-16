<?php

namespace App\Engagement\Adapters;

use App\Engagement\Contracts\ClientDirectory;
use App\Models\User;

class UserClientDirectory implements ClientDirectory
{
    public function find(int $clientId): ?User
    {
        return User::find($clientId);
    }

    public function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }
}
