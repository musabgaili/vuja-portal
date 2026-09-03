<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\Auth\SocialIdentity;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Resolve a verified social identity to a local user.
 *
 * Team-first policy: do NOT auto-create accounts (managers invite staff).
 * Set config('mobile.social_auto_register') when client self-serve is ready.
 */
class SocialTokenAuthService
{
    public function authenticate(SocialIdentity $identity): User
    {
        $user = User::query()
            ->where('provider', $identity->provider)
            ->where('provider_id', $identity->providerId)
            ->first();

        if ($user) {
            $this->assertActive($user);

            return $user;
        }

        if ($identity->email) {
            $user = User::query()->where('email', $identity->email)->first();

            if ($user) {
                if (! $identity->emailVerified || $user->email_verified_at === null) {
                    throw ValidationException::withMessages([
                        'token' => ['Cannot link this social account to an existing account automatically.'],
                    ]);
                }

                $this->assertActive($user);

                $user->update([
                    'provider' => $identity->provider,
                    'provider_id' => $identity->providerId,
                ]);

                return $user->fresh();
            }
        }

        if (! config('mobile.social_auto_register')) {
            throw ValidationException::withMessages([
                'token' => ['No account found for this login. Ask a manager to invite you.'],
            ]);
        }

        return $this->createClient($identity);
    }

    private function assertActive(User $user): void
    {
        if (in_array($user->status, [UserStatus::SUSPENDED, UserStatus::INACTIVE], true)) {
            throw ValidationException::withMessages([
                'token' => ['This account is not active.'],
            ]);
        }
    }

    private function createClient(SocialIdentity $identity): User
    {
        if (! $identity->email || ! $identity->emailVerified) {
            throw ValidationException::withMessages([
                'token' => ['A verified email is required to create an account.'],
            ]);
        }

        $user = User::create([
            'name' => $identity->name ?: Str::before($identity->email, '@'),
            'email' => $identity->email,
            'provider' => $identity->provider,
            'provider_id' => $identity->providerId,
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
            'password' => Str::password(32),
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('client');
        }

        return $user;
    }
}
