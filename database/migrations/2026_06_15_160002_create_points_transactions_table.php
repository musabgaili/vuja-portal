<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — the append-only ledger (spec §6, §8). Never mutate a
 * balance directly: every change is a row here, and points_accounts caches are
 * recomputed from these rows.
 *
 * direction: earn | spend | expire | reverse | adjust
 * source:    idea | social | review | event | project_payment | referral_signup |
 *            referral_payment | profile | survey | manual_grant |
 *            redemption_discount | redemption_ai | redemption_consultation | expiry | reversal | welcome
 * status:    pending | approved | rejected | reversed | expired
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('points_account_id')->constrained()->cascadeOnDelete();
            $t->string('direction', 16);
            $t->string('source', 32);
            $t->integer('points');                          // signed: + earn, - spend/expire
            $t->integer('remaining')->nullable();           // for earns: unconsumed amount (FIFO spend/expiry)
            $t->string('description')->nullable();
            $t->string('status', 16)->default('approved');
            $t->nullableMorphs('reference');                // claim / redemption / referral / project / grant_request
            $t->timestamp('earned_at')->nullable();
            $t->timestamp('expires_at')->nullable();        // earns: earned_at + expiry_months
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['points_account_id', 'direction', 'status']);
            $t->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
    }
};
