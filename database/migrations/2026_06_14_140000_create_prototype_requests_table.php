<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prototype requests — a client describes a prototype they want developed,
 * with a description and supporting document uploads (see prototype_request_files).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prototype_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();                 // public route key (no sequential IDs)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the client

            $table->string('title');
            $table->string('category')->nullable();         // e.g. Web app, Mobile app, Hardware, IoT
            $table->text('description');
            $table->text('goals')->nullable();              // what success looks like
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();

            // submitted -> assigned -> in_progress -> completed | rejected | cancelled
            $table->enum('status', ['submitted', 'assigned', 'in_progress', 'completed', 'rejected', 'cancelled'])
                ->default('submitted');

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('manager_notes')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prototype_requests');
    }
};
