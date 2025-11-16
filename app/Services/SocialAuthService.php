<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthService
{
    /**
     * Handle social login callback.
     */
    public function handleCallback(string $provider): User
    {
        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            return $user;
        }

        // Check if user exists with same email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Update existing user with social provider info
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
            ]);

            return $user;
        }

        // Create new user
        return $this->createUserFromSocial($socialUser, $provider);
    }

    /**
     * Create user from social provider data.
     */
    private function createUserFromSocial($socialUser, string $provider): User
    {
        $user = User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt(Str::random(16)), // Random password for social users
        ]);

        // Assign default role
        $user->assignRole('client');

        return $user;
    }

    /**
     * Get redirect URL for social provider.
     */
    public function getRedirectUrl(string $provider): string
    {
        return Socialite::driver($provider)->redirect()->getTargetUrl();
    }

    /**
     * Get supported providers.
     */
    public function getSupportedProviders(): array
    {
        return ['google', 'facebook', 'linkedin'];
    }
}
