<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Targets — one monthly target per staff user per metric. The
 * manager sets target_value; current_value caches the live/manual actual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->date('period');                          // first day of the month
            $t->foreignId('target_metric_id')->constrained('target_metrics')->cascadeOnDelete();
            $t->decimal('target_value', 12, 2)->default(0);
            $t->decimal('current_value', 12, 2)->nullable(); // manual entry / last computed cache
            $t->string('notes')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->unique(['user_id', 'period', 'target_metric_id']);
            $t->index(['period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_targets');
    }
};
