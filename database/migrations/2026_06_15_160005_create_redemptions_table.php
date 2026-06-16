<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — issued rewards (spec §6, §9).
 * status: issued | applied | expired | cancelled
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('points_account_id')->constrained()->cascadeOnDelete();
            $t->foreignId('redemption_option_id')->constrained()->cascadeOnDelete();
            $t->integer('cost_points')->default(0);
            $t->json('value_meta')->nullable();             // snapshot at redeem time
            $t->string('voucher_code')->nullable()->index(); // service_discount / consultation
            $t->string('status', 16)->default('issued');
            $t->foreignId('applied_to_quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $t->timestamp('applied_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};
