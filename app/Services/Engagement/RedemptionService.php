<?php

namespace App\Services\Engagement;

use App\Engagement\Contracts\QuoteDiscountBridge;
use App\Models\PointsAccount;
use App\Models\Quote;
use App\Models\Redemption;
use App\Models\RedemptionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Redemption catalog (spec §9): service discount (applied to a quote's service
 * lines only), AI idea-engine access (entitlement), and free consultation
 * (voucher). Spends points via the ledger, then issues the reward by type.
 */
class RedemptionService
{
    public function __construct(
        private PointsService $points,
        private QuoteDiscountBridge $discounts,
    ) {}

    public function redeem(PointsAccount $account, RedemptionOption $option): Redemption
    {
        if (! $option->is_active) {
            throw new \RuntimeException('This reward is not available.');
        }
        if ($account->balance < $option->cost_points) {
            throw new \RuntimeException('Not enough points to redeem this reward.');
        }

        return DB::transaction(function () use ($account, $option) {
            $source = match ($option->type) {
                'service_discount' => 'redemption_discount',
                'ai_access' => 'redemption_ai',
                'consultation' => 'redemption_consultation',
                default => 'redemption_discount',
            };

            $redemption = Redemption::create([
                'points_account_id' => $account->id,
                'redemption_option_id' => $option->id,
                'cost_points' => $option->cost_points,
                'value_meta' => $option->value_meta,
                'voucher_code' => $this->needsVoucher($option->type) ? $this->voucherCode($option->type) : null,
                'status' => 'issued',
                'expires_at' => now()->addDays((int) config('engagement_points.voucher_validity_days', 180)),
            ]);

            $this->points->spend($account, (int) $option->cost_points, $source, $redemption, $option->name);

            return $redemption;
        });
    }

    /** Apply a service-discount voucher to a quote (service lines only). Returns SAR discounted. */
    public function applyDiscountToQuote(Redemption $redemption, Quote $quote): float
    {
        if ($redemption->option->type !== 'service_discount') {
            throw new \RuntimeException('This voucher is not a service discount.');
        }

        $percent = (float) $redemption->meta('percent', 0);
        $cap = (float) $redemption->meta('cap_sar', config('engagement_points.discount_cap_sar', 2500));

        // Serialise the check-then-act so two requests can't both apply a discount
        // (one voucher per quote, spec §9).
        return DB::transaction(function () use ($redemption, $quote, $percent, $cap) {
            $redemption = Redemption::lockForUpdate()->findOrFail($redemption->id);
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);

            if (! $redemption->isIssued() || $redemption->isExpired()) {
                throw new \RuntimeException('This voucher is not redeemable.');
            }
            // A voucher may only be applied to a quote awaiting the client's decision
            // ('sent') — not a draft / in-approval / already accepted/declined quote.
            // This mirrors the dashboard's eligible-quote filter and stops a client
            // from discounting an in-flight quote whose price staff haven't finalised.
            if ($quote->status !== 'sent') {
                throw new \RuntimeException('This quote can no longer be discounted.');
            }
            if ($quote->discount_amount > 0 || $quote->discount_percent > 0) {
                throw new \RuntimeException('This quote already has a discount applied.');
            }

            $discount = $this->discounts->applyServiceDiscount($quote->id, $percent, $cap);

            $redemption->forceFill([
                'status' => 'applied',
                'applied_to_quote_id' => $quote->id,
                'applied_at' => now(),
            ])->save();

            return $discount;
        });
    }

    /** Cancel an unused voucher and refund the points. */
    public function cancel(Redemption $redemption, string $reason = 'Voucher cancelled'): void
    {
        if (! $redemption->isIssued()) {
            throw new \RuntimeException('Only an unused voucher can be cancelled.');
        }
        DB::transaction(function () use ($redemption, $reason) {
            $redemption->forceFill(['status' => 'cancelled'])->save();
            if ($redemption->cost_points > 0) {
                $this->points->awardPoints(
                    $redemption->account,
                    (int) $redemption->cost_points,
                    'reversal',
                    $redemption,
                    $reason,
                );
            }
        });
    }

    /**
     * Release an applied service-discount voucher when its quote is declined /
     * abandoned: clear the discount on the quote and REFUND the spent points, so
     * a normal decline never strands the client's loyalty currency.
     */
    public function releaseForQuote(Quote $quote): void
    {
        $redemption = Redemption::where('applied_to_quote_id', $quote->id)->where('status', 'applied')->first();
        if (! $redemption) {
            return;
        }

        DB::transaction(function () use ($redemption, $quote) {
            $this->discounts->removeDiscount($quote->id);
            $redemption->forceFill(['status' => 'cancelled', 'applied_to_quote_id' => null, 'applied_at' => null])->save();
            if ($redemption->cost_points > 0) {
                $this->points->awardPoints(
                    $redemption->account,
                    (int) $redemption->cost_points,
                    'reversal',
                    $redemption,
                    'Discount voucher released — quote not accepted',
                );
            }
        });
    }

    private function needsVoucher(string $type): bool
    {
        return in_array($type, ['service_discount', 'consultation'], true);
    }

    private function voucherCode(string $type): string
    {
        $prefix = match ($type) {
            'service_discount' => 'DISC',
            'consultation' => 'CONS',
            default => 'VCHR',
        };

        return $prefix.'-'.strtoupper(Str::random(8));
    }
}
