<?php

namespace App\Services\Fcm;

use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends FCM push notifications to registered device tokens.
 * Configure FIREBASE_PROJECT_ID + FIREBASE_SERVICE_ACCOUNT_JSON (path or inline JSON).
 */
class FcmPushService
{
    public function pushToUser(User $user, string $title, string $body, ?string $deepLink = null, array $data = []): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $tokens = $user->fcmTokens()->pluck('token')->all();
        if ($tokens === []) {
            return;
        }

        $payload = array_merge($data, array_filter([
            'deep_link' => $deepLink,
        ]));

        foreach ($tokens as $token) {
            $this->sendToToken($token, $title, $body, $payload);
        }
    }

    public function isConfigured(): bool
    {
        return filled(config('fcm.project_id'))
            && filled(config('fcm.service_account'));
    }

    /** @param  array<string, string>  $data */
    private function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $accessToken = $this->accessToken();
            $projectId = config('fcm.project_id');

            Http::withToken($accessToken)
                ->timeout(10)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_map('strval', $data),
                        'android' => ['priority' => 'HIGH'],
                        'apns' => ['headers' => ['apns-priority' => '10']],
                    ],
                ])
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('FCM push failed: '.$e->getMessage());
            if (str_contains($e->getMessage(), 'NOT_FOUND') || str_contains($e->getMessage(), 'UNREGISTERED')) {
                FcmToken::query()->where('token', $token)->delete();
            }
        }
    }

    private function accessToken(): string
    {
        $cacheKey = 'fcm_access_token';
        $cached = cache()->get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $sa = $this->serviceAccount();
        $jwt = $this->buildJwt($sa);
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ])->throw()->json();

        $token = (string) ($response['access_token'] ?? '');
        $ttl = max(60, (int) ($response['expires_in'] ?? 3600) - 60);
        cache()->put($cacheKey, $token, $ttl);

        return $token;
    }

    /** @return array<string, mixed> */
    private function serviceAccount(): array
    {
        $raw = config('fcm.service_account');
        if (is_string($raw) && str_starts_with(trim($raw), '{')) {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        }
        if (is_string($raw) && is_file($raw)) {
            return json_decode(file_get_contents($raw), true, 512, JSON_THROW_ON_ERROR);
        }

        throw new \RuntimeException('FCM service account is not configured.');
    }

    /** @param  array<string, mixed>  $sa */
    private function buildJwt(array $sa): string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64Url(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64Url(json_encode($claims, JSON_THROW_ON_ERROR)),
        ];
        $input = implode('.', $segments);

        $key = openssl_pkey_get_private($sa['private_key']);
        openssl_sign($input, $signature, $key, OPENSSL_ALGO_SHA256);

        return $input.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
