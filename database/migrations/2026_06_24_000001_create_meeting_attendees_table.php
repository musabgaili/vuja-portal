<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additional internal attendees on a meeting (beyond the organiser/booker stored
 * in meetings.client_id and the primary host in meetings.team_member_id). Each
 * row tracks one invited colleague's response and the slot consumed for them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // The attendee's own slot consumed for this meeting (null while pending,
            // or when an invite was sent for a time outside their availability).
            $table->foreignId('time_slot_id')->nullable()->constrained('time_slots')->nullOnDelete();
            $table->string('status')->default('invited'); // invited | accepted | declined
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendees');
    }
};
