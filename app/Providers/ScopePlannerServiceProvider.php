<?php

namespace App\Providers;

use App\ScopePlanner\Adapters\GeminiScopeAdapter;
use App\ScopePlanner\Adapters\PricingRuleAdapter;
use App\ScopePlanner\Adapters\StockItemInventoryAdapter;
use App\ScopePlanner\Contracts\InventoryContract;
use App\ScopePlanner\Contracts\PricingToolContract;
use App\ScopePlanner\Contracts\ScopeAiContract;
use App\Services\Scope\Render\MpdfRenderer;
use App\Services\Scope\Render\PdfRenderer;
use Illuminate\Support\ServiceProvider;

/**
 * Binds the Scope Planner contracts to the real existing modules (Inventory,
 * Pricing Tool, Gemini). Swapping any implementation is a one-line change here.
 */
class ScopePlannerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryContract::class, StockItemInventoryAdapter::class);
        $this->app->bind(PricingToolContract::class, PricingRuleAdapter::class);
        $this->app->bind(ScopeAiContract::class, GeminiScopeAdapter::class);

        // PDF engine is swappable via config('scope.pdf_engine'); mPDF ships now.
        $this->app->bind(PdfRenderer::class, function () {
            return match (config('scope.pdf_engine', 'mpdf')) {
                default => new MpdfRenderer(),
            };
        });
    }
}
