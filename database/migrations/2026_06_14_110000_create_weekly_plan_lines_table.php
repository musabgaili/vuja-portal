<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timesheet line items for a weekly plan. One row per thing the employee logs
 * time against in a week — an assigned project, a direct staff task, or a fixed
 * activity (Vacation, Sick time, Marketing, ...). Hours-per-day live in a JSON
 * map keyed by the config('planner.days') day names.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_plan_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_plan_id')->constrained()->cascadeOnDelete();

            // project | task | activity
            $table->string('kind');

            // Exactly one of these identifies the line, depending on kind.
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('staff_task_id')->nullable()->constrained('staff_tasks')->nullOnDelete();
            $table->string('activity')->nullable();          // activity key from config('planner.activities')

            // Cached display label so history/review survive a deleted source.
            $table->string('label');

            // {"sunday":8,"monday":8,...} — hours per working day.
            $table->json('hours')->nullable();

            $table->timestamps();

            $table->index(['weekly_plan_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_plan_lines');
    }
};
