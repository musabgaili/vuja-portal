<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A multi-attendee internal meeting can be proposed before any single slot is
 * confirmed (attendees may still be deciding), so the meeting's primary
 * time_slot_id becomes optional. Client/legacy bookings still set it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('time_slot_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('time_slot_id')->nullable(false)->change();
        });
    }
};
