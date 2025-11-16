<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('copyright_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('work_description');
            $table->string('work_type'); // Literary, Artistic, Musical, Software, etc.
            $table->json('work_files')->nullable();
            
            // Status: submitted -> meeting_booked -> meeting_confirmed -> filing -> registered -> completed
            $table->enum('status', [
                'submitted', 'meeting_booked', 'meeting_confirmed',
                'filing', 'registered', 'completed', 'cancelled'
            ])->default('submitted');
            
            // Meeting
            $table->timestamp('meeting_requested_at')->nullable();
            $table->timestamp('meeting_confirmed_at')->nullable();
            $table->string('meeting_link')->nullable();
            
            // Registration
            $table->string('copyright_number')->nullable();
            $table->timestamp('filed_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copyright_registrations');
    }
};
