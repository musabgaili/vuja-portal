<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VujaDe Engagement System ("Impact Points").
 * - engagement_logs: one row per point-earning event (audit trail + analytics)
 * - thank_you_tokens: peer-to-peer gratitude (5 per giver per month)
 * - users.impact_points: denormalized running total for the XP bar / leaderboard
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action');                 // e.g. task_completed_early
            $table->integer('points');                // may be negative (e.g. late plan)
            $table->nullableMorphs('subject');        // optional related model
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });

        Schema::create('thank_you_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('message')->nullable();
            $table->string('period', 7);              // YYYY-MM, for the monthly quota
            $table->timestamps();

            $table->index(['from_user_id', 'period']);
            $table->index('to_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('impact_points')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('impact_points');
        });
        Schema::dropIfExists('thank_you_tokens');
        Schema::dropIfExists('engagement_logs');
    }
};
