<?php

namespace App\Services\Auth;

use App\Contracts\SocialTokenVerifier;
use App\Support\Auth\SocialIdentity;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Verifies a Google ID token from the Flutter google_sign_in plugin
 * via Google's tokeninfo endpoint (aud must match a configured client id).
 */
class GoogleTokenVerifier implements SocialTokenVerifier
{
    public function verify(string $token, ?string $nonce = null): SocialIdentity
    {
        $response = Http::timeout(8)
            ->acceptJson()
            ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $token]);

        if (! $response->ok()) {
            throw ValidationException::withMessages([
                'id_token' => ['Google sign-in token is invalid or expired.'],
            ]);
        }

        $payload = $response->json() ?? [];
        $audience = (string) ($payload['aud'] ?? '');
        $allowed = $this->allowedAudiences();

        if ($allowed === [] || ! in_array($audience, $allowed, true)) {
            throw ValidationException::withMessages([
                'id_token' => ['Google sign-in token audience is not allowed.'],
            ]);
        }

        $email = isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : null;
        $verified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $sub = (string) ($payload['sub'] ?? '');
        if ($sub === '') {
            throw ValidationException::withMessages([
                'id_token' => ['Google sign-in token is missing a subject.'],
            ]);
        }

        return new SocialIdentity(
            provider: 'google',
            providerId: $sub,
            email: $email !== '' ? $email : null,
            name: isset($payload['name']) ? (string) $payload['name'] : null,
            emailVerified: $verified,
        );
    }

    /** @return list<string> */
    private function allowedAudiences(): array
    {
        $ids = config('services.google.client_ids', []);
        if (! is_array($ids) || $ids === []) {
            $single = config('services.google.client_id');
            $ids = $single ? [$single] : [];
        }

        return array_values(array_filter(array_map('strval', $ids)));
    }
}
