<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL still had the original ENUM from create_projects_table; the app and DemoDataSeeder
     * use planning, quoted, awarded, in_progress, paused, completed, lost, cancelled.
     * Widen to VARCHAR so all statuses persist without truncation (1265).
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('projects')->where('status', 'active')->update(['status' => 'in_progress']);
        DB::table('projects')->where('status', 'on_hold')->update(['status' => 'paused']);

        DB::statement("ALTER TABLE `projects` MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'planning'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('projects')->whereIn('status', ['quoted', 'awarded', 'in_progress', 'lost'])
            ->update(['status' => 'active']);
        DB::table('projects')->where('status', 'paused')->update(['status' => 'on_hold']);

        DB::statement("ALTER TABLE `projects` MODIFY `status` ENUM('planning','active','on_hold','completed','cancelled') NOT NULL DEFAULT 'planning'");
    }
};
