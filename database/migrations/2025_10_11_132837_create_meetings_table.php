<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_slot_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('team_member_id')->constrained('users')->onDelete('cascade');
            
            // Link to service request (polymorphic)
            $table->string('bookable_type')->nullable(); // ConsultationRequest, ResearchRequest, etc.
            $table->unsignedBigInteger('bookable_id')->nullable();
            
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled'])->default('scheduled');
            
            $table->string('meeting_link')->nullable(); // Zoom/Google Meet link
            $table->text('meeting_notes')->nullable();
            
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['bookable_type', 'bookable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
