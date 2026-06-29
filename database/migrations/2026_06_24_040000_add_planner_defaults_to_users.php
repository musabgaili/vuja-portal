<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A reusable default weekly schedule (per-day availability windows + working
 * location) the employee can apply each week in the planner with one click,
 * instead of re-entering the same timing every time.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'planner_defaults')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('planner_defaults')->nullable()->after('notification_preferences');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'planner_defaults')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('planner_defaults');
            });
        }
    }
};
