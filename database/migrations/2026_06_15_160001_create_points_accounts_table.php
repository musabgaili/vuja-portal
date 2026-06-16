<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — one account per client. balance/lifetime/tier are CACHES
 * recomputed from the append-only ledger on every write (spec §6, §8).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->integer('balance')->default(0);            // current spendable (earned - spent - expired)
            $t->integer('lifetime_points')->default(0);    // total ever EARNED; never reduced by spend → drives tier
            $t->foreignId('tier_id')->nullable()->constrained('tiers')->nullOnDelete();
            $t->string('referral_code')->unique();         // generated on creation
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_accounts');
    }
};
