<?php

namespace App\Services\Auth;

use App\Contracts\SocialTokenVerifier;
use App\Support\Auth\SocialIdentity;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

/**
 * Verifies a Sign in with Apple identity token (JWT) against Apple's JWKS.
 * Native Flutter sign_in_with_apple sends this token; no client secret needed.
 */
class AppleTokenVerifier implements SocialTokenVerifier
{
    public function verify(string $token, ?string $nonce = null): SocialIdentity
    {
        try {
            $jwks = Http::timeout(8)
                ->acceptJson()
                ->get('https://appleid.apple.com/auth/keys')
                ->throw()
                ->json();
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'identity_token' => ['Could not reach Apple to verify the sign-in token.'],
            ]);
        }

        try {
            JWT::$leeway = 60;
            $decoded = JWT::decode($token, JWK::parseKeySet($jwks));
        } catch (UnexpectedValueException $e) {
            throw ValidationException::withMessages([
                'identity_token' => ['Apple sign-in token is invalid or expired.'],
            ]);
        }

        if (($decoded->iss ?? null) !== 'https://appleid.apple.com') {
            throw ValidationException::withMessages([
                'identity_token' => ['Apple sign-in token issuer is invalid.'],
            ]);
        }

        $audience = (string) ($decoded->aud ?? '');
        $allowed = $this->allowedAudiences();
        if ($allowed === [] || ! in_array($audience, $allowed, true)) {
            throw ValidationException::withMessages([
                'identity_token' => ['Apple sign-in token audience is not allowed.'],
            ]);
        }

        if ($nonce !== null && $nonce !== '' && (string) ($decoded->nonce ?? '') !== $nonce) {
            throw ValidationException::withMessages([
                'identity_token' => ['Apple sign-in nonce mismatch.'],
            ]);
        }

        $sub = (string) ($decoded->sub ?? '');
        if ($sub === '') {
            throw ValidationException::withMessages([
                'identity_token' => ['Apple sign-in token is missing a subject.'],
            ]);
        }

        $email = isset($decoded->email) ? strtolower(trim((string) $decoded->email)) : null;
        $verified = filter_var($decoded->email_verified ?? false, FILTER_VALIDATE_BOOLEAN);

        return new SocialIdentity(
            provider: 'apple',
            providerId: $sub,
            email: $email !== '' ? $email : null,
            name: null,
            emailVerified: $verified || $email !== null,
        );
    }

    /** @return list<string> */
    private function allowedAudiences(): array
    {
        $ids = config('services.apple.client_ids', []);
        if (! is_array($ids) || $ids === []) {
            $single = config('services.apple.client_id');
            $ids = $single ? [$single] : [];
        }

        return array_values(array_filter(array_map('strval', $ids)));
    }
}
