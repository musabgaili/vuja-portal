<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MoyasarClient
{
    public function fetchPayment(string $paymentId): array
    {
        $response = $this->request()->get('/v1/payments/'.$paymentId);

        if (! $response->successful()) {
            throw new RuntimeException('Moyasar payment verification failed with HTTP '.$response->status());
        }

        return $response->json();
    }

    private function request(): PendingRequest
    {
        $secret = (string) config('services.moyasar.secret_key');

        if (! str_starts_with($secret, 'sk_')) {
            throw new RuntimeException('Moyasar secret key is not configured.');
        }

        return Http::baseUrl((string) config('services.moyasar.api_url', 'https://api.moyasar.com'))
            ->withBasicAuth($secret, '')
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 250, throw: false);
    }
}
