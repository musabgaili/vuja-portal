<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Targets — monthly snapshot history. A "Record month" action (or
 * the nightly safety-net) writes one row per user per metric per month; the
 * 6-month trend chart reads from here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->date('period');                          // first day of the month
            $t->foreignId('target_metric_id')->constrained('target_metrics')->cascadeOnDelete();
            $t->decimal('target_value', 12, 2)->default(0);
            $t->decimal('actual_value', 12, 2)->default(0);
            $t->decimal('attainment_pct', 6, 2)->default(0); // actual/target*100 (0 if no target)
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('recorded_at')->nullable();
            $t->timestamps();

            $t->unique(['user_id', 'period', 'target_metric_id']);
            $t->index(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_logs');
    }
};
