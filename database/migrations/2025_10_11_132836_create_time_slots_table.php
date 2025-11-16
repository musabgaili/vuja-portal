<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Internal team member
            
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            
            $table->enum('status', ['available', 'booked', 'blocked'])->default('available');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern')->nullable(); // e.g., 'weekly', 'daily'
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->unique(['user_id', 'date', 'start_time']); // Prevent overlapping slots
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
