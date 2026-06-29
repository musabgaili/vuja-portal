<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits weekly capacity into a manager-set FLOOR (min_weekly_hours) and the
 * engineer's own committed hours (weekly_capacity_hours, which must stay >= the
 * floor). Existing rows seed the floor from their current capacity so nobody can
 * drop below what they already had.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('team_members', 'min_weekly_hours')) {
            Schema::table('team_members', function (Blueprint $t) {
                $t->decimal('min_weekly_hours', 6, 2)->default(40)->after('weekly_capacity_hours');
            });
        }

        // Floor starts at the current capacity for every existing engineer.
        DB::table('team_members')->update(['min_weekly_hours' => DB::raw('weekly_capacity_hours')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('team_members', 'min_weekly_hours')) {
            Schema::table('team_members', function (Blueprint $t) {
                $t->dropColumn('min_weekly_hours');
            });
        }
    }
};
