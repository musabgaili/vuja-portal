<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Engagement Points — manual-review claims for forgeable actions (spec §6, §11):
 * social posts, public reviews, claimed event attendance. No points until approved.
 * platform: linkedin | x | instagram | google | other
 * status:   submitted | approved | rejected
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_claims', function (Blueprint $t) {
            $t->id();
            $t->foreignId('points_account_id')->constrained()->cascadeOnDelete();
            $t->string('earning_rule_key');                 // social_post, review_public, event_attendance...
            $t->string('platform', 16)->nullable();
            $t->string('post_url')->nullable();
            $t->string('screenshot_path')->nullable();
            $t->timestamp('posted_at')->nullable();
            $t->string('status', 16)->default('submitted');
            $t->text('review_notes')->nullable();
            $t->foreignId('points_transaction_id')->nullable()->constrained('points_transactions')->nullOnDelete();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['status', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_claims');
    }
};
