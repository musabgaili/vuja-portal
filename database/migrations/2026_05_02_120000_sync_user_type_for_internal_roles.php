<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Align users.type with internal-facing roles (fixes client dashboard + My Projects mismatch).
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['employee', 'manager', 'project_manager'])
            ->update(['type' => 'internal']);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('role', 'client')
            ->update(['type' => 'client']);

        DB::table('users')
            ->whereIn('role', ['employee', 'manager', 'project_manager'])
            ->where('type', 'internal')
            ->update(['type' => 'client']);
    }
};
