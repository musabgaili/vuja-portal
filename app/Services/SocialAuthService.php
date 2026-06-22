<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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
            $this->assertActive($user);

            return $user;
        }

        // Link to an existing local account by email ONLY when the provider
        // asserts the email is verified AND the local account is already
        // email-verified — otherwise this is an account-takeover vector (a provider
        // returning an attacker-controlled, unverified email could hijack the match).
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            $providerVerified = (bool) data_get((array) ($socialUser->user ?? []), 'email_verified', false);
            if (! $providerVerified || $user->email_verified_at === null) {
                throw new \RuntimeException('Cannot link this social account to an existing account automatically.');
            }
            $this->assertActive($user);

            // Do NOT change status here (never silently un-suspend).
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);

            return $user;
        }

        // Create new user
        return $this->createUserFromSocial($socialUser, $provider);
    }

    /** Reject suspended/inactive accounts on the social path too. */
    private function assertActive(User $user): void
    {
        if ($user->status !== UserStatus::ACTIVE) {
            throw new \RuntimeException('This account is not active.');
        }
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
