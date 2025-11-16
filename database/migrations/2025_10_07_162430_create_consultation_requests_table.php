<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category'); // Business, Tech, Marketing, Legal, etc.
            $table->string('title');
            $table->text('description');
            $table->text('specific_questions')->nullable();
            
            // Status: submitted -> filtered -> assigned -> meeting_scheduled -> meeting_sent -> completed
            $table->enum('status', [
                'submitted', 'filtered', 'assigned', 'meeting_scheduled', 
                'meeting_sent', 'completed', 'cancelled'
            ])->default('submitted');
            
            // Employee assignment (auto-filtered)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Meeting details
            $table->timestamp('meeting_scheduled_at')->nullable();
            $table->string('meeting_link')->nullable();
            $table->text('meeting_notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_requests');
    }
};
