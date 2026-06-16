<?php

namespace App\Providers;

use App\Engagement\Adapters\ImprovementIdeasBridge;
use App\Engagement\Adapters\InvoiceBillingAdapter;
use App\Engagement\Adapters\ScopeQuoteDiscountBridge;
use App\Engagement\Adapters\UserClientDirectory;
use App\Engagement\Contracts\BillingBridge;
use App\Engagement\Contracts\ClientDirectory;
use App\Engagement\Contracts\IdeasBridge;
use App\Engagement\Contracts\QuoteDiscountBridge;
use App\Models\User;
use App\Services\Engagement\ReferralService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the client Engagement Points engine to the existing portal models
 * (User / ImprovementIdea / Invoice / Quote) via stable contracts (spec §5),
 * registers the authorization gates, and attributes referral signups.
 */
class ClientEngagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientDirectory::class, UserClientDirectory::class);
        $this->app->bind(IdeasBridge::class, ImprovementIdeasBridge::class);
        $this->app->bind(BillingBridge::class, InvoiceBillingAdapter::class);
        $this->app->bind(QuoteDiscountBridge::class, ScopeQuoteDiscountBridge::class);
    }

    public function boot(): void
    {
        // Manager configures rules/redemptions/tiers, reviews claims, adjusts accounts.
        Gate::define('manage-engagement', fn (User $u) => $u->isManager());
        // A Project Manager may suggest a discretionary grant (Manager approves).
        Gate::define('suggest-grant', fn (User $u) => $u->isProjectManager() || $u->isManager());

        // Attribute a referral when a referred client account is created (records
        // the attribution; the reward vests on email verification, below).
        User::created(function (User $user) {
            if ($user->isClient()) {
                app(ReferralService::class)->attributeSignup($user);
            }
        });

        // Vest the referral signup reward + welcome perk once the referred client
        // verifies their email — blunts throwaway-account farming.
        Event::listen(Verified::class, function (Verified $event) {
            if ($event->user instanceof User && $event->user->isClient()) {
                app(ReferralService::class)->confirmSignup($event->user);
            }
        });
    }
}
