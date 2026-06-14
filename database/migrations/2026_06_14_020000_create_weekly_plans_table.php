<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VujaDe Weekly Strategic Planner.
 * One plan per employee per week: a 40-hour allocation across three buckets and
 * a per-day working location, with an approval workflow (draft/pending/approved/
 * rejected/overdue).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');                 // Sunday of the planned week
            $table->string('status')->default('draft'); // draft|pending|approved|rejected|overdue

            // 40-hour allocation buckets
            $table->unsignedSmallInteger('hours_projects')->default(0);
            $table->unsignedSmallInteger('hours_development')->default(0);
            $table->unsignedSmallInteger('hours_presale')->default(0);

            // Per-day working location, e.g. {"sunday":"home","monday":"noura_lab",...}
            $table->json('locations')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
            $table->index('status');
            $table->index('week_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_plans');
    }
};
