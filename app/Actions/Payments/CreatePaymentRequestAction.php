<?php

namespace App\Actions\Payments;

use App\Models\PaymentRequest;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePaymentRequestAction
{
    public function __construct(private RecordPaymentRequestEventAction $recordEvent) {}

    public function execute(array $data, User $creator): PaymentRequest
    {
        return DB::transaction(function () use ($data, $creator) {
            $email = mb_strtolower(trim($data['email']));
            $client = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->where('role', 'client')
                ->first();

            $unitAmountMinor = $this->toMinorUnits((string) $data['amount']);
            $quantity = (int) $data['quantity'];
            $quote = ! empty($data['quote_id']) ? Quote::query()->findOrFail($data['quote_id']) : null;

            $paymentRequest = PaymentRequest::create([
                'client_id' => $client?->id,
                'created_by' => $creator->id,
                'payable_type' => $quote ? $quote->getMorphClass() : null,
                'payable_id' => $quote?->id,
                'name' => trim($data['name']),
                'email' => $email,
                'phone' => $data['phone'] ?? null,
                'title' => trim($data['title']),
                'description' => $data['description'] ?? null,
                'quantity' => $quantity,
                'unit_amount_minor' => $unitAmountMinor,
                'total_amount_minor' => $unitAmountMinor * $quantity,
                'currency' => config('services.moyasar.currency', 'SAR'),
                'status' => 'pending',
                'expires_at' => now()->addHours((int) config('services.moyasar.link_ttl_hours', 48)),
            ]);

            $this->recordEvent->execute(
                $paymentRequest,
                'created',
                payload: [
                    'created_by' => $creator->id,
                    'quote_id' => $quote?->id,
                    'quote_number' => $quote?->quote_number,
                ],
                outcome: 'created',
            );

            return $paymentRequest->fresh(['client', 'creator', 'payable']);
        });
    }

    private function toMinorUnits(string $amount): int
    {
        [$whole, $decimal] = array_pad(explode('.', trim($amount), 2), 2, '0');
        $decimal = substr(str_pad($decimal, 2, '0'), 0, 2);

        return ((int) $whole * 100) + (int) $decimal;
    }
}
