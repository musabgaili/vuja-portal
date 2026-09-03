<?php

namespace App\Services\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;

class IssuesApiToken
{
    /** Issue a Sanctum personal-access token for a mobile device. */
    public function issue(User $user, ?string $deviceName = null): array
    {
        $device = trim((string) $deviceName) ?: 'mobile';
        $days = max(1, (int) config('mobile.token_ttl_days', 90));
        $expiresAt = now()->addDays($days);

        $plain = $user->createToken($device, ['*'], $expiresAt)->plainTextToken;

        return [
            'token' => $plain,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => (new UserResource($user))->resolve(),
        ];
    }
}
