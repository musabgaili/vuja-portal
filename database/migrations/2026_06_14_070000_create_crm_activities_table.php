<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM activities & chatter — polymorphic to opportunities / contacts / companies.
 * A "planned" activity is a next-action (call/email/meeting/to-do) with a due
 * date; a "done" activity (incl. type=note) is a logged interaction in the
 * record timeline.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();    // assignee
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('todo');     // call|email|meeting|todo|note
            $table->string('summary');
            $table->text('notes')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('status')->default('planned'); // planned|done
            $table->timestamp('done_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
