<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A redeemed service-discount voucher applies to a quote's SERVICE lines only
 * (never the Electronic Components line), capped in SAR (spec §9). Stored here
 * so PricingService stays the single source of truth and re-pricing preserves
 * the discount. discount_amount is computed by PricingService, never the client.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $t) {
            $t->decimal('discount_percent', 5, 2)->default(0)->after('vat_rate');
            $t->decimal('discount_cap_sar', 12, 2)->nullable()->after('discount_percent');
            $t->decimal('discount_amount', 12, 2)->default(0)->after('discount_cap_sar');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $t) {
            $t->dropColumn(['discount_percent', 'discount_cap_sar', 'discount_amount']);
        });
    }
};
