<?php

namespace App\Targets\Adapters;

use App\Models\User;
use App\Targets\Contracts\StaffDirectory;

class UserStaffDirectory implements StaffDirectory
{
    public function find(int $userId): ?User
    {
        return User::find($userId);
    }
}
