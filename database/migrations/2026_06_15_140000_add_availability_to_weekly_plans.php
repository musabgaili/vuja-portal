<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-day availability window on a weekly plan: { day => { start, end } } in
 * HH:MM. Combined with the existing per-day working location, this tells a
 * manager from when to when each employee is available and where — for lab
 * allocation. Hours-per-project remain, but the time window is the planning key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_plans', function (Blueprint $table) {
            $table->json('availability')->nullable()->after('locations');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_plans', function (Blueprint $table) {
            $table->dropColumn('availability');
        });
    }
};
