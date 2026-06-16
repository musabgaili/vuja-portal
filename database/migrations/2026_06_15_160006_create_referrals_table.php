<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — referral program (spec §6, §10).
 * status: pending | signed_up | qualified | rewarded | rejected
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('referrer_account_id')->constrained('points_accounts')->cascadeOnDelete();
            $t->foreignId('referred_client_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('referred_email')->nullable();
            $t->string('code_used');
            $t->string('status', 16)->default('pending');
            $t->foreignId('qualifying_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $t->timestamp('signup_rewarded_at')->nullable();
            $t->timestamp('payment_rewarded_at')->nullable();
            $t->timestamps();

            $t->index(['referrer_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
