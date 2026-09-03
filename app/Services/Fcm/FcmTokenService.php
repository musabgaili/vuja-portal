<?php

namespace App\Services\Fcm;

use App\Models\FcmToken;
use App\Models\User;

class FcmTokenService
{
    public function register(User $user, string $token, ?string $device = null, ?string $platform = null): FcmToken
    {
        // A physical device belongs to one logged-in user at a time.
        FcmToken::query()->where('token', $token)->where('user_id', '!=', $user->id)->delete();

        return FcmToken::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $token,
            ],
            [
                'device' => $device,
                'platform' => $platform,
            ],
        );
    }

    public function unregister(User $user, string $token): void
    {
        $user->fcmTokens()->where('token', $token)->delete();
    }

    public function unregisterAll(User $user): void
    {
        $user->fcmTokens()->delete();
    }
}
