<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Direct staff tasks — work a manager assigns to an internal user that may or
 * may not be tied to a project/opportunity. Categorised as project / sales /
 * presale / management; completing one awards Impact Points at the (higher)
 * staff_task_* rate for its category.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            // project | sales | presale | management
            $table->string('category')->default('management');
            // low | normal | high | urgent
            $table->string('priority')->default('normal');
            // open | in_progress | done | cancelled
            $table->string('status')->default('open');

            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();

            // Optional links — a task need not belong to a project or opportunity.
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();

            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            // Guards against double-awarding IP if a task is re-opened then re-closed.
            $table->boolean('points_awarded')->default(false);
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
            $table->index(['status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_tasks');
    }
};
